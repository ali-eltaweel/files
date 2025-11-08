<?php

namespace Files\Handles;

use Files\{ Path, Stat };

use Lang\{ Annotations\Computes, ComputedProperties };

use Logger\{ EmitsLogs, IHasLogger, Logger };

/**
 * File Handle.
 * 
 * @api
 * @abstract
 * @since 1.0.0
 * @version 1.1.0
 * @package files
 * @author Ali M. Kamel <ali.kamel.dev@gmail.com>
 * 
 * @property-read Stat $stat
 */
abstract class Handle implements IHasLogger {

    use ComputedProperties { __get as getComputedProperty; }
    use EmitsLogs;

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
     * The logger instance.
     * 
     * @internal
     * @since 1.1.0
     * 
     * @var Logger|null $logger
     */
    protected ?Logger $logger = null;

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
     * Sets the logger instance.
     * 
     * @api
     * @since 1.1.0
     * @version 1.0.0
     * 
     * @param Logger|null $logger
     * @return void
     */
    public function setLogger(?Logger $logger): void {

        $this->logger = $logger;
    }

    /**
     * Gets the stat information of the handle.
     * 
     * @api
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return Stat|null Returns a Stat object containing detailed information about the handle, or null if the handle is not valid.
     */
    #[Computes('stat')]
    public function getStat(): ?Stat {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Getting stats' => [ 'path' => $this->path->path ]
        ], $logUnit);
        
        $stats = new Stat($this->handle, isResource: true);

        $this->debugLog(fn () => [
            'Getting stats' => [ 'path' => $this->path->path, 'stats' => $stats->toArray() ]
        ], $logUnit);

        return $stats;
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
