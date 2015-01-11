<?php
/*
 * Copyright (c) 2012, 2013, Oracle and/or its affiliates. All rights reserved.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This code is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License version 2 only, as
 * published by the Free Software Foundation.  Oracle designates this
 * particular file as subject to the "Classpath" exception as provided
 * by Oracle in the LICENSE file that accompanied this code.
 *
 * This code is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
 * version 2 for more details (a copy is included in the LICENSE file that
 * accompanied this code).
 *
 * You should have received a copy of the GNU General Public License version
 * 2 along with this work; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * Please contact Oracle, 500 Oracle Parkway, Redwood Shores, CA 94065 USA
 * or visit www.oracle.com if you need additional information or have any
 * questions.
 */

namespace ocramius\util;

use ocramius\util\exception\NullPointerException;
use ocramius\util\exception\NoSuchElementException;

/**
 * A container object which may or may not contain a non-null value.
 * If a value is present, {@code isPresent()} will return {@code true} and
 * {@code get()} will return the value.
 *
 * <p>Additional methods that depend on the presence or absence of a contained
 * value are provided, such as {@link #orElse(java.lang.Object) orElse()}
 * (return a default value if value not present) and
 * {@link #ifPresent(java.util.function.Consumer) ifPresent()} (execute a block
 * of code if the value is present).
 *
 * <p>This is a value-based class; use of identity-sensitive operations
 * (including reference equality ({@code ==}), identity hash code, or
 * synchronization) on instances of {@code Optional} may have unpredictable
 * results and should be avoided.
 */
final class Optional
{
    /**
     * Common instance for {@code empty()}.
     */
    private static $EMPTY;

    /**
     * If non-null, the value; if null, indicates no value is present
     *
     * @var mixed
     */
    private $value;

    /**
     * Constructs an empty instance.
     *
     * @implNote Generally only one empty instance, {@link Optional#EMPTY},
     * should exist per VM.
     */
    private function __construct()
    {
    }

    /**
     * Returns an empty {@code Optional} instance.  No value is present for this
     * Optional.
     *
     * @apiNote Though it may be tempting to do so, avoid testing if an object
     * is empty by comparing with {@code ==} against instances returned by
     * {@code Option.empty()}. There is no guarantee that it is a singleton.
     * Instead, use {@link #isPresent()}.
     *
     * @param <T> Type of the non-existent value
     * @return self an empty {@code Optional}
     */
    public static function newEmpty()
    {
        return self::$EMPTY ?: self::$EMPTY = new self();
    }

    /**
     * Returns an {@code Optional} with the specified present non-null value.
     *
     * @param mixed $value the value to be present, which must be non-null
     * @return self an {@code Optional} with the value present
     *
     * @throws NullPointerException if value is null
     */
    public static function of($value)
    {
        if (null === $value) {
            throw new NullPointerException();
        }

        $self = new self();

        $self->value = $value;

        return $self;
    }

    /**
     * Returns an {@code Optional} describing the specified value, if non-null,
     * otherwise returns an empty {@code Optional}.
     *
     * @param mixed $value the possibly-null value to describe
     * @return self an {@code Optional} with a present value if the specified value
     * is non-null, otherwise an empty {@code Optional}
     */
    public static function ofNullable($value)
    {
        return $value === null ? self::newEmpty() : self::of($value);
    }

    /**
     * If a value is present in this {@code Optional}, returns the value,
     * otherwise throws {@code NoSuchElementException}.
     *
     * @return mixed the non-null value held by this {@code Optional}
     * @throws NoSuchElementException if there is no value present
     *
     * @see Optional#isPresent()
     */
    public function get()
    {
        if (null === $this->value) {
            throw new NoSuchElementException("No value present");
        }

        return $this->value;
    }

    /**
     * Return {@code true} if there is a value present, otherwise {@code false}.
     *
     * @return bool {@code true} if there is a value present, otherwise {@code false}
     */
    public function isPresent()
    {
        return null !== $this->value;
    }

    /**
     * If a value is present, invoke the specified consumer with the value,
     * otherwise do nothing.
     *
     * @param callable $consumer block to be executed if a value is present
     *
     * @return void
     */
    public function ifPresent(callable $consumer)
    {
        if (null !== $this->value) {
            $consumer($this->value);
        }
    }

    /**
     * If a value is present, and the value matches the given predicate,
     * return an {@code Optional} describing the value, otherwise return an
     * empty {@code Optional}.
     *
     * @param callable $predicate a predicate to apply to the value, if present
     * @return self an {@code Optional} describing the value of this {@code Optional}
     * if a value is present and the value matches the given predicate,
     * otherwise an empty {@code Optional}
     * @throws NullPointerException if the predicate is null
     */
    public function filter(callable $predicate)
    {
        if (null === $this->value) {
            return $this;
        }

        return $predicate($this->value) ? $this : self::newEmpty();
    }

    /**
     * If a value is present, apply the provided mapping function to it,
     * and if the result is non-null, return an {@code Optional} describing the
     * result.  Otherwise return an empty {@code Optional}.
     *
     * @apiNote This method supports post-processing on optional values, without
     * the need to explicitly check for a return status.  For example, the
     * following code traverses a stream of file names, selects one that has
     * not yet been processed, and then opens that file, returning an
     * {@code Optional<FileInputStream>}:
     *
     * <pre>{@code
     *     Optional<FileInputStream> fis =
     *         names.stream().filter(name -> !isProcessedYet(name))
     *                       .findFirst()
     *                       .map(name -> new FileInputStream(name));
     * }</pre>
     *
     * Here, {@code findFirst} returns an {@code Optional<String>}, and then
     * {@code map} returns an {@code Optional<FileInputStream>} for the desired
     * file if one exists.
     *
     * @param callable $mapper a mapping function to apply to the value, if present
     * @return self an {@code Optional} describing the result of applying a mapping
     * function to the value of this {@code Optional}, if a value is present,
     * otherwise an empty {@code Optional}
     * @throws NullPointerException if the mapping function is null
     */
    public function map(callable $mapper)
    {
        if (null === $this->value) {
            return self::newEmpty();
        }

        return self::ofNullable($mapper($this->value));
    }

    /**
     * If a value is present, apply the provided {@code Optional}-bearing
     * mapping function to it, return that result, otherwise return an empty
     * {@code Optional}.  This method is similar to {@link #map(Function)},
     * but the provided mapper is one whose result is already an {@code Optional},
     * and if invoked, {@code flatMap} does not wrap it with an additional
     * {@code Optional}.
     *
     * @param callable $mapper a mapping function to apply to the value, if present
     *           the mapping function
     * @return mixed the result of applying an {@code Optional}-bearing mapping
     * function to the value of this {@code Optional}, if a value is present,
     * otherwise an empty {@code Optional}
     * @throws NullPointerException if the mapping function is null or returns
     * a null result
     */
    public function flatMap(callable $mapper)
    {
        if (null === $this->value) {
            return self::newEmpty();
        }

        $result = $mapper($this->value);

        if (null === $result) {
            throw new NullPointerException();
        }

        return $result;
    }

    /**
     * Return the value if present, otherwise return {@code other}.
     *
     * @param mixed $other the value to be returned if there is no value present, may
     * be null
     * @return mixed the value, if present, otherwise {@code other}
     */
    public function orElse($other)
    {
        return null === $this->value ? $other : $this->value;
    }

    /**
     * Return the value if present, otherwise invoke {@code other} and return
     * the result of that invocation.
     *
     * @param callable $other a {@code Supplier} whose result is returned if no value
     * is present
     * @return mixed the value if present otherwise the result of {@code other.get()}
     * @throws NullPointerException if value is not present and {@code other} is
     * null
     */
    public function orElseGet(callable $other)
    {
        return null === $this->value ? $other() : $this->value;
    }

    /**
     * Return the contained value, if present, otherwise throw an exception
     * to be created by the provided supplier.
     *
     * @apiNote A method reference to the exception constructor with an empty
     * argument list can be used as the supplier. For example,
     * {@code IllegalStateException::new}
     *
     * @param callable $exceptionSupplier The supplier which will return the exception to
     * be thrown
     * @return mixed the present value
     * @throws \Exception if there is no value present
     * @throws NullPointerException if no value is present and
     * {@code exceptionSupplier} is null
     */
    public function orElseThrow(callable $exceptionSupplier)
    {
        if (null === $this->value) {
            throw $exceptionSupplier();
        }

        return $this->value;
    }

    /**
     * Indicates whether some other object is "equal to" this Optional. The
     * other object is considered equal if:
     * <ul>
     * <li>it is also an {@code Optional} and;
     * <li>both instances have no value present or;
     * <li>the present values are "equal to" each other via {@code equals()}.
     * </ul>
     *
     * @param mixed $object an object to be tested for equality
     * @return bool {code true} if the other object is "equal to" this object
     * otherwise {@code false}
     */
    public function equals($object)
    {
        return $object === $this || ($object instanceof self && $object->value === $this->value);
    }

    /**
     * Returns a non-empty string representation of this Optional suitable for
     * debugging. The exact presentation format is unspecified and may vary
     * between implementations and versions.
     *
     * @implSpec If a value is present the result must include its string
     * representation in the result. Empty and present Optionals must be
     * unambiguously differentiable.
     *
     * @return string the string representation of this instance
     */
    public function __toString() {
        return $this->value === null ? 'Optional.empty' : sprintf('Optional[%s]', $this->value);
    }
}
