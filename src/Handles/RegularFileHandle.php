<?php

namespace Files\Handles;

use Files\{ Lock, Path };

use Lang\Annotations\{ Computes, Sets };

/**
 * Regular-File Handle.
 * 
 * @api
 * @since 1.0.0
 * @version 1.0.0
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
     * @version 1.0.0
     * 
     * @return void
     */
    public final function close(): bool {

        return fclose($this->handle);
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
     * @param int|null $length The number of bytes to read. If null or less than or equal to zero, nothing will be read.
     * @return ?string
     */
    public final function read(?int $length = null): ?string {

        if (is_null($length) || $length <= 0) return null;

        $text = fread($this->handle, $length);

        return $text === false ? null : $text;
    }

    /**
     * Reads a single line from the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param mixed $length
     * @return bool|string|null
     */
    public final function readline(?int $length = null): ?string {

        $text = fgets($this->handle, $length);

        return $text === false ? null : $text;
    }

    /**
     * Sets the position of the handle.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param int $position
     * @return RegularFileHandle
     */
    #[Sets('position')]
    public final function setPosition(int $position): static {

        $this->seek($position, SEEK_SET);

        return $this;
    }

    /**
     * Gets the current position of the handle.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return int
     */
    #[Computes('position')]
    public final function getPosition(): int {

        return ftell($this->handle);
    }

    /**
     * Sets the position of the handle.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param int $position
     * @param int $whence
     * @return bool
     */
    public final function seek(int $position, int $whence = SEEK_SET): bool {

        return 0 === fseek($this->handle, $position, $whence);
    }

    /**
     * Sets the content of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param string $content
     * @return int
     */
     #[Sets('content')]
    public final function setContent(string $content): int {

        $this->position = 0;

        return $this->write($content);
    }

    /**
     * Gets the content of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return ?string
     */
    #[Computes('content')]
    public final function getContent(): ?string {

        $oldPosition = $this->getPosition();

        $this->position = 0;

        $content = $this->read($this->stat->size);

        $this->position = $oldPosition;

        return $content;
    }

    /**
     * Writes data to the file.
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

        return fwrite($this->handle, $content, $length);
    }

    /**
     * Writes a line to the file, appending a newline character at the end.
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
    public final function writeline(string $content, ?int $length = null): int {

        return fwrite($this->handle, explode("\n", $content)[0] . "\n", $length);
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
