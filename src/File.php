<?php

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
    public static function getTempFilename($path = '', $prefix = '', $extension = '')
    {
        if ($path == '') {
            $path = sys_get_temp_dir();
        }
        if ($extension != '' && substr($extension, 0, 1) != '.') {
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
     * @param integer $time Default null which means current.
     */
    public static function touch($filename, $time = null)
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
     * @return boolean
     */
    public static function exist($filename)
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
    public static function get($filename, $defaultValue = '')
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
     * @param array $defaultValue Default [].
     * @return array
     */
    public static function getLines($filename, array $defaultValue = [])
    {
        $content = self::get($filename);
        $content = str_replace("\r", '', $content);
        if (trim($content) != '') {
            return explode("\n", $content);
        }
        return $defaultValue;
    }

    /**
     * Put.
     *
     * @param string $filename
     * @param string $content
     * @return integer
     */
    public static function put($filename, $content)
    {
        return file_put_contents($filename, $content);
    }

    /**
     * Prepend.
     *
     * @param string $filename
     * @param string $content
     * @return integer
     */
    public static function prepend($filename, $content)
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
     * @return integer
     */
    public static function append($filename, $content)
    {
        return file_put_contents($filename, $content, FILE_APPEND);
    }

    /**
     * Put lines.
     *
     * @param string $filename
     * @param array $lines
     * @param string $separator Default "\n".
     * @return integer
     */
    public static function putLines($filename, array $lines, $separator = "\n")
    {
        return self::put($filename, implode($separator, $lines));
    }

    /**
     * Prepend lines.
     *
     * @param string $filename
     * @param array $lines
     * @param string $separator Default "\n".
     * @return integer
     */
    public static function prependLines($filename, array $lines, $separator = "\n")
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
     * @param array $lines
     * @param string $separator Default "\n".
     * @return integer
     */
    public static function appendLines($filename, array $lines, $separator = "\n")
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
     * @param array $tokens Default []. Format ['token' => 'value']. Replaces {token} with value.
     * @param mixed $defaultContent Default ''.
     * @return string
     */
    public static function getStub($filename, array $tokens = [], $defaultContent = '')
    {
        return self::getTemplate($filename, $tokens, $defaultContent, 'stub');
    }

    /**
     * Get template.
     *
     * @param string $filename
     * @param array $tokens Default []. Format ['token' => 'value']. Replaces {token} with value.
     * @param mixed $defaultContent Default ''.
     * @param string $extension Default 'tpl'.
     * @return string
     */
    public static function getTemplate($filename, array $tokens = [], $defaultContent = '', $extension = 'tpl')
    {
        if (!Str::endsWith($filename, '.' . $extension)) {
            $filename .= '.' . $extension;
        }
        if (!self::exist($filename)) {
            return $defaultContent;
        }
        $template = self::get($filename, $defaultContent);
        if ($template != '' && count($tokens) > 0) {
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
     * @param array $defaultValue Default [].
     * @return array
     */
    public static function getJson($filename, array $defaultValue = [])
    {
        if (!Str::endsWith($filename, '.json')) {
            $filename .= '.json';
        }
        $data = self::get($filename);
        if ($data == '') {
            return $defaultValue;
        }
        $data = json_decode($data, true);
        if (is_null($data) || $data === false) {
            $data = [];
        }
        return $data;
    }

    /**
     * Save json.
     *
     * @param string $filename
     * @param array $data
     * @param boolean $prettyPrint Default true.
     */
    public static function putJson($filename, array $data, $prettyPrint = true)
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
     * @return boolean
     */
    public static function delete($filename)
    {
        return @unlink($filename);
    }

    /**
     * Copy.
     *
     * @param string $filename
     * @param string $path
     * @return boolean
     */
    public static function copy($filename, $path)
    {
        return @copy($filename, $path . '/' . self::basename($filename));
    }

    /**
     * Move.
     *
     * @param string $filename
     * @param string $path
     * @return boolean
     */
    public static function move($filename, $path)
    {
        return @rename($filename, $path . '/' . self::basename($filename));
    }

    /**
     * Name.
     *
     * @param string $path
     * @return mixed
     */
    public static function name($path)
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Basename.
     *
     * @param string $path
     * @return mixed
     */
    public static function basename($path)
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }

    /**
     * Dirname.
     *
     * @param string $path
     * @return mixed
     */
    public static function dirname($path)
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    /**
     * Extension.
     *
     * @param string $path
     * @return mixed
     */
    public static function extension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Type.
     *
     * @param string $path
     * @return string
     */
    public static function type($path)
    {
        return @filetype($path);
    }

    /**
     * Mimetype.
     *
     * @param string $path
     * @return mixed
     */
    public static function mimetype($path)
    {
        return @finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
    }

    /**
     * Size.
     *
     * @param string $path
     * @return integer
     */
    public static function size($path)
    {
        return filesize($path);
    }

    /**
     * Last modified.
     *
     * @param string $path
     * @return integer
     */
    public static function lastModified($path)
    {
        clearstatcache();
        return filemtime($path);
    }

    /**
     * Is file.
     *
     * @param string $path
     * @return boolean
     */
    public static function isFile($path)
    {
        return is_file($path);
    }
}