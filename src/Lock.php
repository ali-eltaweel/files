<?php

namespace Files;

/**
 * Lock Type.
 * 
 * @api
 * @since 1.0.0
 * @version 1.0.0
 * @package files
 * @author Ali M. Kamel <ali.kamel.dev@gmail.com>
 */
enum Lock {

    case Shared;
    
    case Exclusive;
}
