<?php

namespace Files;

use Codecs\ICodec;

use Lang\Annotations\{ Computes, Sets };

/**
 * Encoded File.
 * 
 * @api
 * @since 1.0.0
 * @version 1.1.0
 * @package files
 * @author Ali M. Kamel <ali.kamel.dev@gmail.com>
 * 
 * @template T
 * @property T $data
 */
class EncodedFile extends RegularFile {

    /**
     * Creates a new encoded file.
     * 
     * @api
     * @override
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param Path|string $path
     * @param ICodec $codec
     */
    public function __construct(Path|string $path, public ICodec $codec) {

        parent::__construct($path);
    }

    /**
     * Retrieves and decodes the data from the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return mixed
     */
    #[Computes('data')]
    public final function getData(): mixed {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [ 'Reading file data' => [ 'path' => $this->path->path ] ], $logUnit);
        
        $this->codec->setLogger($this->logger);
        $data = is_null($content = $this->getContent()) ? null : $this->codec->decode($content);

        $this->debugLog(fn () => [ 'Reading file data' => [
            'path' => $this->path->path,
            'type' => gettype($data) === 'object' ? get_class($data) : gettype($data)
        ] ], $logUnit);

        return $data;
    }

    /**
     * Encodes and sets the data to the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param mixed $data
     * @return int The number of bytes written to the file.
     */
    #[Sets('data')]
    public final function setData(mixed $data): int {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [ 'Writing file data' => [
            'path' => $this->path->path,
            'type' => gettype($data) === 'object' ? get_class($data) : gettype($data)
        ] ], $logUnit);

        $this->codec->setLogger($this->logger);
        $written = $this->setContent($this->codec->encode($data));

        $this->debugLog(fn () => [
            'Writing file data' => [
                'path'    => $this->path->path,
                'type'    => gettype($data) === 'object' ? get_class($data) : gettype($data),
                'written' => $written
            ]
        ], $logUnit);

        return $written;
    }
}
