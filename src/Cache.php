<?php

namespace CoRex\Filesystem;

class Cache
{
    private static $marker = '||';
    private static $path;
    private static $seconds = 0;

    /**
     * Generate key based on input key and parameters. Used in more sophisticated keys.
     *
     * @param string $key
     * @param array $params Default [].
     * @return string
     */
    public static function key($key, array $params = [])
    {
        $key = md5(serialize($key));
        if (count($params) > 0) {
            $key .= '-' . md5(serialize($params));
        }
        return $key;
    }

    /**
     * Set/get path for storages.
     *
     * @param string $path Default null which means not set.
     * @param boolean $force Default false.
     * @return string If null, then not set.
     * @throws \Exception
     */
    public static function path($path = null, $force = false)
    {
        if ($path !== null || $force) {
            if ($path !== null) {
                if (!is_writeable($path)) {
                    throw new \Exception('Path is not writable.');
                }
            }
            self::$path = $path !== null ? rtrim($path, '/') : $path;
        }
        return self::$path;
    }

    /**
     * Set/get lifetime.
     *
     * @param string $lifetime Add 'm' for minutes, 'h' for hours. If not specified, seconds are assumed.
     * @param string $storage Default 'global'.
     * @return integer Lifetime in seconds.
     */
    public static function lifetime($lifetime = null, $storage = 'global')
    {
        if ($lifetime !== null) {
            $seconds = strtolower($lifetime);

            // Convert to minutes if hour.
            if (substr($seconds, -1) == 'h') {
                $seconds = (intval($seconds) * 60) . 'm';
            }

            // Convert to seconds if minutes.
            if (substr($seconds, -1) == 'm') {
                $seconds = (intval($seconds) * 60) . 's';
            }

            if (!is_array(self::$seconds)) {
                self::$seconds = [];
            }
            self::$seconds[$storage] = intval($seconds);
        }
        if (isset(self::$seconds[$storage])) {
            return self::$seconds[$storage];
        }
        return 0;
    }

    /**
     * Get expiration from key.
     *
     * @param string $key
     * @param string $storage Default 'global'.
     * @return integer If not exist, 0 is returned.
     */
    public static function expiration($key, $storage = 'global')
    {
        if (!self::has($key)) {
            return null;
        }

        // Get content.
        $fileKey = self::key($key);
        $content = null;
        if (file_exists(self::$path . '/' . $storage . '/' . $fileKey)) {
            $content = file_get_contents(self::$path . '/' . $storage . '/' . $fileKey);
        }

        // Extract expiration.
        $markerPos = strpos($content, self::$marker);
        $expiration = 0;
        if ($markerPos !== false) {
            $expiration = substr($content, 0, $markerPos);
        }

        return $expiration;
    }

    /**
     * Get cache.
     *
     * @param string $key
     * @param mixed $defaultValue Default null.
     * @param string $storage Default 'global'.
     * @return mixed|null
     */
    public static function get($key, $defaultValue = null, $storage = 'global')
    {
        if (!self::has($key)) {
            return $defaultValue;
        }

        // Get content.
        $fileKey = self::key($key);
        $content = null;
        if (file_exists(self::$path . '/' . $storage . '/' . $fileKey)) {
            $content = file_get_contents(self::$path . '/' . $storage . '/' . $fileKey);
        }

        // Extract expiration.
        $markerPos = strpos($content, self::$marker);
        $expiration = null;
        if ($markerPos !== false) {
            $expiration = substr($content, 0, $markerPos);
        }

        // Extract content.
        if ($expiration < time()) {
            self::forget($key);
        } else {
            return unserialize(substr($content, $markerPos + strlen(self::$marker)));
        }

        return $defaultValue;
    }

    /**
     * Put cache.
     *
     * @param string $key
     * @param mixed $value
     * @param string $storage Default 'global'.
     * @throws \Exception
     */
    public static function put($key, $value, $storage = 'global')
    {
        self::initialize($storage);
        $expiration = time() + intval(self::$seconds);
        $value = $expiration . self::$marker . serialize($value);
        $fileKey = self::key($key);
        @file_put_contents(self::$path . '/' . $storage . '/' . $fileKey, $value);
    }

    /**
     * Has key.
     *
     * @param string $key
     * @param string $storage Default 'global'.
     * @return boolean
     */
    public static function has($key, $storage = 'global')
    {
        $fileKey = self::key($key);
        return file_exists(self::$path . '/' . $storage . '/' . $fileKey);
    }

    /**
     * Forget key.
     *
     * @param string $key
     * @param string $storage Default 'global'.
     */
    public static function forget($key, $storage = 'global')
    {
        $fileKey = self::key($key);
        if (self::has($key)) {
            @unlink(self::$path . '/' . $storage . '/' . $fileKey);
        }
    }

    /**
     * Flush storage.
     *
     * @param string $storage Default 'global'.
     */
    public static function flush($storage = 'global')
    {
        $filenames = scandir(self::$path . '/' . $storage);
        if (count($filenames) > 0) {
            foreach ($filenames as $filename) {
                if (substr($filename, 0, 1) == '.') {
                    continue;
                }
                @unlink(self::$path . '/' . $storage . '/' . $filename);
            }
        }
    }

    /**
     * Initialize.
     *
     * @param string $storage
     * @throws \Exception
     */
    private static function initialize($storage)
    {
        if (self::$path === null) {
            throw new \Exception('Path not set.');
        }
        if (self::lifetime(null, $storage) == 0) {
            throw new \Exception('Lifetime not set.');
        }
        Directory::make(self::$path . '/' . $storage);
    }
}