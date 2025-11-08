<?php

namespace Files;

use Lang\{ Annotations\Computes, Annotations\Sets, ComputedProperties, VirtualProperties };

use Logger\{ EmitsLogs, IHasLogger, Logger };

/**
 * Abstract File.
 * 
 * @api
 * @abstract
 * @since 1.0.0
 * @version 1.2.0
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
abstract class File implements IHasLogger {

    use ComputedProperties;
    use VirtualProperties;
    use EmitsLogs;

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
     * The logger instance.
     * 
     * @internal
     * @since 1.2.0
     * 
     * @var Logger|null $logger
     */
    protected ?Logger $logger = null;

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
     * Sets the logger instance.
     * 
     * @api
     * @since 1.2.0
     * @version 1.0.0
     * 
     * @param Logger|null $logger
     * @return void
     */
    public function setLogger(?Logger $logger): void {

        $this->logger = $logger;
    }

    /**
     * Sets the group of the file.
     * 
     * @api
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param string|int $group The group name or ID.
     * 
     * @return bool Returns true on success, false on failure.
     */
    #[Sets('gid')]
    public function chgrp(string|int $group): bool {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Changing group' => [ 'path' => $this->path->path, 'group' => $group ]
        ], $logUnit);

        $changed = chgrp($this->path, $group);

        $this->debugLog(fn () => [
            'Changing group' => [ 'path' => $this->path->path, 'group' => $group, 'success' => $changed ]
        ], $logUnit);

        return $changed;
    }

    /**
     * Gets the group of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return int|null Returns the group ID, or null if the operation fails.
     */
    #[Computes('gid')]
    public final function getGroup(): ?int {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Getting group' => [ 'path' => $this->path->path ]
        ], $logUnit);
        
        $group = ($group = filegroup($this->path)) === false ? null : $group;

        $this->debugLog(fn () => [
            'Getting group' => [ 'path' => $this->path->path, 'group' => $group ]
        ], $logUnit);

        return $group;
    }

    /**
     * Gets the permissions of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return int|null Returns the permissions as an integer, or null if the operation fails.
     */
    #[Computes('permissions')]
    public final function getPermissions(): ?int {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Getting permissions' => [ 'path' => $this->path->path ]
        ], $logUnit);
        
        $perms = ($perms = fileperms($this->path)) === false ? null : $perms;

        $this->debugLog(fn () => [
            'Getting permissions' => [ 'path' => $this->path->path, 'permissions' => $perms ]
        ], $logUnit);

        return $perms;
    }

    /**
     * Sets the mode of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param int $mode The mode to set, in octal format.
     * 
     * @return bool Returns true on success, false on failure.
     */
    #[Sets('mode')]
    public final function chmod(int $mode): bool {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Changing mode' => [ 'path' => $this->path->path, 'mode' => $mode ]
        ], $logUnit);

        $changed = chmod($this->path, $mode);

        $this->debugLog(fn () => [
            'Changing mode' => [ 'path' => $this->path->path, 'mode' => $mode, 'success' => $changed ]
        ], $logUnit);

        return $changed;
    }

    /**
     * Gets the mode of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return int|null Returns the mode as an integer, or null if the operation fails.
     */
    #[Computes('mode')]
    public final function getMode(): ?int {
        
        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Getting mode' => [ 'path' => $this->path->path ]
        ], $logUnit);

        if (is_null($perms = $this->getPermissions())) {

            return null;
        }

        $mode = octdec(substr(decoct($perms), -4));

        $this->debugLog(fn () => [
            'Getting mode' => [ 'path' => $this->path->path, 'mode' => $mode ]
        ], $logUnit);

        return $mode;
    }

    /**
     * Sets the owner of the file.
     * 
     * @api
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param string|int $user The user name or ID.
     * 
     * @return bool Returns true on success, false on failure.
     */
    #[Sets('uid')]
    public function chown(string|int $user): bool {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Changing owner' => [ 'path' => $this->path->path, 'user' => $user ]
        ], $logUnit);

        $changed = chown($this->path, $user);

        $this->debugLog(fn () => [
            'Changing owner' => [ 'path' => $this->path->path, 'user' => $user, 'success' => $changed ]
        ], $logUnit);

        return $changed;
    }

    /**
     * Gets the owner of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return int|null Returns the user ID of the owner, or null if the operation fails.
     */
    #[Computes('uid')]
    public final function getOwner(): ?int {
        
        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Getting owner' => [ 'path' => $this->path->path ]
        ], $logUnit);

        $user = ($user = fileowner($this->path)) === false ? null : $user;

        $this->debugLog(fn () => [
            'Getting owner' => [ 'path' => $this->path->path, 'user' => $user ]
        ], $logUnit);

        return $user;
    }

    /**
     * Sets the access time of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param int|null $atime The access time as a Unix timestamp, or null to use the current time.
     * 
     * @return bool Returns true on success, false on failure.
     */
    #[Sets('atime')]
    public final function setAccessTime(?int $atime): bool {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Setting access time' => [ 'path' => $this->path->path, 'atime' => $atime ]
        ], $logUnit);

        $changed = $this->touch($atime, $atime);

        $this->debugLog(fn () => [
            'Setting access time' => [ 'path' => $this->path->path, 'atime' => $atime, 'success' => $changed ]
        ], $logUnit);

        return $changed;
    }

    /**
     * Gets the access time of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return int|null Returns the access time as a Unix timestamp, or null if the operation fails.
     */
    #[Computes('atime')]
    public final function getAccessTime(): ?int {
        
        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Getting access time' => [ 'path' => $this->path->path ]
        ], $logUnit);

        $atime = ($atime = fileatime($this->path)) === false ? null : $atime;

        $this->debugLog(fn () => [
            'Getting access time' => [ 'path' => $this->path->path, 'atime' => $atime ]
        ], $logUnit);

        return $atime;
    }

    /**
     * Gets the change time of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return int|null Returns the change time as a Unix timestamp, or null if the operation fails.
     */
    #[Computes('ctime')]
    public final function getChangeTime(): ?int {
        
        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Getting inode change time' => [ 'path' => $this->path->path ]
        ], $logUnit);

        $ctime = ($ctime = filectime($this->path)) === false ? null : $ctime;

        $this->debugLog(fn () => [
            'Getting inode change time' => [ 'path' => $this->path->path, 'ctime' => $ctime ]
        ], $logUnit);

        return $ctime;
    }

    /**
     * Sets the modification time of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param int|null $mtime The modification time as a Unix timestamp, or null to use the current time.
     * 
     * @return bool Returns true on success, false on failure.
     */
    #[Sets('mtime')]
    public final function setModificationTime(?int $mtime): bool {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Setting modification time' => [ 'path' => $this->path->path, 'mtime' => $mtime ]
        ], $logUnit);

        $changed = $this->touch($mtime);

        $this->debugLog(fn () => [
            'Setting modification time' => [ 'path' => $this->path->path, 'mtime' => $mtime, 'success' => $changed ]
        ], $logUnit);

        return $changed;
    }

    /**
     * Gets the modification time of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return int|null Returns the modification time as a Unix timestamp, or null if the operation fails.
     */
    #[Computes('mtime')]
    public final function getModificationTime(): ?int {
        
        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Getting modification time' => [ 'path' => $this->path->path ]
        ], $logUnit);

        $mtime = ($mtime = filemtime($this->path)) === false ? null : $mtime;

        $this->debugLog(fn () => [
            'Getting modification time' => [ 'path' => $this->path->path, 'mtime' => $mtime ]
        ], $logUnit);

        return $mtime;
    }

    /**
     * Gets the inode number of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return int|null Returns the inode number, or null if the operation fails.
     */
    #[Computes('inode')]
    public final function getINode(): ?int {
        
        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Getting inode' => [ 'path' => $this->path->path ]
        ], $logUnit);

        $inode = ($inode = fileinode($this->path)) === false ? null : $inode;

        $this->debugLog(fn () => [
            'Getting inode' => [ 'path' => $this->path->path, 'inode' => $inode ]
        ], $logUnit);

        return $inode;
    }

    /**
     * Gets the size of the file in bytes.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return int|null Returns the size in bytes, or null if the operation fails.
     */
    #[Computes('size')]
    public final function getSize(): ?int {
        
        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Getting size' => [ 'path' => $this->path->path ]
        ], $logUnit);

        $size = ($size = filesize($this->path)) === false ? null : $size;

        $this->debugLog(fn () => [
            'Getting size' => [ 'path' => $this->path->path, 'size' => $size ]
        ], $logUnit);

        return $size;
    }

    /**
     * Gets the type of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return FileType|null Returns the type of the file, or null if the file does not exist or the type cannot be determined.
     */
    #[Computes('type')]
    public final function getType(): ?FileType {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Getting type' => [ 'path' => $this->path->path ]
        ], $logUnit);
        
        $type = FileType::of($this->path);

        $this->debugLog(fn () => [
            'Getting type' => [ 'path' => $this->path->path, 'type' => $type?->name ]
        ], $logUnit);

        return $type;
    }

    /**
     * Gets the stat information of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return Stat|null Returns a Stat object containing detailed information about the file, or null if the file does not exist.
     */
    #[Computes('stat')]
    public final function getStat(): ?Stat {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Getting stats' => [ 'path' => $this->path->path ]
        ], $logUnit);
        
        $stats = $this->path->exists() ? new Stat($this->path, $this->getType() == FileType::Link) : null;

        $this->debugLog(fn () => [
            'Getting stats' => [ 'path' => $this->path->path, 'stats' => $stats?->toArray() ]
        ], $logUnit);

        return $stats;
    }

    /**
     * Copies the file to a new location.
     * 
     * @api
     * @since 1.0.0
     * @version 1.2.0
     * 
     * @param Path|string $target The target path where the file should be copied.
     * 
     * @return bool Returns true on success, false on failure.
     */
    public function copy(Path|string $target): bool {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Copying file' => [ 'path' => $this->path->path, 'target' => "$target" ]
        ], $logUnit);

        $copied = copy($this->path, $target);

        $this->debugLog(fn () => [
            'Copying file' => [ 'path' => $this->path->path, 'target' => "$target", 'copied' => $copied ]
        ], $logUnit);

        return $copied;
    }

    /**
     * Creates a hard link to the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param Path|string $target The target path for the hard link.
     * 
     * @return bool Returns true on success, false on failure.
     */
    public final function link(Path|string $target): bool {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Creating hard link' => [ 'path' => $this->path->path, 'target' => "$target" ]
        ], $logUnit);

        $created = link($this->path, $target);

        $this->debugLog(fn () => [
            'Creating hard link' => [ 'path' => $this->path->path, 'target' => "$target", 'created' => $created ]
        ], $logUnit);

        return $created;
    }

    /**
     * Creates a symbolic link to the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param Path|string $target The target path for the symbolic link.
     * 
     * @return bool Returns true on success, false on failure.
     */
    public final function symlink(Path|string $target): bool {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Creating symbolic link' => [ 'path' => $this->path->path, 'target' => "$target" ]
        ], $logUnit);

        $created = symlink($this->path, $target);

        $this->debugLog(fn () => [
            'Creating symbolic link' => [ 'path' => $this->path->path, 'target' => "$target", 'created' => $created ]
        ], $logUnit);

        return $created;
    }

    /**
     * Renames the file to a new name or moves it to a new location.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param Path|string $target The new name or path for the file.
     * 
     * @return bool Returns true on success, false on failure.
     */
    public final function rename(Path|string $target): bool {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Renaming file' => [ 'path' => $this->path->path, 'target' => "$target" ]
        ], $logUnit);

        $renamed = rename($this->path, $target);

        $this->debugLog(fn () => [
            'Renaming file' => [ 'path' => $this->path->path, 'target' => "$target", 'renamed' => $renamed ]
        ], $logUnit);

        return $renamed;
    }

    /**
     * Changes the last access and modification times of the file.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param int|null $mtime The new modification time as a Unix timestamp, or null to use the current time.
     * @param int|null $atime The new access time as a Unix timestamp, or null to use the current time.
     * 
     * @return bool Returns true on success, false on failure.
     */
    public final function touch(?int $mtime = null, ?int $atime = null): bool {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Touching file' => [ 'path' => $this->path->path, 'mtime' => $mtime, 'atime' => $atime ]
        ], $logUnit);

        $touched = touch($this->path, $mtime, $atime);

        $this->debugLog(fn () => [
            'Touching file' => [ 'path' => $this->path->path, 'mtime' => $mtime, 'atime' => $atime, 'touched' => $touched ]
        ], $logUnit);

        return $touched;
    }

    /**
     * Removes the file from the filesystem.
     * 
     * @api
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return bool Returns true on success, false on failure.
     */
    public function remove(): bool {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Removing file' => [ 'path' => $this->path->path ]
        ], $logUnit);

        $removed = unlink($this->path);

        $this->debugLog(fn () => [
            'Removing file' => [ 'path' => $this->path->path, 'removed' => $removed ]
        ], $logUnit);

        return $removed;
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
