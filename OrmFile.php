<?php

declare(strict_types=1);

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm;

use League\Flysystem\FilesystemInterface;

/**
 * Class OrmFile.
 */
class OrmFile
{
    /**
     * @var FilesystemInterface|null
     */
    protected static $filesystem;

    /**
     * @param FilesystemInterface $filesystem
     */
    public static function setFilesystem(FilesystemInterface $filesystem)
    {
        self::$filesystem = $filesystem;
    }

    /**
     * @return FilesystemInterface|null
     */
    public static function getFilesystem(): FilesystemInterface
    {
        return self::$filesystem;
    }
}
