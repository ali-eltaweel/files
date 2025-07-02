<?php

namespace Files;

use Lang\{ Annotations\Computes, Annotations\Sets, ComputedProperties, VirtualProperties };

/**
 * Abstract File.
 * 
 * @api
 * @abstract
 * @since 1.0.0
 * @version 1.1.0
 * @package files
 * @author Ali M. Kamel <ali.kamel.dev@gmail.com>
 * 
 * @property           int|null $atime
 * @property           int|null $mtime
 * @property-read      int|null $ctime
 * 
 * @property           int|null $gid
 * @property           int|null $uid
 * 
 * @property-read      int|null $inode
 * @property-read      int|null $permissions
 * @property           int|null $mode
 * @property-read      int|null $size
 * @property-read FileType|null $type
 * @property-read     Stat|null $stat
 */
abstract class File {

    use ComputedProperties;
    use VirtualProperties;

    /**
     * The file type corresponding to this file class.
     * 
     * @internal
     * @since 1.0.0
     * 
     * @var FileType FILE_TYPE
     */
    protected const FILE_TYPE = FileType::RegularFile;

    /**
     * The callbacks used to create different types of files.
     * 
     * @static
     * @internal
     * @since 1.0.0
     * 
     * @var array<string, callable(Path|string, FileType): File>
     */
    private static array $FACTORIES = [];

    /**
     * The path of the file.
     * 
     * @api
     * @readonly
     * @since 1.0.0
     * 
     * @var Path $path
     */
    public readonly Path $path;

    /**
     * Creates a new file instance.
     * 
     * @api
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param Path|string $path
     */
    public function __construct(Path|string $path) {

        $this->path = is_string($path) ? new Path($path) : $path;
    }

    /**
     * Sets the group of the file.
     * 
     * @api
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param string|int $group The group name or ID.
     * 
     * @return bool Returns true on success, false on failure.
     */
    #[Sets('gid')]
    public function chgrp(string|int $group): bool {

        return chgrp($this->path, $group);
    }

    /**
     * Gets the group of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return int|null Returns the group ID, or null if the operation fails.
     */
    #[Computes('gid')]
    public final function getGroup(): ?int {
        
        return ($group = filegroup($this->path)) === false ? null : $group;
    }

    /**
     * Gets the permissions of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return int|null Returns the permissions as an integer, or null if the operation fails.
     */
    #[Computes('permissions')]
    public final function getPermissions(): ?int {
        
        return ($perms = fileperms($this->path)) === false ? null : $perms;
    }

    /**
     * Sets the mode of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param int $mode The mode to set, in octal format.
     * 
     * @return bool Returns true on success, false on failure.
     */
    #[Sets('mode')]
    public final function chmod(int $mode): bool {

        return chmod($this->path, $mode);
    }

    /**
     * Gets the mode of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return int|null Returns the mode as an integer, or null if the operation fails.
     */
    #[Computes('mode')]
    public final function getMode(): ?int {
        
        return octdec(substr(decoct($this->getPermissions()), -4));
    }

    /**
     * Sets the owner of the file.
     * 
     * @api
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param string|int $user The user name or ID.
     * 
     * @return bool Returns true on success, false on failure.
     */
    #[Sets('uid')]
    public function chown(string|int $user): bool {

        return chown($this->path, $user);
    }

    /**
     * Gets the owner of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return int|null Returns the user ID of the owner, or null if the operation fails.
     */
    #[Computes('uid')]
    public final function getOwner(): ?int {
        
        return ($user = fileowner($this->path)) === false ? null : $user;
    }

    /**
     * Sets the access time of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param int|null $atime The access time as a Unix timestamp, or null to use the current time.
     * 
     * @return bool Returns true on success, false on failure.
     */
    #[Sets('atime')]
    public final function setAccessTime(?int $atime): bool {

        return $this->touch($atime, $atime);
    }

    /**
     * Gets the access time of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return int|null Returns the access time as a Unix timestamp, or null if the operation fails.
     */
    #[Computes('atime')]
    public final function getAccessTime(): ?int {
        
        return ($atime = fileatime($this->path)) === false ? null : $atime;
    }

    /**
     * Gets the change time of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return int|null Returns the change time as a Unix timestamp, or null if the operation fails.
     */
    #[Computes('ctime')]
    public final function getChangeTime(): ?int {
        
        return ($ctime = filectime($this->path)) === false ? null : $ctime;
    }

    /**
     * Sets the modification time of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param int|null $mtime The modification time as a Unix timestamp, or null to use the current time.
     * 
     * @return bool Returns true on success, false on failure.
     */
    #[Sets('mtime')]
    public final function setModificationTime(?int $mtime): bool {

        return $this->touch($mtime);
    }

    /**
     * Gets the modification time of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return int|null Returns the modification time as a Unix timestamp, or null if the operation fails.
     */
    #[Computes('mtime')]
    public final function getModificationTime(): ?int {
        
        return ($mtime = filemtime($this->path)) === false ? null : $mtime;
    }

    /**
     * Gets the inode number of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return int|null Returns the inode number, or null if the operation fails.
     */
    #[Computes('inode')]
    public final function getINode(): ?int {
        
        return ($inode = fileinode($this->path)) === false ? null : $inode;
    }

    /**
     * Gets the size of the file in bytes.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return int|null Returns the size in bytes, or null if the operation fails.
     */
    #[Computes('size')]
    public final function getSize(): ?int {
        
        return ($size = filesize($this->path)) === false ? null : $size;
    }

    /**
     * Gets the type of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return FileType|null Returns the type of the file, or null if the file does not exist or the type cannot be determined.
     */
    #[Computes('type')]
    public final function getType(): ?FileType {
        
        return FileType::of($this->path);
    }

    /**
     * Gets the stat information of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return Stat|null Returns a Stat object containing detailed information about the file, or null if the file does not exist.
     */
    #[Computes('stat')]
    public final function getStat(): ?Stat {
        
        return $this->path->exists() ? new Stat($this->path, $this->getType() == FileType::Link) : null;
    }

    /**
     * Copies the file to a new location.
     * 
     * @api
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param Path|string $target The target path where the file should be copied.
     * 
     * @return bool Returns true on success, false on failure.
     */
    public function copy(Path|string $target): bool {

        return copy($this->path, $target);
    }

    /**
     * Creates a hard link to the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param Path|string $target The target path for the hard link.
     * 
     * @return bool Returns true on success, false on failure.
     */
    public final function link(Path|string $target): bool {

        return link($this->path, $target);
    }

    /**
     * Creates a symbolic link to the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param Path|string $target The target path for the symbolic link.
     * 
     * @return bool Returns true on success, false on failure.
     */
    public final function symlink(Path|string $target): bool {

        return symlink($this->path, $target);
    }

    /**
     * Renames the file to a new name or moves it to a new location.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param Path|string $target The new name or path for the file.
     * 
     * @return bool Returns true on success, false on failure.
     */
    public final function rename(Path|string $target): bool {

        return rename($this->path, $target);
    }

    /**
     * Changes the last access and modification times of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param int|null $mtime The new modification time as a Unix timestamp, or null to use the current time.
     * @param int|null $atime The new access time as a Unix timestamp, or null to use the current time.
     * 
     * @return bool Returns true on success, false on failure.
     */
    public final function touch(?int $mtime = null, ?int $atime = null): bool {

        return touch($this->path, $mtime, $atime);
    }

    /**
     * Removes the file from the filesystem.
     * 
     * @api
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return bool Returns true on success, false on failure.
     */
    public function remove(): bool {

        return unlink($this->path);
    }

    /**
     * Opens the file and returns a handle to it.
     * 
     * @api
     * @abstract
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return Handles\Handle Returns a handle to the opened file.
     */
    public abstract function open(): Handles\Handle;

    /**
     * Creates a new file instance using the factory method.
     * 
     * @api
     * @final
     * @static
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param Path|string $path
     * @return static
     */
    public static final function make(Path|string $path): static {

        return static::getFactory()($path, static::FILE_TYPE);
    }

    /**
     * Sets the factory method for this file class.
     * 
     * @api
     * @final
     * @static
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param callable(Path|string, FileType): File $factory
     * @return void
     */
    public static final function setFactory(callable $factory): void {

        self::$FACTORIES[ static::class ] = $factory;
    }

    /**
     * Retrieves the factory method for this file class.
     * 
     * @api
     * @final
     * @static
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return callable(Path|string, FileType): File
     */
    public static final function getFactory(): callable {

        return self::$FACTORIES[ static::class ] ??= static::getDefaultFactory();
    }

    /**
     * Provides the default factory method for creating file instances.
     * 
     * @static
     * @internal
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return callable(Path|string ,FileType ):File
     */
    protected static function getDefaultFactory(): callable {

        if (static::class == self::class) {

            return function(Path|string $path, FileType $suggestedFileType): File {
            
                $suggestedFileType = FileType::of($path) ?? $suggestedFileType;
    
                $class = $suggestedFileType->getClass();
    
                return $class::make($path, $suggestedFileType);
            };
        }

        return function(Path|string $path, FileType $suggestedFileType): File {

            return new static($path);
        };
    }
}
