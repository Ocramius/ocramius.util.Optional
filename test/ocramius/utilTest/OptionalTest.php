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

    public function testIfPresentIsNotExecutedIfValueIsNotPresent()
    {
        /* @var $neverCalled callable|\PHPUnit_Framework_MockObject_MockObject */
        $neverCalled = $this->getMock('stdClass', ['__invoke']);

        $neverCalled->expects($this->never())->method('__invoke');

        Optional::newEmpty()->ifPresent($neverCalled);
    }

    public function testIfPresentIsExecutedWhenValueIsPresent()
    {
        $value      = new stdClass();
        /* @var $calledOnce callable|\PHPUnit_Framework_MockObject_MockObject */
        $calledOnce = $this->getMock('stdClass', ['__invoke']);

        $calledOnce->expects($this->once())->method('__invoke')->with($value);

        Optional::of($value)->ifPresent($calledOnce);
    }

    public function testFilterIsNotExecutedIfValueIsNotPresent()
    {
        /* @var $neverCalled callable|\PHPUnit_Framework_MockObject_MockObject */
        $neverCalled = $this->getMock('stdClass', ['__invoke']);

        $neverCalled->expects($this->never())->method('__invoke');

        $this->assertSame(Optional::newEmpty(), Optional::newEmpty()->filter($neverCalled));
    }

    public function testFilteringProducesEmptyOptionalWhenValueIsNotAccepted()
    {
        $value       = new stdClass();
        /* @var $falseFilter callable|\PHPUnit_Framework_MockObject_MockObject */
        $falseFilter = $this->getMock('stdClass', ['__invoke']);

        $falseFilter->expects($this->once())->method('__invoke')->with($value)->will($this->returnValue(false));

        $this->assertSame(Optional::newEmpty(), Optional::of($value)->filter($falseFilter));
    }

    public function testFilteringProducesSameOptionalInstanceWhenValueIsAccepted()
    {
        $value       = new stdClass();
        /* @var $falseFilter callable|\PHPUnit_Framework_MockObject_MockObject */
        $falseFilter = $this->getMock('stdClass', ['__invoke']);
        $optional    = Optional::of($value);

        $falseFilter->expects($this->once())->method('__invoke')->with($value)->will($this->returnValue(true));

        $this->assertSame($optional, $optional->filter($falseFilter));
    }

    public function testMappingEmptyOptionalProducesEmptyOptional()
    {
        /* @var $neverCalled callable|\PHPUnit_Framework_MockObject_MockObject */
        $neverCalled = $this->getMock('stdClass', ['__invoke']);

        $neverCalled->expects($this->never())->method('__invoke');

        $this->assertSame(Optional::newEmpty(), Optional::newEmpty()->map($neverCalled));
    }

    public function testMappingNonEmptyValuesProducesOptionalWithMapMethodReturnValue()
    {
        $value       = new stdClass();
        $mappedValue = new stdClass();
        /* @var $mapper callable|\PHPUnit_Framework_MockObject_MockObject */
        $mapper      = $this->getMock('stdClass', ['__invoke']);

        $mapper->expects($this->once())->method('__invoke')->with($value)->will($this->returnValue($mappedValue));

        $optional = Optional::of($value)->map($mapper);

        $this->assertNotSame(Optional::newEmpty(), $optional);
        $this->assertSame($mappedValue, $optional->get());
    }

    public function testMappingNonEmptyValuesMayProduceEmptyOptional()
    {
        /* @var $mapper callable|\PHPUnit_Framework_MockObject_MockObject */
        $mapper = $this->getMock('stdClass', ['__invoke']);

        $mapper->expects($this->once())->method('__invoke')->will($this->returnValue(null));

        $this->assertSame(Optional::newEmpty(), Optional::of(new stdClass())->map($mapper));
    }

    public function testFlatMappingEmptyOptionalProducesEmptyOptional()
    {
        /* @var $neverCalled callable|\PHPUnit_Framework_MockObject_MockObject */
        $neverCalled = $this->getMock('stdClass', ['__invoke']);

        $neverCalled->expects($this->never())->method('__invoke');

        $this->assertSame(Optional::newEmpty(), Optional::newEmpty()->flatMap($neverCalled));
    }

    public function testFlatMappingNonEmptyOptionalProducesNonEmptyOptional()
    {
        $value       = new stdClass();
        $mappedValue = new stdClass();
        /* @var $mapper callable|\PHPUnit_Framework_MockObject_MockObject */
        $mapper      = $this->getMock('stdClass', ['__invoke']);

        $mapper->expects($this->once())->method('__invoke')->with($value)->will($this->returnValue($mappedValue));

        $this->assertSame($mappedValue, Optional::of($value)->flatMap($mapper));
    }

    public function testFlatMappingNonEmptyOptionalDisallowsEmptyMapperResult()
    {
        /* @var $mapper callable|\PHPUnit_Framework_MockObject_MockObject */
        $mapper = $this->getMock('stdClass', ['__invoke']);

        $mapper->expects($this->once())->method('__invoke')->will($this->returnValue(null));

        $this->setExpectedException(NullPointerException::class);

        Optional::of(new stdClass())->flatMap($mapper);
    }

    public function testOrElseRetrievesGivenValueOnEmptyOptional()
    {
        $value = new stdClass();

        $this->assertSame($value, Optional::newEmpty()->orElse($value));
    }

    public function testOrElseRetrievesOptionalValueWhenValueIsPresent()
    {
        $value = new stdClass();

        $this->assertSame($value, Optional::of($value)->orElse(new stdClass()));
    }

    public function testOrElseGetRetrievesCallableReturnValueOnEmptyOptional()
    {
        $value    = new stdClass();
        /* @var $fallback callable|\PHPUnit_Framework_MockObject_MockObject */
        $fallback = $this->getMock('stdClass', ['__invoke']);

        $fallback->expects($this->once())->method('__invoke')->will($this->returnValue($value));

        $this->assertSame($value, Optional::newEmpty()->orElseGet($fallback));
    }

    public function testOrElseGetRetrievesOptionalValueIfValueIsPresent()
    {
        $value    = new stdClass();
        /* @var $fallback callable|\PHPUnit_Framework_MockObject_MockObject */
        $fallback = $this->getMock('stdClass', ['__invoke']);

        $fallback->expects($this->never())->method('__invoke');

        $this->assertSame($value, Optional::of($value)->orElseGet($fallback));
    }

    public function testEquals()
    {
        $value1 = new stdClass();
        $value2 = new stdClass();

        $this->assertFalse(Optional::of($value1)->equals(Optional::of($value2)));
        $this->assertTrue(Optional::of($value1)->equals(Optional::of($value1)));
        $this->assertFalse(Optional::of($value1)->equals($value1));
        $this->assertFalse(Optional::of($value1)->equals('foo'));
        $this->assertTrue(Optional::newEmpty()->equals(Optional::newEmpty()));
    }

    public function testStringCast()
    {
        $this->assertSame('Optional.empty', (string) Optional::newEmpty());
        $this->assertSame('Optional[foo]', (string) Optional::of('foo'));
    }
}
