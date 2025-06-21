<?php

namespace Files;

use Codecs\ICodec;

use Lang\Annotations\{ Computes, Sets };

/**
 * Encoded File.
 * 
 * 
 * @api
 * @since 1.0.0
 * @version 1.0.0
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
     * @version 1.0.0
     * 
     * @return mixed
     */
    #[Computes('data')]
    public final function getData(): mixed {

        return $this->codec->decode($this->getContent());
    }

    /**
     * Encodes and sets the data to the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param mixed $data
     * @return int The number of bytes written to the file.
     */
    #[Sets('data')]
    public final function setData(mixed $data): int {

        return $this->setContent($this->codec->encode($data));
    }
}
