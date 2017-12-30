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

use Mindy\Orm\Files\LocalFile;
use Mindy\Orm\Files\RemoteFile;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    /**
     * @expectedException \Exception
     */
    public function testLocalFile()
    {
        new LocalFile(__FILE__.'foobar', true);
    }

    /**
     * @expectedException \Exception
     */
    public function testRemoteFile()
    {
        new RemoteFile('http://foobar.com/qwe.txt');
    }
}
