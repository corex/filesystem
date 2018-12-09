<?php

declare(strict_types=1);

namespace CoRex\Filesystem;

use CoRex\Helpers\Str;

class File
{
    /**
     * Get temp filename.
     *
     * @param string $path Default '' which means sys_get_temp_dir().
     * @param string $prefix Default ''.
     * @param string $extension Default ''.
     * @return string
     */
    public static function getTempFilename(string $path = '', string $prefix = '', string $extension = ''): string
    {
        if ($path === '') {
            $path = sys_get_temp_dir();
        }
        if ($extension !== '' && substr($extension, 0, 1) !== '.') {
            $extension = '.' . $extension;
        }
        $filename = Str::unique($prefix, $extension);
        if (is_dir($path)) {
            touch($path . '/' . $filename);
        }
        return $path . '/' . $filename;
    }

    /**
     * Touch.
     *
     * @param string $filename
     * @param int $time Default null which means current.
     */
    public static function touch(string $filename, ?int $time = null): void
    {
        if ($time === null) {
            $time = time();
        }
        touch($filename, $time);
    }

    /**
     * Check if file exist.
     *
     * @param string $filename
     * @return bool
     */
    public static function exist(string $filename): bool
    {
        return file_exists($filename);
    }

    /**
     * Get.
     *
     * @param string $filename
     * @param mixed $defaultValue Default ''.
     * @return string
     */
    public static function get(string $filename, string $defaultValue = ''): string
    {
        if (!self::exist($filename)) {
            return $defaultValue;
        }
        return file_get_contents($filename);
    }

    /**
     * Get lines.
     *
     * @param string $filename
     * @param string[] $defaultValue Default [].
     * @return string[]
     */
    public static function getLines(string $filename, array $defaultValue = []): array
    {
        $content = self::get($filename);
        $content = str_replace("\r", '', $content);
        if (trim($content) !== '') {
            return explode("\n", $content);
        }
        return $defaultValue;
    }

    /**
     * Put.
     *
     * @param string $filename
     * @param string $content
     * @return int
     */
    public static function put(string $filename, string $content): int
    {
        return file_put_contents($filename, $content);
    }

    /**
     * Prepend.
     *
     * @param string $filename
     * @param string $content
     * @return int
     */
    public static function prepend(string $filename, string $content): int
    {
        if (self::exist($filename)) {
            return self::put($filename, $content . self::get($filename));
        }
        return self::put($filename, $content);
    }

    /**
     * Append.
     *
     * @param string $filename
     * @param string $content
     * @return int
     */
    public static function append(string $filename, string $content): int
    {
        return file_put_contents($filename, $content, FILE_APPEND);
    }

    /**
     * Put lines.
     *
     * @param string $filename
     * @param string[] $lines
     * @param string $separator Default "\n".
     * @return int
     */
    public static function putLines(string $filename, array $lines, string $separator = "\n"): int
    {
        return self::put($filename, implode($separator, $lines));
    }

    /**
     * Prepend lines.
     *
     * @param string $filename
     * @param string[] $lines
     * @param string $separator Default "\n".
     * @return int
     */
    public static function prependLines(string $filename, array $lines, string $separator = "\n"): int
    {
        if (self::exist($filename)) {
            $existingLines = self::getLines($filename);
            $lines = array_merge($lines, $existingLines);
            return self::putLines($filename, $lines, $separator);
        }
        return self::putLines($filename, $lines, $separator);
    }

    /**
     * Append lines.
     *
     * @param string $filename
     * @param string[] $lines
     * @param string $separator Default "\n".
     * @return int
     */
    public static function appendLines(string $filename, array $lines, string $separator = "\n"): int
    {
        if (self::exist($filename)) {
            $existingLines = self::getLines($filename);
            $lines = array_merge($existingLines, $lines);
            return self::putLines($filename, $lines, $separator);
        }
        return self::putLines($filename, $lines, $separator);
    }

    /**
     * Get stub.
     *
     * @param string $filename
     * @param string[] $tokens Default []. Format ['token' => 'value']. Replaces {token} with value.
     * @param mixed $defaultContent Default ''.
     * @return string
     */
    public static function getStub(string $filename, array $tokens = [], string $defaultContent = ''): string
    {
        return self::getTemplate($filename, $tokens, $defaultContent, 'stub');
    }

    /**
     * Get template.
     *
     * @param string $filename
     * @param string[] $tokens Default []. Format ['token' => 'value']. Replaces {token} with value.
     * @param mixed $defaultContent Default ''.
     * @param string $extension Default 'tpl'.
     * @return string
     */
    public static function getTemplate(
        string $filename,
        array $tokens = [],
        string $defaultContent = '',
        string $extension = 'tpl'
    ): string {
        if (!Str::endsWith($filename, '.' . $extension)) {
            $filename .= '.' . $extension;
        }
        if (!self::exist($filename)) {
            return $defaultContent;
        }
        $template = self::get($filename, $defaultContent);
        if ($template !== '' && count($tokens) > 0) {
            foreach ($tokens as $token => $value) {
                $template = str_replace('{' . $token . '}', $value, $template);
            }
        }
        return $template;
    }

    /**
     * Load json.
     *
     * @param string $filename
     * @param mixed[] $defaultValue Default [].
     * @return mixed[]
     */
    public static function getJson(string $filename, array $defaultValue = []): array
    {
        if (!Str::endsWith($filename, '.json')) {
            $filename .= '.json';
        }
        $data = self::get($filename);
        if ($data === '') {
            return $defaultValue;
        }
        $data = json_decode($data, true);
        if ($data === null || $data === false) {
            $data = [];
        }
        return $data;
    }

    /**
     * Save json.
     *
     * @param string $filename
     * @param mixed[] $data
     * @param bool $prettyPrint Default true.
     */
    public static function putJson(string $filename, array $data, bool $prettyPrint = true): void
    {
        if (!Str::endsWith($filename, '.json')) {
            $filename .= '.json';
        }
        $options = JSON_UNESCAPED_SLASHES;
        if ($prettyPrint) {
            $options += JSON_PRETTY_PRINT;
        }
        $data = json_encode($data, $options);
        self::put($filename, $data);
    }

    /**
     * Delete file.
     *
     * @param string $filename
     * @return bool
     */
    public static function delete(string $filename): bool
    {
        return @unlink($filename);
    }

    /**
     * Copy.
     *
     * @param string $filename
     * @param string $path
     * @return bool
     */
    public static function copy(string $filename, string $path): bool
    {
        return @copy($filename, $path . '/' . self::basename($filename));
    }

    /**
     * Move.
     *
     * @param string $filename
     * @param string $path
     * @return bool
     */
    public static function move(string $filename, string $path): bool
    {
        return @rename($filename, $path . '/' . self::basename($filename));
    }

    /**
     * Name.
     *
     * @param string $path
     * @return string
     */
    public static function name(string $path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Basename.
     *
     * @param string $path
     * @return string
     */
    public static function basename(string $path): string
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }

    /**
     * Dirname.
     *
     * @param string $path
     * @return string
     */
    public static function dirname(string $path): string
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    /**
     * Extension.
     *
     * @param string $path
     * @return string
     */
    public static function extension(string $path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Type.
     *
     * @param string $path
     * @return string
     */
    public static function type(string $path): string
    {
        $result = @filetype($path);
        return $result !== false ? $result : '';
    }

    /**
     * Mimetype.
     *
     * @param string $path
     * @return string
     */
    public static function mimetype(string $path): string
    {
        $result = @finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
        return $result !== false ? $result : '';
    }

    /**
     * Size.
     *
     * @param string $path
     * @return int
     */
    public static function size(string $path): int
    {
        return filesize($path);
    }

    /**
     * Last modified.
     *
     * @param string $path
     * @return int
     */
    public static function lastModified(string $path): int
    {
        clearstatcache();
        return filemtime($path);
    }

    /**
     * Is file.
     *
     * @param string $path
     * @return bool
     */
    public static function isFile(string $path): bool
    {
        return is_file($path);
    }
}