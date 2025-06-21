<?php

namespace Files\Handles;

use Files\Stat;

/**
 * Directory Handle.
 * 
 * @api
 * @since 1.0.0
 * @version 1.0.0
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
     * @version 1.0.0
     * 
     * @return void
     */
    public final function close(): bool {

        closedir($this->handle);

        return true;
    }

    /**
     * Gets the stat information of the handle.
     * 
     * @api
     * @final
     * @override
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return Stat|null Returns a Stat object containing detailed information about the handle, or null if the handle is not valid.
     */
    public final function getStat(): ?Stat {
        
        return new Stat($this->path);
    }

    /**
     * Reads data from the handle.
     * 
     * @api
     * @final
     * @oevrride
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return ?string
     */
    public final function read(): ?string {

        $entry = readdir($this->handle);

        return $entry === false ? null : $entry;
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
