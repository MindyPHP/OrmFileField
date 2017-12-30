<?php

declare(strict_types=1);

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use League\Flysystem\Adapter\Local;
use League\Flysystem\File;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Mindy\Orm\Fields\FileField;
use Mindy\Orm\FileNameHasher\DefaultHasher;
use Mindy\Orm\FileNameHasher\FileNameHasherInterface;
use Mindy\Orm\Files\Base64File;
use Mindy\Orm\Files\LocalFile;
use Mindy\Orm\Files\RemoteFile;
use Mindy\Orm\Files\ResourceFile;
use Mindy\Orm\Tests\Models\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileFieldTest extends TestCase
{
    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var FileField
     */
    protected $field;

    public function setUp()
    {
        $this->filesystem = new Filesystem(new Local(__DIR__.'/temp'));
        file_put_contents(__DIR__.'/test.txt', '123');

        $field = new FileField([
            'name' => 'file',
        ]);
        $field->setFilesystem($this->filesystem);
        assert($field->getFileNameHasher() instanceof DefaultHasher);
        $field->setModel(new FileModel());

        $this->field = $field;
    }

    public function tearDown()
    {
        foreach ($this->filesystem->listContents('/') as $file) {
            if ($file['type'] == 'dir') {
                $this->filesystem->deleteDir($file['path']);
            } else {
                $this->filesystem->delete($file['path']);
            }
        }

        $this->filesystem = null;
        $this->field = null;

        if (is_file(__DIR__.'/test.txt')) {
            unlink(__DIR__.'/test.txt');
        }
    }

    public function testUploadedFile()
    {
        // $path, $originalName, $mimeType = null, $size = null, $error = null, $test = false
        $file = new UploadedFile(
            __DIR__.'/test.txt',
            'test.txt',
            'plain/text',
            filesize(__DIR__.'/test.txt'),
            null,
            true
        );
        $this->field->saveUploadedFile($file);

        $path = $this->field->getUploadTo();
        $this->assertEquals(sprintf('foo/FileModel/%s', date('Y-m-d')), $path);
        $this->assertEquals('123', file_get_contents(__DIR__.'/temp/'.$path.'/test.txt'));
    }

    public function testLocalFile()
    {
        // $path, $originalName, $mimeType = null, $size = null, $error = null, $test = false
        $file = new LocalFile(__DIR__.'/test.txt');
        $this->field->saveFile($file);

        $path = $this->field->getUploadTo();
        $this->assertEquals(sprintf('foo/FileModel/%s', date('Y-m-d')), $path);
        $this->assertEquals('123', file_get_contents(__DIR__.'/temp/'.$path.'/test.txt'));
    }

    public function testResourceFile()
    {
        // $path, $originalName, $mimeType = null, $size = null, $error = null, $test = false
        $file = new ResourceFile('123', 'test.txt');
        $this->field->saveFile($file);

        $path = $this->field->getUploadTo();
        $this->assertEquals(sprintf('foo/FileModel/%s', date('Y-m-d')), $path);
        $this->assertEquals('123', file_get_contents(__DIR__.'/temp/'.$path.'/test.txt'));
    }

    public function testRemoteFile()
    {
        if (@getenv('TRAVIS')) {
            $this->markTestSkipped('Skip remote file');
        }

        $file = new RemoteFile('https://raw.githubusercontent.com/MindyPHP/Mindy/master/README.md', 'readme.md');
        $this->field->saveFile($file);

        $path = $this->field->getUploadTo();
        $this->assertEquals(sprintf('foo/FileModel/%s', date('Y-m-d')), $path);
        $this->assertTrue(is_file(__DIR__.'/temp/'.$path.'/readme.md'));
    }

    public function testFileFieldValidation()
    {
        $this->assertFalse($this->field->isValid());
        $this->assertEquals(['This value should not be blank.'], $this->field->getErrors());

        $path = __DIR__.'/test.txt';
        file_put_contents($path, '123');

        // $path, $originalName, $mimeType = null, $size = null, $error = null, $test = false
        $uploadedFile = new UploadedFile(__FILE__, basename(__FILE__), 'text/php', 10000000, UPLOAD_ERR_OK, false);
        $this->field->setValue($uploadedFile);
        $this->assertFalse($this->field->isValid());
        $this->assertEquals(['The file could not be uploaded.'], $this->field->getErrors());

        $this->field->setValue($path);
        $this->assertInstanceOf(LocalFile::class, $this->field->getValue());
        $this->assertTrue($this->field->isValid());

        $this->field->mimeTypes = [
            'image/*',
        ];

        $uploadedFile = new LocalFile('qweqwe', false);
        $this->field->setValue($uploadedFile);
        $this->assertFalse($this->field->isValid());
        $this->assertEquals('The file could not be found.', $this->field->getErrors()[0]);

        $uploadedFile = new ResourceFile(base64_encode(file_get_contents(__FILE__)));
        $this->field->setValue($uploadedFile);
        $this->assertFalse($this->field->isValid());
        $this->assertEquals('The mime type of the file is invalid ("text/plain"). Allowed mime types are "image/*".',
            $this->field->getErrors()[0]);

        @unlink($path);
    }

    public function testResourceField()
    {
        $resource = new ResourceFile(base64_encode(file_get_contents(__FILE__)), 'test.php');
        $this->field->setValue($resource);
        $this->assertTrue($this->field->isValid());

        $this->field->saveFile($resource);

        $path = $this->field->getUploadTo();
        $this->assertEquals(sprintf('foo/FileModel/%s', date('Y-m-d')), $path);
        $this->assertTrue(is_file(__DIR__.'/temp/'.$path.'/test.php'));
    }

    public function testResourceFieldNoHasher()
    {
        $resource = new ResourceFile(base64_encode(file_get_contents(__FILE__)), 'test.php');
        $this->field->setValue($resource);
        $this->assertTrue($this->field->isValid());

        $this->field->saveFile($resource);

        $path = $this->field->getUploadTo();
        $this->assertEquals(sprintf('foo/FileModel/%s', date('Y-m-d')), $path);
        $this->assertTrue(is_file(__DIR__.'/temp/'.$path.'/test.php'));
    }

    public function testDelete()
    {
        $file = new FileField();
        $fs = $this
            ->getMockBuilder(FilesystemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fs->method('delete')->will($this->returnValue(true));

        $file->setFilesystem($fs);

        $this->assertTrue($file->delete());
    }

    public function testConvertToDatabaseValue()
    {
        $file = new FileField([
            'uploadTo' => 'test',
        ]);
        $file->setModel(new User());
        $fs = $this
            ->getMockBuilder(FilesystemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fs->method('write')->will($this->returnValue(true));

        $platform = $this
            ->getMockBuilder(AbstractPlatform::class)
            ->disableOriginalConstructor()
            ->getMock();

        $file->setFilesystem($fs);

        $value = new UploadedFile(__FILE__, basename(__FILE__), null, null, null, true);
        $this->assertSame('test/FileFieldTest.php', $file->convertToDatabaseValue($value, $platform));

        $value = new LocalFile(__FILE__);
        $this->assertSame('test/FileFieldTest.php', $file->convertToDatabaseValue($value, $platform));
    }

    public function testFailToSaveFile()
    {
        $file = new FileField();

        $platform = $this
            ->getMockBuilder(AbstractPlatform::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertSame(__FILE__, $file->convertToDatabaseValue(__FILE__, $platform));
    }

    public function testSetValue()
    {
        $field = new FileField();
        $field->setValue([
            'tmp_name' => __FILE__,
            'name' => basename(__FILE__),
            'type' => '',
            'size' => 0,
            'error' => UPLOAD_ERR_OK,
        ]);
        $this->assertInstanceOf(UploadedFile::class, $field->getValue());

        $field->setValue(null);
        $this->assertNull($field->getValue());

        $body = 'data:image/gif;base64,R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs=';
        $field->setValue($body);
        $this->assertInstanceOf(Base64File::class, $field->getValue());

        $body = 'data:;base64,R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs=';
        $field->setValue($body);
        $this->assertInstanceOf(Base64File::class, $field->getValue());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unknown file type
     */
    public function testSetValueUnknownFileType()
    {
        $field = new FileField();
        $field->setValue(new \stdClass());
    }

    public function testAfterDeleteEvent()
    {
        $fs = $this
            ->getMockBuilder(FilesystemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fs->method('has')->willReturn(true);
        $fs->expects($this->once())->method('delete');

        $field = new FileField();
        $field->setFilesystem($fs);

        $user = new User();
        $field->afterDelete($user, __FILE__);
    }

    public function testSize()
    {
        $fs = $this
            ->getMockBuilder(FilesystemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fs->method('has')->will($this->returnValue(true));

        $file = $this
            ->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $file->method('getSize')->will($this->returnValue(123));
        $fs->method('get')->will($this->returnValue($file));

        $field = new FileField([
            'value' => __FILE__,
        ]);
        $field->setFilesystem($fs);
        $this->assertSame(123, $field->size());

        $field = new FileField();
        $this->assertSame(0, $field->size());

        $fs->method('has')->will($this->returnValue(false));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage File not found
     */
    public function testSizeUnknownFile()
    {
        $fs = $this
            ->getMockBuilder(FilesystemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fs->method('has')->will($this->returnValue(false));

        $field = new FileField([
            'value' => __FILE__,
        ]);
        $field->setFilesystem($fs);
        $field->size();
    }

    public function testUploadTo()
    {
        $file = new FileField([
            'uploadTo' => function () {
                return '/test/';
            },
        ]);
        $this->assertSame('/test/', $file->getUploadTo());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid uploaded file
     */
    public function testUploadedFileInvalid()
    {
        $file = $this
            ->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->getMock();
        $file->method('isValid')->will($this->returnValue(false));

        $field = new FileField();
        $field->saveUploadedFile($file);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Failed to save file
     */
    public function testUploadedFileBrokenWhileWrite()
    {
        $fs = $this
            ->getMockBuilder(FilesystemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fs->method('write')->willReturn(false);

        $hasher = $this
            ->getMockBuilder(FileNameHasherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $hasher->method('resolveUploadPath')->willReturn('/test/file.txt');

        $file = $this
            ->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $file->method('isValid')->willReturn(true);
        $file->method('getClientOriginalName')->willReturn('file.txt');
        $file->method('getRealPath')->willReturn(__FILE__);

        $field = new FileField([
            'uploadTo' => function () {
                return '/test/';
            },
        ]);
        $field->setFileNameHasher($hasher);
        $field->setFilesystem($fs);
        $field->saveUploadedFile($file);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Failed to save file
     */
    public function testFileBrokenWhileWrite()
    {
        $fs = $this
            ->getMockBuilder(FilesystemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fs->method('write')->willReturn(false);

        $hasher = $this
            ->getMockBuilder(FileNameHasherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $hasher->method('resolveUploadPath')->willReturn('/test/file.txt');

        $file = $this
            ->getMockBuilder(LocalFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $file->method('getFilename')->willReturn('file.txt');
        $file->method('getRealPath')->willReturn(__FILE__);

        $field = new FileField([
            'uploadTo' => function () {
                return '/test/';
            },
        ]);
        $field->setFileNameHasher($hasher);
        $field->setFilesystem($fs);
        $field->saveFile($file);
    }
}
