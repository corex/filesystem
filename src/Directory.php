<?php

namespace CoRex\Filesystem;

class Directory
{
    const TYPE_DIRECTORY = 'dir';
    const TYPE_LINK = 'link';
    const TYPE_FILE = 'file';

    /**
     * Check if directory exists.
     *
     * @param string $path
     * @return boolean
     */
    public static function exist($path)
    {
        return is_dir($path);
    }

    /**
     * Check if it is a directory entry.
     *
     * @param string $path
     * @return boolean
     */
    public static function isDirectory($path)
    {
        return is_dir($path);
    }

    /**
     * Check if directory is writable.
     *
     * @param string $path
     * @return boolean
     */
    public static function isWritable($path)
    {
        return is_writable($path);
    }

    /**
     * Make directory.
     *
     * @param string $path
     * @param integer $mode See mkdir() for options.
     */
    public static function make($path, $mode = 0777)
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
     * @param string|array $types List of types to return. Use constants Directory::TYPE_*. Default [] which means all.
     * @param boolean $recursive Default false.
     * @param array $attributes Default []. Used internally.
     * @return array
     */
    public static function entries($path, $criteria, $types = [], $recursive = false, array $attributes = [])
    {
        if (count($attributes) == 0) {
            $attributes = [
                'pathRoot' => $path
            ];
        }
        $entries = [];
        if (!is_dir($path)) {
            return $entries;
        }

        if (is_string($types)) {
            $types = [$types];
        }
        if (count($types) == 0) {
            $types = [self::TYPE_DIRECTORY, self::TYPE_LINK, self::TYPE_FILE];
        }

        if ($handle = opendir(rtrim($path, '/'))) {
            while ($entryName = readdir($handle)) {

                // Validate entry.
                if (substr($entryName, 0, 1) == '.') {
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
                if ($type != Directory::TYPE_LINK) {
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
                if ($recursive && $type == self::TYPE_DIRECTORY) {
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
     * @param boolean $preserveRoot Default false.
     * @return boolean
     */
    public static function delete($path, $preserveRoot = false)
    {
        // Ensure that we are not doing something stupid.
        if (!is_string($path) || trim($path) == '' || trim($path) == '/') {
            return false;
        }

        if (!self::isDirectory($path)) {
            return false;
        }

        // Loop through entries.
        $entries = self::entries($path, '*', [], true);
        foreach ($entries as $entry) {
            $filename = $entry['path'] . '/' . $entry['name'];
            if ($entry['type'] == self::TYPE_DIRECTORY) {
                self::delete($filename);
                @rmdir($filename);
            } elseif ($entry['type'] == self::TYPE_LINK) {
                File::delete($filename);
            } elseif ($entry['type'] == self::TYPE_FILE) {
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
     * @return boolean
     */
    public static function clean($path)
    {
        return self::delete($path, true);
    }

    /**
     * Get temp directory.
     *
     * @return string
     */
    public static function temp()
    {
        return sys_get_temp_dir();
    }
}