<?php

namespace Files;

/**
 * Lockable Directory.
 * 
 * @api
 * @since 1.0.0
 * @version 1.2.0
 * @package files
 * @author Ali M. Kamel <ali.kamel.dev@gmail.com>
 * 
 * @method Handles\LockableDirectoryHandle open()
 */
class LockableDirectory extends Directory {

    /**
     * Opens the file and returns a handle to it.
     * 
     * @api
     * @final
     * @override
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param Path|string $lockFilename The name of the lock file to be created (default is '.lock').
     * @param string $lockFileOpeningMode The mode in which to open the lock file (default is 'w').
     * @param bool $removeLockFileOnUnlock Whether to remove the lock file when unlocking (default is true).
     * @return Handles\LockableDirectoryHandle Returns a handle to the opened file.
     */
    public final function open(Path|string $lockFilename = '.lock', string $lockFileOpeningMode = 'w', bool $removeLockFileOnUnlock = true): Handles\LockableDirectoryHandle {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Opening lockable directory' => [
                'path'                   => $this->path->path,
                'lockFileName'           => $lockFilename,
                'lockFileOpeningMode'    => $lockFileOpeningMode,
                'removeLockFileOnUnlock' => $removeLockFileOnUnlock
            ]
        ], $logUnit);

        $handle = new Handles\LockableDirectoryHandle(
            $this->path,
            $this->createLockFile($lockFilename),
            $lockFileOpeningMode,
            $removeLockFileOnUnlock
        );
        $handle->setLogger($this->logger);

        $this->debugLog(fn () => [
            'Opening lockable directory' => [
                'path'                   => $this->path->path,
                'lockFileName'           => $lockFilename,
                'lockFileOpeningMode'    => $lockFileOpeningMode,
                'removeLockFileOnUnlock' => $removeLockFileOnUnlock,
                'handle'                 => [ 'class' => $handle::class, 'id' => spl_object_id($handle) ]
            ]
        ], $logUnit);

        return $handle;
    }

    /**
     * Opens the file, lock it, performs the given work, and then unlocks the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param callable $work  The work to be done within the transaction.
     * @param Path|string $lockFilename The name of the lock file to be created (default is '.lock').
     * @param string $lockFileOpeningMode The mode in which to open the lock file (default is 'w').
     * @param bool $removeLockFileOnUnlock Whether to remove the lock file when unlocking (default is true).
     * @param Lock $lock The type of lock to use (default is Lock::Exclusive).
     * @return mixed Returns the result of the work done within the transaction.
     */
    public final function transaction(callable $work, Path|string $lockFilename = '.lock', string $lockFileOpeningMode = 'w', bool $removeLockFileOnUnlock = true, Lock $lock = Lock::Exclusive): mixed {

        $handle = $this->open($lockFilename, $lockFileOpeningMode, $removeLockFileOnUnlock);

        $handle->lock($lock);

        $result = $work($handle);

        $handle->unlock(close: true);

        return $result;
    }

    /**
     * Iterates over each child in the directory and applies the given callback.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.2.0
     * 
     * @param callable(File): void $callback
     * @return void
     */
    public final function foreachChild(callable $callback): void {

        $this->transaction(function(Handles\LockableDirectoryHandle $handle) use ($callback) {

            while (($entry = $handle->read()) !== null) {

                if ($entry === '.' || $entry === '..') {
                
                    continue;
                }

                $file = File::make($this->path->append($entry));

                $file->setLogger($this->logger);

                if ($callback($file) === false) {

                    $handle->close();
                    return;
                }
            }
        });
    }

    /**
     * Creates a lock file for the directory.
     * 
     * @internal
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param Path|string $lockFilename
     * @return RegularFile
     */
    protected function createLockFile(Path|string $lockFilename = '.lock'): RegularFile {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Creating lock file' => [ 'path' => $this->path->path, 'lockFilename' => $lockFilename ]
        ], $logUnit);

        $lockFile = new RegularFile($this->path->append($lockFilename));

        $this->debugLog(fn () => [
            'Creating lock file' => [
                'path'         => $this->path->path,
                'lockFilename' => $lockFilename,
                'lockFile'     => [ 'class' => $lockFile::class, 'id' => spl_object_id($lockFile) ]
            ]
        ], $logUnit);

        return $lockFile;
    }
}
