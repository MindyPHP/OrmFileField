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

use League\Flysystem\FilesystemInterface;
use Mindy\Orm\Fields\FileField;
use Mindy\Orm\OrmFile;
use PHPUnit\Framework\TestCase;

class OrmFileTest extends TestCase
{
    public function testOrmSetFs()
    {
        $fs = $this
            ->getMockBuilder(FilesystemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        OrmFile::setFilesystem($fs);
        $this->assertInstanceOf(FilesystemInterface::class, OrmFile::getFilesystem());

        $file = new FileField();
        $this->assertInstanceOf(FilesystemInterface::class, $file->getFilesystem());
    }
}
