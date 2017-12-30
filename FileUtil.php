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

class FileUtil
{
    /**
     * Combine multiple slashes into a single slash
     *
     * @param $path
     *
     * @return string
     */
    public static function normalizePath($path): string
    {
        return preg_replace('/\/+/', '/', $path);
    }
}
