<?php

namespace Files\Handles;

use Files\Lock;

use Lang\VirtualProperties;
use Lang\Annotations\{ Computes, Sets };

/**
 * Lockable-File Handle.
 * 
 * @api
 * @since 1.0.0
 * @version 1.1.0
 * @package files
 * @author Ali M. Kamel <ali.kamel.dev@gmail.com>
 * 
 * @property Lock|null $lock
 */
trait LockableHandle {

    use VirtualProperties;

    /**
     * The current lock associated with this handle.
     * 
     * @internal
     * @since 1.0.0
     * 
     * @var Lock|null $lock
     */
    private ?Lock $lock = null;

    /**
     * Gets the current lock associated with this handle.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return Lock|null
     */
    #[Computes('lock')]
    public final function getLock(): ?Lock {
        
        return $this->lock;
    }

    /**
     * Sets the current lock associated with this handle.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param Lock|null $lock
     * @return static
     */
    #[Sets('lock')]
    public final function setLock(?Lock $lock): static {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [ 'Setting lock' => [ 'lock' => $lock?->name ] ], $logUnit);

        if ($lock == $this->lock) {

            return $this;
        }
        
        if (is_null($lock) || !is_null($this->lock)) {

            $this->doUnlock();
        }

        if (!is_null($lock)) {

            $this->doLock($lock);
        }

        $this->lock = $lock;

        $this->debugLog(fn () => [ 'Lock set' => [ 'lock' => $lock?->name ] ], $logUnit);
        
        return $this;
    }

    /**
     * Checks if the handle is currently locked.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return bool
     */
    public final function isLocked(): bool {

        return !is_null($this->lock);
    }

    /**
     * Locks the handle with the given lock.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param Lock $lock
     */
    public final function lock(Lock $lock): static {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [ 'Acquiring lock' => [ 'lock' => $lock->name ] ], $logUnit);

        $this->setLock($lock);

        $this->debugLog(fn () => [ 'Acquiring lock' => [ 'lock' => $lock->name ] ], $logUnit);

        return $this;
    }
    
    /**
     * Unlocks the handle and optionally closes it.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param bool $close
     * @return static
     */
    public final function unlock(bool $close = true): static {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [ 'Releasing lock' ], $logUnit);

        $this->setLock(null);

        $this->infoLog(fn () => [ 'Lock released' ], $logUnit);

        if ($close) {

            $this->close();
        }

        return $this;
    }
    
    /**
     * Performs the actual locking operation.
     * 
     * @internal
     * @abstract
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param Lock $lock
     * @return void
     */
    protected abstract function doLock(Lock $lock): void;
    
    /**
     * Performs the actual unlocking operation.
     * 
     * @internal
     * @abstract
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return void
     */
    protected abstract function doUnlock(): void;
}
