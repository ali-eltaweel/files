<?php

namespace Files\Handles;

use Files\Path;

/**
 * Socket Handle.
 * 
 * @api
 * @since 1.1.0
 * @version 1.0.0
 * @package files
 * @author Ali M. Kamel <ali.kamel.dev@gmail.com>
 */
class SocketHandle extends Handle {

    /**
     * Creates a new socket handle.
     * 
     * @api
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param Path $path
     * @param int $domain
     * @param int $type
     * @param int $protocol
     */
    public function __construct(Path $path, int $domain, int $type, int $protocol) {

        parent::__construct($path, compact('domain', 'type', 'protocol'));
    }

    /**
     * Binds the socket to the specified path.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return bool
     */
    public final function bind(): bool {

        return socket_bind($this->handle, $this->path);
    }

    /**
     * Connects the socket to the specified path.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return bool
     */
    public final function connect(): bool {

        return socket_connect($this->handle, $this->path);
    }

    /**
     * Listens for incoming connections on the socket.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param int $backlog Maximum number of pending connections that can be queued for acceptance.
     * @return bool
     */
    public final function listen(int $backlog = SOMAXCONN): bool {

        return socket_listen($this->handle, $backlog);
    }

    /**
     * Accepts an incoming connection on the socket.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return SocketHandle
     */
    public final function accept(): self {

        $client = socket_accept($this->handle);

        $handle = new self(
            $this->path,
            $this->options['domain'],
            $this->options['type'],
            $this->options['protocol']
        );

        $handle->handle = $client;

        return $handle;
    }

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

        try {

            socket_close($this->handle);
            return true;

        } catch (\Throwable $e) { return false; }

    }

    /**
     * Reads data from the socket.
     * 
     * @api
     * @final
     * @oevrride
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param int|null $length The number of bytes to read. If null or less than or equal to zero, nothing will be read.
     * @return ?string
     */
    public final function read(int $length = 1024, int $mode = PHP_BINARY_READ): ?string {

        $message = socket_read($this->handle, $length, $mode);

        return $message === false ? null : $message;
    }

    /**
     * Writes data to the socket.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param string $content
     * @param ?int $length
     * @return int
     */
    public final function write(string $content, ?int $length = null): int {

        return socket_write($this->handle, $content, $length);
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

        return socket_create($this->options['domain'], $this->options['type'], $this->options['protocol']);
    }
}
