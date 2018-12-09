<?php

declare(strict_types=1);

namespace CoRex\Filesystem;

use CoRex\Helpers\Arr;

class Path
{
    /**
     * Get path to root of site.
     *
     * @param string|string[] $segments Dot notation is supported in string. Default null.
     * @return string
     */
    public static function root($segments = null): string
    {
        $path = __DIR__;
        for ($c1 = 0; $c1 < 4; $c1++) {
            $path = dirname($path);
        }
        $path = str_replace('\\', '/', $path);
        $path = self::addSegmentsToPath($path, $segments);
        return $path;
    }

    /**
     * Get path to current package.
     *
     * @param string|string[] $segments Dot notation is supported in string. Default null.
     * @return string
     */
    public static function packageCurrent($segments = null): string
    {
        return self::package(null, null, $segments);
    }

    /**
     * Get path to package.
     * Note: if both $vendor and $package is null, current package is returned.
     *
     * @param string $vendor Default null which means current.
     * @param string $package Default null which means current.
     * @param string|string[] $segments Dot notation is supported in string. Default null.
     * @return string
     */
    public static function package(?string $vendor = null, ?string $package = null, $segments = null): string
    {
        $path = dirname(dirname(static::packagePath()));
        if ($package === null) {
            $package = static::packageName();
        }
        if ($vendor === null) {
            $vendor = static::vendorName();
        }
        $path .= '/' . $vendor . '/' . $package;
        $path = self::addSegmentsToPath($path, $segments);
        return $path;
    }

    /**
     * Get vendor name.
     *
     * @return string
     */
    public static function vendorName(): string
    {
        $path = static::packagePath();
        return basename(dirname($path));
    }

    /**
     * Get package name.
     *
     * @return string
     */
    public static function packageName(): string
    {
        $path = static::packagePath();
        return basename($path);
    }

    /**
     * Get package path.
     * Note: if this class is extended, this method has to be overridden to give the base path.
     *
     * @return string
     */
    protected static function packagePath(): string
    {
        return dirname(__DIR__);
    }

    /**
     * Add segments to path.
     *
     * @param string $path
     * @param string|string[] $segments Dot notation is supported in string.
     * @return string
     */
    private static function addSegmentsToPath(string $path, $segments): string
    {
        $segments = Arr::toArray($segments);
        if (count($segments) > 0) {
            $path .= '/' . implode('/', $segments);
        }
        return $path;
    }
}
