<?php

namespace Files;

use BadMethodCallException;

/**
 * Socket.
 * 
 * @api
 * @since 1.1.0
 * @version 1.0.0
 * @package files
 * @author Ali M. Kamel <ali.kamel.dev@gmail.com>
 * 
 * @property string $content
 */
class Socket extends File {

    /**
     * Opens the file and returns a handle to it.
     * 
     * @api
     * @final
     * @abstract
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return Handles\SocketHandle Returns a handle to the opened file.
     */
    public final function open(int $domain = AF_UNIX, int $type = SOCK_STREAM, int $protocol = 0): Handles\SocketHandle {

        return new Handles\SocketHandle($this->path, $domain, $type, $protocol);
    }

    /**
     * Copies the file to a new location.
     * 
     * @api
     * @final
     * @override
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param Path|string $target The target path where the file should be copied.
     * 
     * @throws BadMethodCallException
     */
    public final function copy(Path|string $target): bool {

        throw new BadMethodCallException('Copying a socket file is not supported.');
    }
}
