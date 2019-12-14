<?php
/*
 * Oracle designates this
 * particular file as subject to the "Classpath" exception as provided
 * by Oracle in the LICENSE file that accompanied this code.
 */

declare(strict_types=1);

namespace ocramius\util;

use Exception;
use ocramius\util\exception\NoSuchElementException;
use ocramius\util\exception\NullPointerException;
use function sprintf;
use function strval;

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
 *
 * @template T The type of the value residing in the Optional wrapper.
 */
final class Optional
{
    /**
     * Common instance for {@code empty()}.
     *
     * @var self
     */
    private static $EMPTY;

    /**
     * If non-null, the value; if null, indicates no value is present
     *
     * @psalm-var T
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
     * @return self an empty {@code Optional}
     *
     * @apiNote Though it may be tempting to do so, avoid testing if an object
     * is empty by comparing with {@code ==} against instances returned by
     * {@code Option.empty()}. There is no guarantee that it is a singleton.
     * Instead, use {@link #isPresent()}.
     */
    public static function newEmpty() : self
    {
        return self::$EMPTY ?: self::$EMPTY = new self();
    }

    /**
     * Returns an {@code Optional} with the specified present non-null value.
     *
     * @param mixed $value the value to be present, which must be non-null
     *
     * @return self an {@code Optional} with the value present
     *
     * @throws NullPointerException If value is null.
     *
     * @psalm-param T $value
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration
     */
    public static function of($value) : self
    {
        if ($value === null) {
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
     * @param mixed|null $value the possibly-null value to describe
     *
     * @return self an {@code Optional} with a present value if the specified value
     * is non-null, otherwise an empty {@code Optional}
     *
     * @psalm-param T|null $value
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration
     */
    public static function ofNullable($value) : self
    {
        return $value === null ? self::newEmpty() : self::of($value);
    }

    /**
     * If a value is present in this {@code Optional}, returns the value,
     * otherwise throws {@code NoSuchElementException}.
     *
     * @see Optional#isPresent()
     *
     * @return mixed the non-null value held by this {@code Optional}
     *
     * @throws NoSuchElementException If there is no value present.
     *
     * @psalm-return T
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration
     */
    public function get()
    {
        if ($this->value === null) {
            throw new NoSuchElementException('No value present');
        }

        return $this->value;
    }

    /**
     * Return {@code true} if there is a value present, otherwise {@code false}.
     *
     * @return bool {@code true} if there is a value present, otherwise {@code false}
     */
    public function isPresent() : bool
    {
        return $this->value !== null;
    }

    /**
     * If a value is present, invoke the specified consumer with the value,
     * otherwise do nothing.
     *
     * @param callable $consumer block to be executed if a value is present
     *
     * @psalm-param callable(T):void $consumer
     */
    public function ifPresent(callable $consumer) : void
    {
        if ($this->value === null) {
            return;
        }

        $consumer($this->value);
    }

    /**
     * If a value is present, and the value matches the given predicate,
     * return an {@code Optional} describing the value, otherwise return an
     * empty {@code Optional}.
     *
     * @param callable $predicate a predicate to apply to the value, if present
     *
     * @return self an {@code Optional} describing the value of this {@code Optional}
     * if a value is present and the value matches the given predicate,
     * otherwise an empty {@code Optional}
     *
     * @throws NullPointerException If the predicate is null.
     *
     * @psalm-param callable(T):self $predicate
     */
    public function filter(callable $predicate) : self
    {
        if ($this->value === null) {
            return $this;
        }

        return $predicate($this->value) ? $this : self::newEmpty();
    }

    /**
     * If a value is present, apply the provided mapping function to it,
     * and if the result is non-null, return an {@code Optional} describing the
     * result.  Otherwise return an empty {@code Optional}.
     *
     * @param callable $mapper a mapping function to apply to the value, if present
     *
     * @return self an {@code Optional} describing the result of applying a mapping
     * function to the value of this {@code Optional}, if a value is present,
     * otherwise an empty {@code Optional}
     *
     * @throws NullPointerException If the mapping function is null.
     *
     * @psalm-param callable(T):U $mapper
     * @psalm-return self<U>
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
     * @template U
     */
    public function map(callable $mapper) : self
    {
        if ($this->value === null) {
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
     *
     * @return self the result of applying an {@code Optional}-bearing mapping
     * function to the value of this {@code Optional}, if a value is present,
     * otherwise an empty {@code Optional}
     *
     * @throws NullPointerException If the mapping function is null or returns
     * a null result.
     *
     * @template U
     * @psalm-param callable(T):self<U> $mapper
     * @psalm-return self<U>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration
     */
    public function flatMap(callable $mapper)
    {
        if ($this->value === null) {
            return self::newEmpty();
        }

        $result = $mapper($this->value);

        if ($result === null) {
            throw new NullPointerException();
        }

        return $result;
    }

    /**
     * Return the value if present, otherwise return {@code other}.
     *
     * @param mixed $other the value to be returned if there is no value present, may
     * be null
     *
     * @return mixed the value, if present, otherwise {@code other}
     *
     * @psalm-param T $other
     * @psalm-return T
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration
     */
    public function orElse($other)
    {
        return $this->value ?? $other;
    }

    /**
     * Return the value if present, otherwise invoke {@code other} and return
     * the result of that invocation.
     *
     * @param callable $other a {@code Supplier} whose result is returned if no value
     * is present
     *
     * @return mixed the value if present otherwise the result of {@code other.get()}
     *
     * @throws NullPointerException If value is not present and {@code other} is
     * null.
     *
     * @psalm-param callable():T $other
     * @psalm-return T
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration
     */
    public function orElseGet(callable $other)
    {
        return $this->value ?? $other();
    }

    /**
     * Return the contained value, if present, otherwise throw an exception
     * to be created by the provided supplier.
     *
     * @param callable $exceptionSupplier The supplier which will return the exception to
     * be thrown
     *
     * @return mixed the present value
     *
     * @throws Exception If there is no value present.
     * @throws NullPointerException If no value is present and
     * {@code exceptionSupplier} is null.
     *
     * @psalm-param callable():\Throwable $exceptionSupplier
     * @psalm-return T
     * @apiNote A method reference to the exception constructor with an empty
     * argument list can be used as the supplier. For example,
     * {@code IllegalStateException::new}
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration
     */
    public function orElseThrow(callable $exceptionSupplier)
    {
        if ($this->value === null) {
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
     *
     * @return bool {code true} if the other object is "equal to" this object
     * otherwise {@code false}
     */
    public function equals($object) : bool
    {
        return $object === $this || ($object instanceof self && $object->value === $this->value);
    }

    /**
     * Returns a non-empty string representation of this Optional suitable for
     * debugging. The exact presentation format is unspecified and may vary
     * between implementations and versions.
     *
     * @return string the string representation of this instance
     *
     * @implSpec If a value is present the result must include its string
     * representation in the result. Empty and present Optionals must be
     * unambiguously differentiable.
     */
    public function __toString() : string
    {
        return $this->value === null ? 'Optional.empty' : sprintf('Optional[%s]', strval($this->value));
    }
}
