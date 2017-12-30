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

use League\Flysystem\Util\MimeType;
use Mimey\MimeTypes;

/**
 * Class ResourceFile.
 */
class Base64File extends File
{
    /**
     * ResourceFile constructor.
     *
     * @param string      $raw
     * @param null|string $name
     * @param null|string $tempDir
     */
    public function __construct($raw, $name = null, $tempDir = null)
    {
        $path = $this->saveFileFromBase64(
            empty($tempDir) ? sys_get_temp_dir() : $tempDir,
            $raw,
            $name ?: 'file'
        );
        parent::__construct($path);
    }

    /**
     * @param string      $tmpDir
     * @param string      $raw
     * @param string|null $name
     *
     * @return string
     */
    protected function saveFileFromBase64(string $tmpDir, string $raw, string $name): string
    {
        list($mimeType, $value) = explode(';', $raw);
        $bodyRaw = mb_substr($value, mb_strpos($value, ',', 0, 'UTF-8'), null, 'UTF-8');
        $body = base64_decode($bodyRaw);

        $mimes = new MimeTypes;
        $name = sprintf(
            '%s.%s',
            pathinfo($name, PATHINFO_FILENAME),
            $mimes->getExtension($mimeType)
        );

        $path = dirname(tempnam($tmpDir, 'upload')).DIRECTORY_SEPARATOR.$name;

        file_put_contents($path, $body);

        return $path;
    }
}
