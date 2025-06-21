<?php

namespace Files;

use Lang\Annotations\{ Computes, Sets };

/**
 * Regular File.
 * 
 * @api
 * @since 1.0.0
 * @version 1.0.0
 * @package files
 * @author Ali M. Kamel <ali.kamel.dev@gmail.com>
 * 
 * @property string $content
 */
class RegularFile extends File {

    /**
     * Opens the file and returns a handle to it.
     * 
     * @api
     * @final
     * @abstract
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return Handles\Handle Returns a handle to the opened file.
     */
    public final function open(string $mode = 'r'): Handles\RegularFileHandle {

        return new Handles\RegularFileHandle($this->path, $mode);
    }

    /**
     * Opens the file, lock it, performs the given work, and then unlocks the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param callable $work The work to be done within the transaction.
     * @param string $mode The mode in which to open the file (default is 'r').
     * @param Lock $lock The type of lock to use (default is Lock::Exclusive).
     * 
     * @return mixed Returns the result of the work done within the transaction.
     */
    public final function transaction(callable $work, string $mode = 'r', Lock $lock = Lock::Exclusive): mixed {

        $handle = $this->open($mode);

        $handle->lock($lock);

        $result = $work($handle);

        $handle->unlock(close: true);

        return $result;
    }

    /**
     * Sets the content of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param string $content The content to be set in the file.
     * 
     * @return int Returns the number of bytes written to the file.
     */
    #[Sets('content')]
    public final function setContent(string $content): int {

        return $this->transaction(fn (Handles\RegularFileHandle $handle) => $handle->setContent($content), 'w');
    }

    /**
     * Gets the content of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return string|null Returns the content of the file or null if the file is empty.
     */
    #[Computes('content')]
    public final function getContent(): ?string {

        return $this->transaction(fn (Handles\RegularFileHandle $handle) => $handle->getContent(), 'r');
    }
}
