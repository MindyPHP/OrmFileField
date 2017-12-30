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

trait FileNameHasherAwareTrait
{
    /**
     * @var FileNameHasherInterface
     */
    protected $fileNameHasher;

    /**
     * @param FileNameHasherInterface $fileNameHasher
     */
    public function setFileNameHasher(FileNameHasherInterface $fileNameHasher)
    {
        $this->fileNameHasher = $fileNameHasher;
    }

    /**
     * @return FileNameHasherInterface
     */
    public function getFileNameHasher()
    {
        if ($this->fileNameHasher === null) {
            $this->fileNameHasher = new DefaultHasher();
        }

        return $this->fileNameHasher;
    }
}
