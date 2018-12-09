<?php

declare(strict_types=1);

namespace CoRex\Filesystem;

class Directory
{
    public const TYPE_DIRECTORY = 'dir';
    public const TYPE_LINK = 'link';
    public const TYPE_FILE = 'file';

    /**
     * Check if directory exists.
     *
     * @param string $path
     * @return bool
     */
    public static function exist(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * Check if it is a directory entry.
     *
     * @param string $path
     * @return bool
     */
    public static function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * Check if directory is writable.
     *
     * @param string $path
     * @return bool
     */
    public static function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    /**
     * Make directory.
     *
     * @param string $path
     * @param int $mode See mkdir() for options.
     */
    public static function make(string $path, int $mode = 0777): void
    {
        if (!is_dir($path)) {
            mkdir($path, $mode, true);
        }
    }

    /**
     * Get entries in directory.
     *
     * @param string $path
     * @param string $criteria
     * @param string|string[] $types List of types to return. Use Directory::TYPE_*. Default [] which means all.
     * @param bool $recursive Default false.
     * @param string[] $attributes Default []. Used internally.
     * @return string[]
     */
    public static function entries(
        ?string $path,
        string $criteria,
        $types = [],
        bool $recursive = false,
        array $attributes = []
    ): array {
        if (count($attributes) === 0) {
            $attributes = [
                'pathRoot' => $path
            ];
        }
        $entries = [];
        if ($path === null || !is_dir($path)) {
            return $entries;
        }

        if (is_string($types)) {
            $types = [$types];
        }
        if (count($types) === 0) {
            $types = [self::TYPE_DIRECTORY, self::TYPE_LINK, self::TYPE_FILE];
        }

        $handle = opendir(rtrim($path, '/'));
        if ($handle) {
            while ($entryName = readdir($handle)) {

                // Validate entry.
                if (substr($entryName, 0, 1) === '.') {
                    continue;
                }
                if (!fnmatch($criteria, $entryName)) {
                    continue;
                }

                // Determine type.
                if (is_dir($path . '/' . $entryName)) {
                    $type = self::TYPE_DIRECTORY;
                } elseif (is_link($path . '/' . $entryName)) {
                    $type = self::TYPE_LINK;
                } else {
                    $type = self::TYPE_FILE;
                }

                // Get file modified time.
                if ($type !== self::TYPE_LINK) {
                    $modified = filemtime($path . '/' . $entryName);
                } else {
                    $modified = 0;
                }

                // Prepare entry.
                $info = pathinfo($path . '/' . $entryName);
                $entry = [
                    'name' => $entryName,
                    'path' => $path,
                    'basename' => isset($info['basename']) ? $info['basename'] : '',
                    'filename' => isset($info['filename']) ? $info['filename'] : '',
                    'extension' => isset($info['extension']) ? $info['extension'] : '',
                    'modified' => $modified,
                    'type' => $type
                ];

                // Add attributes.
                foreach ($attributes as $attribute => $value) {
                    $entry[$attribute] = $value;
                }

                // Determine level.
                $pathRelative = substr($entry['path'], strlen($entry['pathRoot']));
                $entry['level'] = substr_count($pathRelative, '/');

                // Add to list.
                if (in_array($type, $types)) {
                    $entries[] = $entry;
                }

                // Recursive.
                if ($recursive && $type === self::TYPE_DIRECTORY) {
                    $recursiveEntries = static::entries(
                        $path . '/' . $entryName,
                        $criteria,
                        $types,
                        $recursive,
                        $attributes
                    );
                    $entries = array_merge($entries, $recursiveEntries);
                }
            }
            closedir($handle);
        }
        return $entries;
    }

    /**
     * Delete.
     *
     * @param string $path
     * @param bool $preserveRoot Default false.
     * @return bool
     */
    public static function delete(?string $path, bool $preserveRoot = false): bool
    {
        // Ensure that we are not doing something stupid.
        if (!is_string($path) || trim($path) === '' || trim($path) === '/') {
            return false;
        }

        if (!self::isDirectory($path)) {
            return false;
        }

        // Loop through entries.
        $entries = self::entries($path, '*', [], true);
        foreach ($entries as $entry) {
            $filename = $entry['path'] . '/' . $entry['name'];
            if ($entry['type'] === self::TYPE_DIRECTORY) {
                self::delete($filename);
                @rmdir($filename);
            } elseif ($entry['type'] === self::TYPE_LINK) {
                File::delete($filename);
            } elseif ($entry['type'] === self::TYPE_FILE) {
                File::delete($filename);
            }
        }

        // Remote root.
        if (!$preserveRoot) {
            @rmdir($path);
        }

        return true;
    }

    /**
     * Clean directory.
     *
     * @param string $path
     * @return bool
     */
    public static function clean(string $path): bool
    {
        return self::delete($path, true);
    }

    /**
     * Get temp directory.
     *
     * @return string
     */
    public static function temp(): string
    {
        return sys_get_temp_dir();
    }
}