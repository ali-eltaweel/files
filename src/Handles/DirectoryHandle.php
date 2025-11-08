<?php

namespace Files\Handles;

use Files\Stat;

/**
 * Directory Handle.
 * 
 * @api
 * @since 1.0.0
 * @version 1.1.0
 * @package files
 * @author Ali M. Kamel <ali.kamel.dev@gmail.com>
 * 
 * @property-read Stat $stat
 */
class DirectoryHandle extends Handle {

    /**
     * Closes the handle.
     * 
     * @api
     * @final
     * @override
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return void
     */
    public final function close(): bool {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Closing directory handle' => [ 'path' => $this->path->path ]
        ], $logUnit);

        closedir($this->handle);

        $this->debugLog(fn () => [
            'Directory handle closed' => [ 'path' => $this->path->path ]
        ], $logUnit);

        return true;
    }

    /**
     * Gets the stat information of the handle.
     * 
     * @api
     * @final
     * @override
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return Stat|null Returns a Stat object containing detailed information about the handle, or null if the handle is not valid.
     */
    public final function getStat(): ?Stat {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Getting stats' => [ 'path' => $this->path->path ]
        ], $logUnit);
        
        $stats = new Stat($this->path);

        $this->debugLog(fn () => [
            'Getting stats' => [ 'path' => $this->path->path, 'stats' => $stats->toArray() ]
        ], $logUnit);

        return $stats;
    }

    /**
     * Reads data from the handle.
     * 
     * @api
     * @final
     * @override
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return ?string
     */
    public final function read(): ?string {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Reading directory entry' => [ 'path' => $this->path->path ]
        ], $logUnit);

        $entry = readdir($this->handle);

        if ($entry === false) {

            $this->warningLog(fn () => [
                'End of directory entries' => [ 'path' => $this->path->path ]
            ], $logUnit);

            return null;
        }

        $this->debugLog(fn () => [
            'Reading directory entry' => [ 'path' => $this->path->path, 'entry' => $entry ]
        ], $logUnit);

        return $entry;
    }

    /**
     * Open the handle.
     * 
     * @final
     * @internal
     * @override
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return resource
     */
    protected final function openHandle(): mixed {

        return opendir($this->path);
    }
}
