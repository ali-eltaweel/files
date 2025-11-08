<?php

namespace Files;

/**
 * Directory.
 * 
 * @api
 * @since 1.0.0
 * @version 1.1.0
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
     * @version 1.1.0
     * 
     * @param Path|string $name
     * @param int $permissions
     * @param bool $recursive
     * @return Directory|null
     */
    public final function mkdir(Path|string $name, int $permissions = 0777, bool $recursive = false): ?Directory {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [ 'Creating directory' => [
            'path' => $this->path->path, 'name' => $name, 'permissions' => $permissions, 'recursive' => $recursive
        ] ], $logUnit);

        if (mkdir($path = $this->path->append($name), $permissions, $recursive)) {

            $dir = new Directory($path);

            $this->debugLog(fn () => [
                'Creating directory' => [
                    'path'        => $this->path->path,
                    'name'        => $name,
                    'permissions' => $permissions,
                    'recursive'   => $recursive,
                    'directory'   => [ 'class' => $dir::class, 'id' => spl_object_id($dir) ]
                ]
            ], $logUnit);

            return $dir;
        }

        $this->debugLog(fn () => [
            'Creating directory' => [
                'path'        => $this->path->path,
                'name'        => $name,
                'permissions' => $permissions,
                'recursive'   => $recursive,
                'directory'   => null
            ]
        ], $logUnit);

        return null;
    }

    /**
     * Removes the file from the filesystem.
     * 
     * @api
     * @final
     * @override
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param bool $force If true, recursively removes all files and directories within this directory before removing it.
     * @return bool Returns true on success, false on failure.
     */
    public final function remove(bool $force = false): bool {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Removing directory' => [ 'path' => $this->path->path, 'force' => $force ]
        ], $logUnit);

        if ($force) {

            $this->foreachChild(function(File $file) {

                if ($file instanceof Directory) {
                    $file->remove(true);
                } else {
                    $file->remove();
                }
            });
        }

        $removed = rmdir($this->path);

        $this->debugLog(fn () => [
            'Removing directory' => [ 'path' => $this->path->path, 'force' => $force, 'removed' => $removed ]
        ], $logUnit);

        return $removed;
    }

    /**
     * Iterates over each child file in the directory and applies the given callback.
     * 
     * @api
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param callable(File): void $callback
     * @return void
     */
    public function foreachChild(callable $callback): void {

        $handle = $this->open();

        $handle->setLogger($this->logger);

        while (($entry = $handle->read()) !== null) {

            if ($entry === '.' || $entry === '..') {
                
                continue;
            }

            $file = File::make($this->path->append($entry));

            $file->setLogger($this->logger);

            $callback($file);
        }

        $handle->close();
    }

    /**
     * Opens the file and returns a handle to it.
     * 
     * @api
     * @override
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return Handles\DirectoryHandle Returns a handle to the opened file.
     */
    public function open(): Handles\DirectoryHandle {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Opening directory' => [ 'path' => $this->path->path ]
        ], $logUnit);

        $handle = new Handles\DirectoryHandle($this->path);
        $handle->setLogger($this->logger);

        $this->debugLog(fn () => [
            'Opening directory' => [ 'path' => $this->path->path, 'handle' => [ 'class' => $handle::class, 'id' => spl_object_id($handle) ] ]
        ], $logUnit);

        return $handle;
    }
}
