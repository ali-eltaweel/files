<?php

namespace Files\Handles;

use Files\Path;

/**
 * Socket Handle.
 * 
 * @api
 * @since 1.1.0
 * @version 1.1.0
 * @package files
 * @author Ali M. Kamel <ali.kamel.dev@gmail.com>
 * 
 * @property \Socket $handle
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
     * @version 1.1.0
     * 
     * @return bool
     */
    public final function bind(): bool {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Binding socket' => [ 'address' => "$this->path" ]
        ], $logUnit);

        $bound = socket_bind($this->handle, $this->path);

        $this->debugLog(fn () => [
            'Binding socket' => [ 'address' => "$this->path", 'bound' => $bound ]
        ], $logUnit);

        return $bound;
    }

    /**
     * Connects the socket to the specified path.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return bool
     */
    public final function connect(): bool {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Connecting socket' => [ 'address' => "$this->path" ]
        ], $logUnit);

        $connected = socket_connect($this->handle, $this->path);

        $this->debugLog(fn () => [
            'Connecting socket' => [ 'address' => "$this->path", 'connected' => $connected ]
        ], $logUnit);

        return $connected;
    }

    /**
     * Listens for incoming connections on the socket.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param int $backlog Maximum number of pending connections that can be queued for acceptance.
     * @return bool
     */
    public final function listen(int $backlog = SOMAXCONN): bool {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Listening to socket' => [ 'address' => "$this->path", 'backlog' => $backlog ]
        ], $logUnit);

        $listening = socket_listen($this->handle, $backlog);

        $this->debugLog(fn () => [
            'Listening to socket' => [ 'address' => "$this->path", 'backlog' => $backlog, 'listening' => $listening ]
        ], $logUnit);

        return $listening;
    }

    /**
     * Accepts an incoming connection on the socket.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return SocketHandle
     */
    public final function accept(): self {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Accepting connection' => [ 'address' => "$this->path" ]
        ], $logUnit);

        $client = socket_accept($this->handle);

        $handle = new self(
            $this->path,
            $this->options['domain'],
            $this->options['type'],
            $this->options['protocol']
        );

        $handle->handle = $client;
        $handle->setLogger($this->logger);

        $this->debugLog(fn () => [
            'Connection accepted' => [ 'address' => "$this->path", 'client' => [ 'type' => $handle::class, 'id' => spl_object_id($handle) ] ]
        ], $logUnit);

        return $handle;
    }

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
            'Closing socket handle' => [ 'path' => $this->path->path ]
        ], $logUnit);

        try {

            socket_close($this->handle);

            $this->debugLog(fn () => [
                'Socket handle closed' => [ 'path' => $this->path->path ]
            ], $logUnit);

            return true;

        } catch (\Throwable $e) {

            $this->warningLog(fn () => [
                'Failed to close socket handle' => [ 'path' => $this->path->path ]
            ], $logUnit);
            
            return false;
        }

    }

    /**
     * Reads data from the socket.
     * 
     * @api
     * @final
     * @oevrride
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param int|null $length The number of bytes to read. If null or less than or equal to zero, nothing will be read.
     * @return ?string
     */
    public final function read(int $length = 1024, int $mode = PHP_BINARY_READ): ?string {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Reading socket handle' => [ 'path' => $this->path->path, 'length' => $length, 'mode' => $mode ]
        ], $logUnit);

        $message = socket_read($this->handle, $length, $mode);

        if ($message === false) {

            $this->warningLog(fn () => [
                'Failed to read socket handle' => [
                    'path'   => $this->path->path,
                    'length' => $length,
                    'mode'   => $mode
                ]
            ], $logUnit);

            return null;
        }

        $this->debugLog(fn () => [
            'Reading socket handle' => [
                'path'         => $this->path->path,
                'length'       => $length,
                'mode'         => $mode,
                'actualLength' => function_exists('mb_strlen') ? mb_strlen($message) : strlen($message)
            ]
        ], $logUnit);

        return $message;
    }

    /**
     * Writes data to the socket.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param string $content
     * @param ?int $length
     * @return int
     */
    public final function write(string $content, ?int $length = null): int {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Writing to socket handle' => [ 'path' => $this->path->path, 'length' => $length ]
        ], $logUnit);
        $this->debugLog(fn () => [
            'Writing to socket handle' => [
                'path'        => $this->path->path,
                'length'      => $length,
                'totalLength' => function_exists('mb_strlen') ? mb_strlen($content) : strlen($content)
            ]
        ], $logUnit);

        $written = socket_write($this->handle, $content, $length);

        $this->debugLog(fn () => [
            'Writing to socket handle' => [
                'path'        => $this->path->path,
                'length'      => $length,
                'totalLength' => function_exists('mb_strlen') ? mb_strlen($content) : strlen($content),
                'written'     => $written
            ]
        ], $logUnit);

        return $written;
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
