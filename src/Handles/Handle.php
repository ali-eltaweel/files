<?php

namespace Files\Handles;

use Files\{ Path, Stat };

use Lang\{ Annotations\Computes, ComputedProperties };

/**
 * File Handle.
 * 
 * @api
 * @abstract
 * @since 1.0.0
 * @version 1.0.0
 * @package files
 * @author Ali M. Kamel <ali.kamel.dev@gmail.com>
 * 
 * @property-read Stat $stat
 */
abstract class Handle {

    use ComputedProperties { __get as getComputedProperty; }

    /**
     * The file handle resource.
     * 
     * @internal
     * @since 1.0.0
     * 
     * @var resource $handle
     */
    protected $handle;

    /**
     * Creates a new file handle.
     * 
     * @api
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param Path $path
     * @param array $options
     */
    public function __construct(public readonly Path $path, public readonly array $options = []) {

        $this->handle = $this->openHandle();
    }

    /**
     * Retrieves the value of the specified property.
     * 
     * @api
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed {

        if ($this->hasComputedProperty($name, $this)) {

            return $this->getComputedProperty($name);
        }

        user_error(sprintf('Undefined property: %s::%s', static::class, $name));

        return null;
    }

    /**
     * Gets the stat information of the handle.
     * 
     * @api
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return Stat|null Returns a Stat object containing detailed information about the handle, or null if the handle is not valid.
     */
    #[Computes('stat')]
    public function getStat(): ?Stat {
        
        return new Stat($this->handle, isResource: true);
    }

    /**
     * Closes the handle.
     * 
     * @api
     * @abstract
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return void
     */
    public abstract function close(): bool;
    
    /**
     * Reads data from the handle.
     * 
     * @api
     * @abstract
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return ?string
     */
    public abstract function read(): ?string;

    /**
     * Open the handle.
     * 
     * @abstract
     * @internal
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return resource
     */
    protected abstract function openHandle(): mixed;
}
