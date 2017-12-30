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
     * @return bool
     */
    protected function saveFileFromBase64(string $tmpDir, string $raw, string $name): bool
    {
        list($mimeType, $value) = explode(';', $raw);
        $bodyRaw = mb_substr(mb_strpos($value, ',', 0, 'UTF-8'), null, 'UTF-8');
        $body = base64_decode($bodyRaw);

        if (empty($mimeType)) {
            $ext = $this->getExtensionFromMimeType($mimeType);
        } else {
            $ext = $this->getExtensionFromContent($body);
        }

        $name = sprintf('%s.%s', pathinfo($name, PATHINFO_FILENAME), $ext);

        $path = dirname(tempnam($tmpDir, 'upload')).DIRECTORY_SEPARATOR.$name;

        return (bool) file_put_contents($path, $body);
    }

    /**
     * @param string $mimeType
     *
     * @return string
     */
    protected function getExtensionFromMimeType(string $mimeType): string
    {
        $mimeTypes = array_flip(MimeType::getExtensionToMimeTypeMap());
        if (array_key_exists($mimeType, $mimeTypes)) {
            return $mimeTypes[$mimeType];
        }

        throw new \RuntimeException('Could not determine mime type from file body');
    }

    /**
     * @param string $content
     *
     * @return string
     */
    protected function getExtensionFromContent(string $content): string
    {
        return $this->getExtensionFromMimeType(MimeType::detectByContent($content));
    }
}
