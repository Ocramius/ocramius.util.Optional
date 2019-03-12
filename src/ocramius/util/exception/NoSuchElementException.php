<?php
/*
 * Oracle designates this
 * particular file as subject to the "Classpath" exception as provided
 * by Oracle in the LICENSE file that accompanied this code.
 */

declare(strict_types=1);

namespace ocramius\util\exception;

use RuntimeException;

/**
 * Thrown by various accessor methods to indicate that the element being requested
 * does not exist.
 */
class NoSuchElementException extends RuntimeException
{
}
