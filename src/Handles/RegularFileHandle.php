<?php

namespace Files\Handles;

use Files\{ Lock, Path };

use Lang\Annotations\{ Computes, Sets };

/**
 * Regular-File Handle.
 * 
 * @api
 * @since 1.0.0
 * @version 1.1.0
 * @package files
 * @author Ali M. Kamel <ali.kamel.dev@gmail.com>
 * 
 * @property int     $position
 * @property ?string $content
 */
class RegularFileHandle extends Handle {

    use LockableHandle;

    /**
     * Creates a new regular file handle.
     * 
     * @api
     * @override
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param \Files\Path $path
     * @param string $mode
     */
    public function __construct(Path $path, string $mode = 'r') {

        parent::__construct($path, compact('mode'));
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
            'Closing handle' => [ 'path' => $this->path->path, 'mode' => $this->options['mode'] ]
        ], $logUnit);

        $closed = fclose($this->handle);

        $this->debugLog(fn () => [
            'Closing handle' => [ 'path' => $this->path->path, 'mode' => $this->options['mode'], 'closed' => $closed ]
        ], $logUnit);

        return $closed;
    }

    /**
     * Reads data from the handle.
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
    public final function read(?int $length = null): ?string {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Reading file handle' => [ 'path' => $this->path->path, 'length' => $length ]
        ], $logUnit);

        if (is_null($length) || $length <= 0) {

            $this->noticeLog(fn () => [
                'Skipping file handle reading; zero length' => [ 'path' => $this->path->path, 'length' => $length ]
            ], $logUnit);

            return null;
        }

        $text = fread($this->handle, $length);

        if ($text === false) {

            $this->warningLog(fn () => [
                'Failed to read file handle' => [
                    'path'   => $this->path->path,
                    'length' => $length
                ]
            ], $logUnit);

            return null;
        }

        $this->debugLog(fn () => [
            'Reading file handle' => [
                'path'         => $this->path->path,
                'length'       => $length,
                'actualLength' => function_exists('mb_strlen') ? mb_strlen($text) : strlen($text)
            ]
        ], $logUnit);

        return $text;
    }

    /**
     * Reads a single line from the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param mixed $length
     * @return bool|string|null
     */
    public final function readline(?int $length = null): ?string {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Reading line from file handle' => [ 'path' => $this->path->path, 'length' => $length ]
        ], $logUnit);

        $text = fgets($this->handle, $length);

        if ($text === false) {

            $this->warningLog(fn () => [
                'Failed to read line from file handle' => [
                    'path'   => $this->path->path,
                    'length' => $length
                ]
            ], $logUnit);

            return null;
        }

        $this->debugLog(fn () => [
            'Reading line from file handle' => [
                'path'         => $this->path->path,
                'length'       => $length,
                'actualLength' => function_exists('mb_strlen') ? mb_strlen($text) : strlen($text)
            ]
        ], $logUnit);

        return $text;
    }

    /**
     * Sets the position of the handle.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param int $position
     * @return RegularFileHandle
     */
    #[Sets('position')]
    public final function setPosition(int $position): static {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Setting file handle position' => [ 'path' => $this->path->path, 'position' => $position ]
        ], $logUnit);

        if ($this->seek($position, SEEK_SET)) {

            $this->debugLog(fn () => [
                'Setting file handle position' => [ 'path' => $this->path->path, 'position' => $position ]
            ], $logUnit);
        } else {
            $this->warningLog(fn () => [
                'Failed to set file handle position' => [ 'path' => $this->path->path, 'position' => $position ]
            ], $logUnit);
        }

        return $this;
    }

    /**
     * Gets the current position of the handle.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return int
     */
    #[Computes('position')]
    public final function getPosition(): int {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Getting file handle position' => [ 'path' => $this->path->path ]
        ], $logUnit);

        $position = ftell($this->handle);

        if ($position === false) {

            $this->warningLog(fn () => [
                'Failed to get file handle position' => [ 'path' => $this->path->path ]
            ], $logUnit);

            return -1;
        }

        $this->debugLog(fn () => [
            'Getting file handle position' => [ 'path' => $this->path->path, 'position' => $position ]
        ], $logUnit);

        return $position;
    }

    /**
     * Sets the position of the handle.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param int $position
     * @param int $whence
     * @return bool
     */
    public final function seek(int $position, int $whence = SEEK_SET): bool {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Seeking file handle' => [ 'path' => $this->path->path, 'position' => $position, 'whence' => $whence ]
        ], $logUnit);

        if (0 === fseek($this->handle, $position, $whence)) {

            $this->debugLog(fn () => [
                'Seeking file handle' => [ 'path' => $this->path->path, 'position' => $position, 'whence' => $whence ]
            ], $logUnit);

            return true;
        }

        $this->warningLog(fn () => [
            'Failed to seek file handle' => [ 'path' => $this->path->path, 'position' => $position, 'whence' => $whence ]
        ], $logUnit);

        return false;
    }

    /**
     * Sets the content of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param string $content
     * @return int
     */
     #[Sets('content')]
    public final function setContent(string $content): int {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Setting file content' => [ 'path' => $this->path->path ]
        ], $logUnit);

        $this->debugLog(fn () => [
            'Setting file content' => [
                'path' => $this->path->path,
                'length' => function_exists('mb_strlen') ? mb_strlen($content) : strlen($content)
            ]
        ], $logUnit);

        $this->position = 0;

        $written = $this->write($content);

        $this->debugLog(fn () => [
            'Setting file content' => [
                'path'    => $this->path->path,
                'length'  => function_exists('mb_strlen') ? mb_strlen($content) : strlen($content),
                'written' => $written
            ]
        ], $logUnit);

        return $written;
    }

    /**
     * Gets the content of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return ?string
     */
    #[Computes('content')]
    public final function getContent(): ?string {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Getting file content' => [ 'path' => $this->path->path ]
        ], $logUnit);

        $oldPosition = $this->getPosition();

        $this->position = 0;

        $content = $this->read($this->stat->size);

        $this->position = $oldPosition;

        $this->debugLog(fn () => [
            'Getting file content' => [
                'path' => $this->path->path,
                'length' => function_exists('mb_strlen') ? mb_strlen($content) : strlen($content)
            ]
        ], $logUnit);

        return $content;
    }

    /**
     * Writes data to the file.
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
            'Writing to file handle' => [ 'path' => $this->path->path, 'length' => $length ]
        ], $logUnit);
        $this->debugLog(fn () => [
            'Writing to file handle' => [
                'path'        => $this->path->path,
                'length'      => $length,
                'totalLength' => function_exists('mb_strlen') ? mb_strlen($content) : strlen($content)
            ]
        ], $logUnit);

        $written = fwrite($this->handle, $content, $length);

        $this->debugLog(fn () => [
            'Writing to file handle' => [
                'path'        => $this->path->path,
                'length'      => $length,
                'totalLength' => function_exists('mb_strlen') ? mb_strlen($content) : strlen($content),
                'written'     => $written
            ]
        ], $logUnit);

        return $written;
    }

    /**
     * Writes a line to the file, appending a newline character at the end.
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
    public final function writeline(string $content, ?int $length = null): int {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Writing a line to file handle' => [ 'path' => $this->path->path, 'length' => $length ]
        ], $logUnit);
        $this->debugLog(fn () => [
            'Writing a line to file handle' => [
                'path'        => $this->path->path,
                'length'      => $length,
                'totalLength' => function_exists('mb_strlen') ? mb_strlen($content) : strlen($content)
            ]
        ], $logUnit);

        $written = fwrite($this->handle, explode("\n", $content)[0] . "\n", $length);

        $this->debugLog(fn () => [
            'Writing a line to file handle' => [
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

        return fopen($this->path, $this->options['mode']);
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

        flock($this->handle, match ($lock) { Lock::Shared => LOCK_SH, Lock::Exclusive => LOCK_EX });
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

        flock($this->handle, LOCK_UN);
    }
}
