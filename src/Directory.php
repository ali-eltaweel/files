<?php

namespace Files;

/**
 * Directory.
 * 
 * @api
 * @since 1.0.0
 * @version 1.0.0
 * @package files
 * @author Ali M. Kamel <ali.kamel.dev@gmail.com>
 */
class Directory extends File {

    /**
     * The file type corresponding to this file class.
     * 
     * @final
     * @internal
     * @override
     * @since 1.0.0
     * 
     * @var FileType FILE_TYPE
     */
    protected final const FILE_TYPE = FileType::Directory;

    /**
     * Creates a new directory relative to this directory.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param Path|string $name
     * @param int $permissions
     * @param bool $recursive
     * @return Directory|null
     */
    public final function mkdir(Path|string $name, int $permissions = 0777, bool $recursive = false): ?Directory {

        if (mkdir($path = $this->path->append($name), $permissions, $recursive)) {

            return new Directory($path);
        }

        return null;
    }

    /**
     * Removes the file from the filesystem.
     * 
     * @api
     * @final
     * @override
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param bool $force If true, recursively removes all files and directories within this directory before removing it.
     * @return bool Returns true on success, false on failure.
     */
    public final function remove(bool $force = false): bool {

        if ($force) {

            $this->foreachChild(function(File $file) {

                if ($file instanceof Directory) {
                    $file->remove(true);
                } else {
                    $file->remove();
                }
            });
        }

        return rmdir($this->path);
    }

    /**
     * Iterates over each child file in the directory and applies the given callback.
     * 
     * @api
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param callable(File): void $callback
     * @return void
     */
    public function foreachChild(callable $callback): void {

        $handle = $this->open();

        while (($entry = $handle->read()) !== null) {

            if ($entry === '.' || $entry === '..') {
                
                continue;
            }

            $callback(File::make($this->path->append($entry)));
        }
    }

    /**
     * Opens the file and returns a handle to it.
     * 
     * @api
     * @override
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return Handles\DirectoryHandle Returns a handle to the opened file.
     */
    public function open(): Handles\DirectoryHandle {

        return new Handles\DirectoryHandle($this->path);
    }
}
