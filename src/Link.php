<?php

namespace Files;

use Lang\Annotations\{ Computes, Sets };

/**
 * Link.
 * 
 * @api
 * @since 1.0.0
 * @version 1.1.0
 * @package files
 * @author Ali M. Kamel <ali.kamel.dev@gmail.com>
 * 
 * @property      int|null $targetGid
 * @property      int|null $targetUid
 * @property-read File|null $target
 * @property-read File|null $lastTarget
 */
class Link extends File {

    /**
     * Sets the group of the link.
     * 
     * @api
     * @final
     * @override
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param string|int $group The group name or ID.
     * 
     * @return bool Returns true on success, false on failure.
     */
    #[Sets('gid')]
    public final function chgrp(string|int $group): bool {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Changing link group' => [ 'path' => $this->path->path, 'group' => $group ]
        ], $logUnit);

        $changed = lchgrp($this->path, $group);

        $this->debugLog(fn () => [
            'Changing link group' => [ 'path' => $this->path->path, 'group' => $group, 'success' => $changed ]
        ], $logUnit);

        return $changed;
    }

    /**
     * Sets the group of the target of the link.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param string|int $group The group name or ID.
     * 
     * @return bool Returns true on success, false on failure.
     */
    #[Sets('targetGid')]
    public final function chgrpTarget(string|int $group): bool {

        return parent::chgrp($group);
    }

    /**
     * Sets the owner of the link.
     * 
     * @api
     * @final
     * @override
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @param string|int $user The user name or ID.
     * 
     * @return bool Returns true on success, false on failure.
     */
    #[Sets('uid')]
    public final function chown(string|int $user): bool {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Changing link owner' => [ 'path' => $this->path->path, 'user' => $user ]
        ], $logUnit);

        $changed = lchown($this->path, $user);

        $this->debugLog(fn () => [
            'Changing link owner' => [ 'path' => $this->path->path, 'user' => $user, 'success' => $changed ]
        ], $logUnit);

        return $changed;
    }

    /**
     * Sets the owner of the target of the link.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @param string|int $user The user name or ID.
     * 
     * @return bool Returns true on success, false on failure.
     */
    #[Sets('targetUid')]
    public final function chownTarget(string|int $user): bool {

        return parent::chown($user);
    }

    /**
     * Reads the target of the link.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return File|null Returns the target file if it exists, null otherwise.
     */
    #[Computes('target')]
    public final function readlink(): ?File {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Reading link' => [ 'path' => $this->path->path ]
        ], $logUnit);

        if (false === ($path = readlink($this->path))) {

            $this->debugLog(fn () => [
                'Reading link' => [ 'path' => $this->path->path, 'target' => null ]
            ], $logUnit);

            return null;
        }

        $target = File::make($path);

        $this->debugLog(fn () => [
            'Reading link' => [ 'path' => $this->path->path, 'target' => [ 'class' => $target::class, 'id' => spl_object_id($target) ] ]
        ], $logUnit);

        return $target;
    }

    /**
     * Reads the last target of the link recursively.
     * 
     * @api
     * @final
     * @since 1.0.0
     * @version 1.0.0
     * 
     * @return File|null Returns the last target file if it exists, null otherwise.
     */
    #[Computes('finalTarget')]
    public final function readlinkRecursively(): ?File {

        $target = $this->readlink();

        if ($target instanceof self) {

            return $target->readlinkRecursively();
        }

        return $target;
    }

    /**
     * Opens the file and returns a handle to it.
     * 
     * @api
     * @final
     * @override
     * @since 1.0.0
     * @version 1.1.0
     * 
     * @return Handles\Handle Returns a handle to the opened file.
     */
    public final function open(mixed ...$args): Handles\Handle {

        $logUnit = static::class . '::' . __FUNCTION__;

        $this->infoLog(fn () => [
            'Opening link' => [ 'path' => $this->path->path, 'args' => $args ]
        ], $logUnit);

        $handle = $this->readlink()->open(...$args);
        $handle->setLogger($this->logger);

        $this->debugLog(fn () => [
            'Opening link' => [ 'path' => $this->path->path, 'args' => $args, 'handle' => [ 'class' => $handle::class, 'id' => spl_object_id($handle) ] ]
        ], $logUnit);

        return $handle;
    }
}
