<?php

namespace Files;

use Lang\{ Annotations\Computes, ComputedProperties };

use Stringable;

/**
 * File Path.
 * 
 * @api
 * @final
 * @since 1.0.0
 * @version 1.0.0
 * @package files
 * @author Ali M. Kamel <ali.kamel.dev@gmail.com>
 * 
 * @property-read string    $basename
 * @property-read string    $dirname
 * @property-read string    $extension
 * @property-read string    $filename
 * @property-read self      $parentDir
 * @property-read self|null $realpath
 */
final class Path implements Stringable {

    use ComputedProperties;
    
    /**
     * Creates a new Path instance.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param string $path
     * @param string $separator
     */
    public final function __construct(public readonly string $path, public readonly string $separator = DIRECTORY_SEPARATOR) {}

    /**
     * Returns the string representation of the path.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return string
     */
    public final function __toString(): string {
        
        return $this->path;
    }

    /**
     * Returns the basename of the path.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return string
     */
    #[Computes('basename')]
    public final function getBasename(): string {
        
        return pathinfo($this->path, PATHINFO_BASENAME);
    }

    /**
     * Returns the directory name of the path.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return string
     */
    #[Computes('dirname')]
    public final function getDirname(): string {
        
        return pathinfo($this->path, PATHINFO_DIRNAME);
    }

    /**
     * Returns the file extension of the path.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return string
     */
    #[Computes('extension')]
    public final function getExtension(): string {
        
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }

    /**
     * Returns the filename without extension of the path.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return string
     */
    #[Computes('filename')]
    public final function getFilename(): string {
        
        return pathinfo($this->path, PATHINFO_FILENAME);
    }

    /**
     * Returns the path to the parent directory.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return self
     */
    #[Computes('parentDir')]
    public final function getParentDir(): self {
        
        return new self($this->getDirname(), $this->separator);
    }

    /**
     * Returns the real path of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return self|null
     */
    #[Computes('realpath')]
    public final function getRealpath(): ?self {
        
        $realpath = realpath($this->path);

        return $realpath === false ? null : new self($realpath);
    }

    /**
     * Checks if the path is absolute.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return bool
     */
    public final function isAbsolute(): bool {
        
        return $this->path[0] == $this->separator;
    }

    /**
     * Checks if the path is relative.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return bool
     */
    public final function isRelative(): bool {

        return !$this->isAbsolute();
    }

    /**
     * Checks if the path exists.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return bool
     */
    public final function exists(): bool {
        
        return file_exists($this->path);
    }

    /**
     * Appends a path to the current path.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param string $path
     * @return Path
     */
    public final function append(string $path): self {
        
        return new self(
            rtrim($this->path, $this->separator) .
                  $this->separator .
                  ltrim($path, $this->separator),
            $this->separator
        );
    }
}
