<?php

namespace Files;

use RuntimeException;

/**
 * File Type.
 * 
 * @api
 * @since 1.0.0
 * @version 1.1.0
 * @package files
 * @author Ali M. Kamel <ali.kamel.dev@gmail.com>
 */
enum FileType: string {

    case Fifo            = 'fifo';

    case CharacterDevice = 'char';

    case Directory       = 'dir';
    
    case BlockDevice     = 'block';
    
    case RegularFile     = 'file';
    
    case Link            = 'link';
    
    case Socket          = 'socket';

    /**
     * Returns the class name associated with the file type.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return string The class name.
     * @throws RuntimeException If the file type is unsupported.
     */
    public final function getClass(): string {
        
        return match($this) {
            
            self::Fifo              => Fifo::class,
            self::CharacterDevice   => CharacterDevice::class,
            self::Directory         => Directory::class,
            self::RegularFile       => RegularFile::class,
            self::Link              => Link::class,
            self::Socket            => Socket::class,
            default                 => throw new RuntimeException('Unsupported file type: ' . $this->value),
        };
    }

    /**
     * Returns the type of the specified file.
     * 
     * @api
     * @final
     * @sttaic
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param string $path
     * @return FileType|null
     */
    public static final function of(string $path): ?static {

        return file_exists($path) ? self::from(filetype($path)) : null;
    }
}
