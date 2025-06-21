<?php

namespace Files;

use Lang\Annotations\Computes;
use Lang\Annotations\Sets;

/**
 * Link.
 * 
 * @api
 * @since 1.0.0
 * @version 1.0.0
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
     * @version 1.0.0
     * 
     * @param string|int $group The group name or ID.
     * 
     * @return bool Returns true on success, false on failure.
     */
    #[Sets('gid')]
    public final function chgrp(string|int $group): bool {

        return lchgrp($this->path, $group);
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
     * @version 1.0.0
     * 
     * @param string|int $user The user name or ID.
     * 
     * @return bool Returns true on success, false on failure.
     */
    #[Sets('uid')]
    public final function chown(string|int $user): bool {

        return lchown($this->path, $user);
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
     * @version 1.0.0
     * 
     * @return File|null Returns the target file if it exists, null otherwise.
     */
    #[Computes('target')]
    public final function readlink(): ?File {

        if (false === ($path = readlink($this->path))) {

            return null;
        }

        return File::make($path);
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
     * @version 1.0.0
     * 
     * @return Handles\Handle Returns a handle to the opened file.
     */
    public final function open(mixed ...$args): Handles\Handle {

        return $this->readlink()->open(...$args);
    }
}
