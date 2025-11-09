<?php

namespace Files;

use Codecs\ICodec;
use Files\Handles\RegularFileHandle;
use Generator;
use Lang\Annotations\{ Computes, Sets };

/**
 * Line Encoded File.
 * 
 * @api
 * @since 1.3.0
 * @version 1.0.0
 * @package files
 * @author Ali M. Kamel <ali.kamel.dev@gmail.com>
 * 
 * @template T
 * @property T $data
 */
class LineEncodedFile extends RegularFile {

    /**
     * Creates a new line-encoded file.
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
     * @return array
     */
    #[Computes('data')]
    public final function getData(): array {

        $logUnit = static::class . '::' . __FUNCTION__;

         $this->infoLog(fn () => [ 'Reading file data' => [ 'path' => $this->path->path ] ], $logUnit);

        $this->codec->setLogger($this->logger);

        $data = $this->transaction(function(RegularFileHandle $handle): array {

            $data = [];

            while (!is_null($line = $handle->readline())) {

                $line = rtrim($line, PHP_EOL);

                $data[] = $this->codec->decode($line);
            }
            
            return $data;
        }, 'r');

        $this->debugLog(fn () => [ 'Reading file data' => [
            'path'  => $this->path->path,
            'count' => count($data)
        ] ], $logUnit);

        return $data;
    }

    /**
     * Encodes and sets the data to the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param array|Generator $data
     * @return int The number of bytes written to the file.
     */
    #[Sets('data')]
    public final function setData(array|Generator $data): int {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [ 'Writing file data' => [
            'path'  => $this->path->path,
            'count' => is_array($data) ? count($data) : null
        ] ], $logUnit);

        $this->codec->setLogger($this->logger);

        $count = 0;

        $written = $this->transaction(function(RegularFileHandle $handle) use ($data, &$count): int {

            $written = 0;

            foreach ($data as $record) {
    
                $line = $this->codec->encode($record);
                $written += $handle->write($line . PHP_EOL);

                $count++;
            }

            return $written;
        }, 'w');

        $this->debugLog(fn () => [
            'Writing file data' => [
                'path'        => $this->path->path,
                'count'       => is_array($data) ? count($data) : null,
                'actualCount' => $count,
                'written'     => $written
            ]
        ], $logUnit);

        return $written;
    }

    /**
     * Appends the specified record.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param mixed $data
     * @return int
     */
    public final function append(mixed $data): int {
        
        $logUnit = static::class . '::' . __FUNCTION__;
        
        $this->infoLog(fn () => [ 'Appending record' => [
            'path'  => $this->path->path,
        ] ], $logUnit);
            
        $this->codec->setLogger($this->logger);
        
        $written = $this->transaction(
            fn(RegularFileHandle $handle) => $handle->writeline(
                $this->codec->encode($data)
            ),
            'a'
        );

        $this->debugLog(fn () => [ 'Record appended' => [
            'path'    => $this->path->path,
            'written' => $written
        ] ], $logUnit);

        return $written;
    }

    /**
     * Iterates over each record in the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param callable(T):bool $callback
     * @return int The last position after iteration.
     */
    public final function foreachRecord(callable $callback, int $startPosition = 0): int {

        $this->codec->setLogger($this->logger);
        
        return $this->transaction(function(RegularFileHandle $handle) use ($callback, $startPosition): int {

            if ($startPosition > 0) {
                
                $handle->position = $startPosition;
            }

            while (!is_null($line = $handle->readline())) {

                $line = rtrim($line, PHP_EOL);

                if ($callback($this->codec->decode($line), $handle) === false) {
                    
                    return $handle->position;
                }
            }

            return $handle->position;
        }, 'r');
    }
}
