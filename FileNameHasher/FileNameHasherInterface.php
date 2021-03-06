<?php

declare(strict_types=1);

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\FileNameHasher;

use League\Flysystem\FilesystemInterface;

interface FileNameHasherInterface
{
    /**
     * @param string $name
     *
     * @return string
     */
    public function hash($name): string;

    /**
     * @param FilesystemInterface $filesystem
     * @param string              $uploadTo
     * @param string              $name
     *
     * @return string
     */
    public function resolveUploadPath(FilesystemInterface $filesystem, string $uploadTo, string $name): string;
}
