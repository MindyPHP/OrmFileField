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

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Mindy\Orm\FileNameHasher\DefaultHasher;
use Mindy\Orm\FileNameHasher\MD5NameHasher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NameHasherTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Empty file name received
     */
    public function testResolveUploadPath()
    {
        $fs = $this
            ->getMockBuilder(FilesystemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $file = new DefaultHasher();
        $file->resolveUploadPath($fs, __DIR__, null);
    }

    public function testDefaultNameHasher()
    {
        $fs = $this
            ->getMockBuilder(FilesystemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $hasher = new DefaultHasher();
        $this->assertSame(
            ltrim(__DIR__.'/test.txt', '/'),
            $hasher->resolveUploadPath($fs, __DIR__, 'test.txt')
        );

        $fs
            ->expects($this->any())
            ->method('has')
            ->will($this->returnCallback(function ($value) {
                return ltrim(__DIR__.'/test.txt', '/') === $value;
            }));

        $this->assertSame(
            ltrim(__DIR__.'/test_1.txt', '/'),
            $hasher->resolveUploadPath($fs, __DIR__, 'test.txt')
        );
    }

    public function testMD5NameHasher()
    {
        $fs = $this
            ->getMockBuilder(FilesystemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $hasher = new MD5NameHasher();
        $this->assertSame(
            ltrim(__DIR__.'/098f6bcd4621d373cade4e832627b4f6.txt', '/'),
            $hasher->resolveUploadPath($fs, __DIR__, 'test.txt')
        );

        $fs
            ->expects($this->any())
            ->method('has')
            ->will($this->returnCallback(function ($value) {
                return ltrim(__DIR__.'/098f6bcd4621d373cade4e832627b4f6.txt', '/') === $value;
            }));

        $this->assertSame(
            ltrim(__DIR__.'/098f6bcd4621d373cade4e832627b4f6_1.txt', '/'),
            $hasher->resolveUploadPath($fs, __DIR__, 'test.txt')
        );
    }
}
