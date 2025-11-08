<?php

namespace Files;

use Lang\{ Annotations\Computes, ComputedProperties };

/**
 * File Statistics.
 * 
 * @api
 * @final
 * @since 1.0.0
 * @version 1.1.0
 * @package files
 * @author Ali M. Kamel <ali.kamel.dev@gmail.com>
 * 
 * @property-read int $deviceNumber
 * @property-read int $inodeNumber
 * @property-read int $mode
 * @property-read int $numberOfLinks
 * @property-read int $uid
 * @property-read int $gid
 * @property-read int $deviceType
 * @property-read int $size
 * @property-read int $atime
 * @property-read int $ctime
 * @property-read int $mtime
 * @property-read int $blockSize
 * @property-read int $numberOfBlocks
 */
final class Stat {

    use ComputedProperties;

    /**
     * Creates a new Stat instance.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param resource|string $path
     * @param bool $isLink
     * @param bool $isResource
     */
    public final function __construct(public readonly mixed $path, public readonly bool $isLink = false, public readonly bool $isResource = false) {}

    public final function __set(string $name, mixed $value): void {

        user_error(sprintf('Undefined property: %s::%s', static::class, $name));
    }

    /**
     * Retrieves the device number of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return int
     */
    #[Computes('deviceNumber')]
    public final function getDeviceNumber(): int {

        return $this->getStat()['dev'];
    }

    /**
     * Retrieves the inode number of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return int
     */
    #[Computes('inodeNumber')]
    public final function getInodeNumber(): int {

        return $this->getStat()['ino'];
    }

    /**
     * Retrieves the protection mode of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return int
     */
    #[Computes('mode')]
    public final function getMode(): int {

        return $this->getStat()['mode'];
    }

    /**
     * Retrieves the number of hard links to the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return int
     */
    #[Computes('numberOfLinks')]
    public final function getNumberOfLinks(): int {

        return $this->getStat()['nlink'];
    }

    /**
     * Retrieves the user ID of the file owner.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return int
     */
    #[Computes('uid')]
    public final function getUID(): int {

        return $this->getStat()['uid'];
    }

    /**
     * Retrieves the group ID of the file owner.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return int
     */
    #[Computes('gid')]
    public final function getGID(): int {

        return $this->getStat()['gid'];
    }

    /**
     * Retrieves the device type of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return int
     */
    #[Computes('deviceType')]
    public final function getDeviceType(): int {

        return $this->getStat()['rdev'];
    }

    /**
     * Retrieves the access time of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return int|null
     */
    #[Computes('atime')]
    public final function getAccessTime(): ?int {
        
        return $this->getStat()['atime'];
    }

    /**
     * Retrieves the change time of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return int|null
     */
    #[Computes('ctime')]
    public final function getChangeTime(): ?int {
        
        return $this->getStat()['ctime'];
    }

    /**
     * Retrieves the modification time of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return int|null
     */
    #[Computes('mtime')]
    public final function getModificationTime(): ?int {
        
        return $this->getStat()['mtime'];
    }

    /**
     * Retrieves the size of the file in bytes.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return int
     */
    #[Computes('size')]
    public final function getSize(): int {

        return $this->getStat()['size'];
    }

    /**
     * Retrieves the block size of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return int
     */
    #[Computes('blockSize')]
    public final function getBlockSize(): int {

        return $this->getStat()['blksize'];
    }

    /**
     * Retrieves the number of blocks allocated for the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return int
     */
    #[Computes('numberOfBlocks')]
    public final function getNumberOfBlocks(): int {

        return $this->getStat()['blocks'];
    }

    /**
     * Converts the Stat instance to an associative array.
     * 
     * @api
     * @final
     * @since 1.1.0
     * @version 1.0.0
     * 
     * @return array<string, mixed>
     */
    public final function toArray(): array {

        $properties = [
            'deviceNumber',
            'inodeNumber',
            'mode',
            'numberOfLinks',
            'uid',
            'gid',
            'deviceType',
            'atime',
            'ctime',
            'mtime',
            'size',
            'blockSize',
            'numberOfBlocks'
        ];

        return array_combine($properties, array_map(fn (string $property): mixed => $this->$property, $properties));
    }

    /**
     * Retrieves the file statistics.
     * 
     * @internal
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return array<int|string, int>|bool
     */
    private function getStat(): array {
        
        return $this->isLink ? lstat($this->path) : (

            $this->isResource ? fstat($this->path) : stat($this->path)
        );
    }
}
