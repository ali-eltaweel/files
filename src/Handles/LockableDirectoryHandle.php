<?php

namespace Files\Handles;

use Files\{ Lock, Path, RegularFile };

use Lang\{ Annotations\LazyInitialized, LazyProperties };

use Logger\Logger;

/**
 * Lockable Directory Handle.
 * 
 * @api
 * @since 1.0.0
 * @version 1.1.0
 * @package files
 * @author Ali M. Kamel <ali.kamel.dev@gmail.com>
 */
final class LockableDirectoryHandle extends DirectoryHandle {

    use LockableHandle { LockableHandle::__set as __setProperty; }
    use LazyProperties { LazyProperties::__set as __setLazyProperty; __get as getLazyProperty; __construct as __constructLazyProperties; }

    /**
     * The lock file handle.
     * 
     * @internal
     * @since 1.0.0
     * 
     * @var RegularFileHandle $lockFileHandle
     */
    #[LazyInitialized('createLockFileHandle')]
    private RegularFileHandle $lockFileHandle;

    /**
     * Creates a new lockable directory handle.
     * 
     * @api
     * @override
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param Path $path
     * @param RegularFile $lockFile
     * @param string $lockFileOpeningMode
     * @param bool $removeLockFileOnUnlock
     */
    public function __construct(Path $path, private RegularFile $lockFile, string $lockFileOpeningMode = 'w', bool $removeLockFileOnUnlock = true) {

        $this->__constructLazyProperties();

        parent::__construct($path, compact('lockFileOpeningMode', 'removeLockFileOnUnlock'));
    }

    /**
     * Retrieves the value of a property.
     * 
     * @api
     * @final
     * @override
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param string $name
     * @return mixed
     */
    public final function __get(string $name): mixed {

        if ($this->hasLazyProperty($name)) {

            return $this->getLazyProperty($name);
        }

        return parent::__get($name);
    }

    /**
     * Sets the value of a property.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public final function __set(string $name, mixed $value): void {

        if ($this->hasLazyProperty($name)) {

            $this->__setLazyProperty($name, $value);
            return;
        }

        $this->__setProperty($name, $value);
    }

    /**
     * Sets the logger instance.
     * 
     * @api
     * @final
     * @override
     * @since 1.1.0
     * @version 1.0.0
     * 
     * @param Logger|null $logger
     * @return void
     */
    public final function setLogger(?Logger $logger): void {

        parent::setLogger($logger);

        $this->lockFileHandle->setLogger($logger);
    }

    /**
     * Performs the actual locking operation.
     * 
     * @final
     * @internal
     * @override
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param Lock $lock
     * @return void
     */
    protected final function doLock(Lock $lock): void {

        $this->lockFileHandle->lock = $lock;
    }
    
    /**
     * Performs the actual unlocking operation.
     * 
     * @final
     * @internal
     * @override
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return void
     */
    protected final function doUnlock(): void {

        $this->lockFileHandle->lock = null;
        if ($this->options['removeLockFileOnUnlock']) {

            $this->lockFile->remove();
        }
    }

    /**
     * Opens the lock file.
     * 
     * @internal
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return Handle
     */
    private function createLockFileHandle(): RegularFileHandle {

        $handle = $this->lockFile->open($this->options['lockFileOpeningMode']);

        $handle->setLogger($this->logger);

        return $handle;
    }
}
