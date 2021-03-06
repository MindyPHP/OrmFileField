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

/**
 * Class MD5NameHasher
 */
class MD5NameHasher extends DefaultHasher
{
    /**
     * {@inheritdoc}
     */
    public function hash($fileName): string
    {
        return md5($fileName);
    }
}
