<?php

namespace ocramius\utilTest;

use Exception;
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

    /**
     * @dataProvider getValidValues
     *
     * @param mixed $value
     */
    public function testOfNullableFromNonEmptyValueProducesNonEmptyInstance($value)
    {
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

    /**
     * @dataProvider getValidValues
     *
     * @param mixed $value
     */
    public function testOfFromNonEmptyValueProducesNonEmptyInstance($value)
    {
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

    /**
     * @dataProvider getValidValues
     *
     * @param mixed $value
     */
    public function testIfPresentIsExecutedWhenValueIsPresent($value)
    {
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

    /**
     * @dataProvider getValidValues
     *
     * @param mixed $value
     */
    public function testFilteringProducesEmptyOptionalWhenValueIsNotAccepted($value)
    {
        /* @var $falseFilter callable|\PHPUnit_Framework_MockObject_MockObject */
        $falseFilter = $this->getMock('stdClass', ['__invoke']);

        $falseFilter->expects($this->once())->method('__invoke')->with($value)->will($this->returnValue(false));

        $this->assertSame(Optional::newEmpty(), Optional::of($value)->filter($falseFilter));
    }

    /**
     * @dataProvider getValidValues
     *
     * @param mixed $value
     */
    public function testFilteringProducesSameOptionalInstanceWhenValueIsAccepted($value)
    {
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

    /**
     * @dataProvider getValidValues
     *
     * @param mixed $value
     */
    public function testMappingNonEmptyValuesProducesOptionalWithMapMethodReturnValue($value)
    {
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

    /**
     * @dataProvider getValidValues
     *
     * @param mixed $value
     */
    public function testFlatMappingNonEmptyOptionalProducesNonEmptyOptional($value)
    {
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

    /**
     * @dataProvider getValidValues
     *
     * @param mixed $value
     */
    public function testOrElseRetrievesGivenValueOnEmptyOptional($value)
    {
        $this->assertSame($value, Optional::newEmpty()->orElse($value));
    }

    /**
     * @dataProvider getValidValues
     *
     * @param mixed $value
     */
    public function testOrElseRetrievesOptionalValueWhenValueIsPresent($value)
    {
        $this->assertSame($value, Optional::of($value)->orElse(new stdClass()));
    }

    /**
     * @dataProvider getValidValues
     *
     * @param mixed $value
     */
    public function testOrElseGetRetrievesCallableReturnValueOnEmptyOptional($value)
    {
        /* @var $fallback callable|\PHPUnit_Framework_MockObject_MockObject */
        $fallback = $this->getMock('stdClass', ['__invoke']);

        $fallback->expects($this->once())->method('__invoke')->will($this->returnValue($value));

        $this->assertSame($value, Optional::newEmpty()->orElseGet($fallback));
    }

    /**
     * @dataProvider getValidValues
     *
     * @param mixed $value
     */
    public function testOrElseThrowRetrievesGivenValueWhenValueIsAvailable($value)
    {
        /* @var $exceptionFactory callable|\PHPUnit_Framework_MockObject_MockObject */
        $exceptionFactory = $this->getMock('stdClass', ['__invoke']);

        $exceptionFactory->expects($this->never())->method('__invoke');

        $this->assertSame($value, Optional::of($value)->orElseThrow($exceptionFactory));
    }

    public function testOrElseThrowThrowsExceptionOnEmptyOptional()
    {
        $exception        = new Exception();
        /* @var $exceptionFactory callable|\PHPUnit_Framework_MockObject_MockObject */
        $exceptionFactory = $this->getMock('stdClass', ['__invoke']);

        $exceptionFactory->expects($this->once())->method('__invoke')->will($this->returnValue($exception));

        try {
            Optional::newEmpty()->orElseThrow($exceptionFactory);

            $this->fail('No exception was thrown, expected Optional#orElseThrow() to throw one');
        } catch (Exception $caught) {
            $this->assertSame($exception, $caught);
        }
    }

    /**
     * @dataProvider getValidValues
     *
     * @param mixed $value
     */
    public function testOrElseGetRetrievesOptionalValueIfValueIsPresent($value)
    {
        /* @var $fallback callable|\PHPUnit_Framework_MockObject_MockObject */
        $fallback = $this->getMock('stdClass', ['__invoke']);

        $fallback->expects($this->never())->method('__invoke');

        $this->assertSame($value, Optional::of($value)->orElseGet($fallback));
    }

    public function testInequality()
    {
        $value1 = new stdClass();
        $value2 = new stdClass();

        $this->assertFalse(Optional::of($value1)->equals(Optional::of($value2)));
        $this->assertFalse(Optional::of($value1)->equals($value1));
        $this->assertFalse(Optional::of($value1)->equals('foo'));
        $this->assertTrue(Optional::newEmpty()->equals(Optional::newEmpty()));
    }

    /**
     * @dataProvider getValidValues
     *
     * @param mixed $value
     */
    public function testEquals($value)
    {
        $this->assertTrue(Optional::of($value)->equals(Optional::of($value)));
    }

    /**
     * @dataProvider getValidValues
     *
     * @param mixed $value
     */
    public function testInequalityWithEmptyOptional($value)
    {
        $this->assertFalse(Optional::of($value)->equals(Optional::newEmpty()));
        $this->assertFalse(Optional::newEmpty()->equals(Optional::of($value)));
    }

    public function testStringCast()
    {
        $this->assertSame('Optional.empty', (string) Optional::newEmpty());
        $this->assertSame('Optional[foo]', (string) Optional::of('foo'));
    }

    /**
     * Data provider: provides valid Optional values
     *
     * @return mixed[][]
     */
    public function getValidValues()
    {
        return [
            [new stdClass()],
            [$this->getMock('stdClass')],
            [''],
            ['foo'],
            [123],
            [123.456],
            [['foo', 'bar']],
            [[]],
        ];
    }
}
