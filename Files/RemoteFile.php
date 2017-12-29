<?php

declare(strict_types=1);

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Files;

use Exception;

/**
 * Class RemoteFile.
 */
class RemoteFile extends ResourceFile
{
    /**
     * RemoteFile constructor.
     *
     * @param string $url
     * @param null   $name
     * @param null   $tempDir
     *
     * @throws Exception
     */
    public function __construct(string $url, $name = null, $tempDir = null)
    {
        if (false === $this->urlExists($url)) {
            throw new Exception("File {$url} not found");
        }

        $name = $name ?: basename(strtok($url, '?'));
        $content = file_get_contents($url);
        parent::__construct($content, $name);
    }

    /**
     * @param $url
     *
     * @return bool
     */
    public function urlExists($url): bool
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $code == 200;
    }
}
