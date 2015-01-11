<?php

namespace ocramius\utilTest;


use ocramius\util\exception\NoSuchElementException;
use ocramius\util\exception\NullPointerException;
use ocramius\util\Optional;
use PHPUnit_Framework_TestCase;
use stdClass;

/**
 * Tests for {@see \ocramius\util\Optional}
 *
 * @covers \ocramius\util\Optional
 */
class OptionalTest extends PHPUnit_Framework_TestCase
{
    public function testNewEmptyAlwaysProducesSameInstance()
    {
        $this->assertSame(Optional::newEmpty(), Optional::newEmpty());
    }

    public function testNewEmptyProducesEmptyInstance()
    {
        $this->assertFalse(Optional::newEmpty()->isPresent());
    }

    public function testOfNullableFromEmptyValueProducesAnEmptyInstance()
    {
        $this->assertSame(Optional::newEmpty(), Optional::ofNullable(null));
    }

    public function testOfNullableFromNonEmptyValueProducesNonEmptyInstance()
    {
        $value    = new stdClass();
        $optional = Optional::ofNullable($value);

        $this->assertNotSame(Optional::newEmpty(), $optional);

        $this->assertTrue($optional->isPresent());
        $this->assertSame($value, $optional->get());
    }

    public function testOfFromEmptyValueCausesExceptionWhenDisallowed()
    {
        $this->setExpectedException(NullPointerException::class);

        Optional::of(null);
    }

    public function testOfFromNonEmptyValueProducesNonEmptyInstance()
    {
        $value    = new stdClass();
        $optional = Optional::of($value);

        $this->assertNotSame(Optional::newEmpty(), $optional);

        $this->assertTrue($optional->isPresent());
        $this->assertSame($value, $optional->get());
    }

    public function testEmptyValueDisallowsGettingWrappedValue()
    {
        $this->setExpectedException(NoSuchElementException::class);

        Optional::newEmpty()->get();
    }

    public function testIfPresentIsRunOnlyWithWhenValueIsPresent()
    {
        $value    = new stdClass();
        /* @var $callable callable|\PHPUnit_Framework_MockObject_MockObject */
        $callable = $this->getMock('stdClass', ['__invoke']);

        $callable->expects($this->once())->method('__invoke')->with($value);

        Optional::newEmpty()->ifPresent($callable);
        Optional::of($value)->ifPresent($callable);
    }
}
