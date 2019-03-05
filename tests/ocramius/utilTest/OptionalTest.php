<?php

declare(strict_types=1);

namespace ocramius\utilTest;

use Exception;
use ocramius\util\exception\NoSuchElementException;
use ocramius\util\exception\NullPointerException;
use ocramius\util\Optional;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use stdClass;
use Throwable;

/**
 * Tests for {@see \ocramius\util\Optional}
 *
 * @covers \ocramius\util\Optional
 */
class OptionalTest extends TestCase
{
    public function testNewEmptyAlwaysProducesSameInstance() : void
    {
        $this->assertSame(Optional::newEmpty(), Optional::newEmpty());
    }

    public function testNewEmptyProducesEmptyInstance() : void
    {
        $this->assertFalse(Optional::newEmpty()->isPresent());
    }

    public function testOfNullableFromEmptyValueProducesAnEmptyInstance() : void
    {
        $this->assertSame(Optional::newEmpty(), Optional::ofNullable(null));
    }

    /**
     * @param mixed $value
     *
     * @dataProvider getValidValues
     */
    public function testOfNullableFromNonEmptyValueProducesNonEmptyInstance($value) : void
    {
        $optional = Optional::ofNullable($value);

        $this->assertNotSame(Optional::newEmpty(), $optional);

        $this->assertTrue($optional->isPresent());
        $this->assertSame($value, $optional->get());
    }

    public function testOfFromEmptyValueCausesExceptionWhenDisallowed() : void
    {
        $this->expectException(NullPointerException::class);

        Optional::of(null);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider getValidValues
     */
    public function testOfFromNonEmptyValueProducesNonEmptyInstance($value) : void
    {
        $optional = Optional::of($value);

        $this->assertNotSame(Optional::newEmpty(), $optional);

        $this->assertTrue($optional->isPresent());
        $this->assertSame($value, $optional->get());
    }

    public function testEmptyValueDisallowsGettingWrappedValue() : void
    {
        $this->expectException(NoSuchElementException::class);

        Optional::newEmpty()->get();
    }

    /**
     * @throws ReflectionException
     */
    public function testIfPresentIsNotExecutedIfValueIsNotPresent() : void
    {
        /** @var callable|MockObject $neverCalled */
        $neverCalled = $this->getMockBuilder(stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();

        $neverCalled->expects($this->never())->method('__invoke');

        Optional::newEmpty()->ifPresent($neverCalled);
    }

    /**
     * @param mixed $value
     *
     * @throws ReflectionException
     *
     * @dataProvider getValidValues
     */
    public function testIfPresentIsExecutedWhenValueIsPresent($value) : void
    {
        /** @var callable|MockObject $calledOnce */
        $calledOnce = $this->getMockBuilder(stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();

        $calledOnce->expects($this->once())->method('__invoke')->with($value);

        Optional::of($value)->ifPresent($calledOnce);
    }

    /**
     * @throws ReflectionException
     */
    public function testFilterIsNotExecutedIfValueIsNotPresent() : void
    {
        /** @var callable|MockObject $neverCalled */
        $neverCalled = $this->getMockBuilder(stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();

        $neverCalled->expects($this->never())->method('__invoke');

        $this->assertSame(Optional::newEmpty(), Optional::newEmpty()->filter($neverCalled));
    }

    /**
     * @param mixed $value
     *
     * @throws ReflectionException
     *
     * @dataProvider getValidValues
     */
    public function testFilteringProducesEmptyOptionalWhenValueIsNotAccepted($value) : void
    {
        /** @var callable|MockObject $falseFilter */
        $falseFilter = $this->getMockBuilder(stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();

        $falseFilter->expects($this->once())->method('__invoke')->with($value)->will($this->returnValue(false));

        $this->assertSame(Optional::newEmpty(), Optional::of($value)->filter($falseFilter));
    }

    /**
     * @param mixed $value
     *
     * @throws ReflectionException
     *
     * @dataProvider getValidValues
     */
    public function testFilteringProducesSameOptionalInstanceWhenValueIsAccepted($value) : void
    {
        /** @var callable|MockObject $falseFilter */
        $falseFilter = $this->getMockBuilder(stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();
        $optional    = Optional::of($value);

        $falseFilter->expects($this->once())->method('__invoke')->with($value)->will($this->returnValue(true));

        $this->assertSame($optional, $optional->filter($falseFilter));
    }

    /**
     * @throws ReflectionException
     */
    public function testMappingEmptyOptionalProducesEmptyOptional() : void
    {
        /** @var callable|MockObject $neverCalled */
        $neverCalled = $this->getMockBuilder(stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();

        $neverCalled->expects($this->never())->method('__invoke');

        $this->assertSame(Optional::newEmpty(), Optional::newEmpty()->map($neverCalled));
    }

    /**
     * @param mixed $value
     *
     * @throws ReflectionException
     *
     * @dataProvider getValidValues
     */
    public function testMappingNonEmptyValuesProducesOptionalWithMapMethodReturnValue($value) : void
    {
        $mappedValue = new stdClass();
        /** @var callable|MockObject $mapper */
        $mapper = $this->getMockBuilder(stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();

        $mapper->expects($this->once())->method('__invoke')->with($value)->will($this->returnValue($mappedValue));

        $optional = Optional::of($value)->map($mapper);

        $this->assertNotSame(Optional::newEmpty(), $optional);
        $this->assertSame($mappedValue, $optional->get());
    }

    /**
     * @throws ReflectionException
     */
    public function testMappingNonEmptyValuesMayProduceEmptyOptional() : void
    {
        /** @var callable|MockObject $mapper */
        $mapper = $this->getMockBuilder(stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();

        $mapper->expects($this->once())->method('__invoke')->will($this->returnValue(null));

        $this->assertSame(Optional::newEmpty(), Optional::of(new stdClass())->map($mapper));
    }

    /**
     * @throws ReflectionException
     */
    public function testFlatMappingEmptyOptionalProducesEmptyOptional() : void
    {
        /** @var callable|MockObject $neverCalled */
        $neverCalled = $this->getMockBuilder(stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();

        $neverCalled->expects($this->never())->method('__invoke');

        $this->assertSame(Optional::newEmpty(), Optional::newEmpty()->flatMap($neverCalled));
    }

    /**
     * @param mixed $value
     *
     * @throws ReflectionException
     *
     * @dataProvider getValidValues
     */
    public function testFlatMappingNonEmptyOptionalProducesNonEmptyOptional($value) : void
    {
        $mappedValue = new stdClass();
        /** @var callable|MockObject $mapper */
        $mapper = $this->getMockBuilder(stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();

        $mapper->expects($this->once())->method('__invoke')->with($value)->will($this->returnValue($mappedValue));

        $this->assertSame($mappedValue, Optional::of($value)->flatMap($mapper));
    }

    /**
     * @throws ReflectionException
     */
    public function testFlatMappingNonEmptyOptionalDisallowsEmptyMapperResult() : void
    {
        /** @var callable|MockObject $mapper */
        $mapper = $this->getMockBuilder(stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();

        $mapper->expects($this->once())->method('__invoke')->will($this->returnValue(null));

        $this->expectException(NullPointerException::class);

        Optional::of(new stdClass())->flatMap($mapper);
    }

    /**
     * @param mixed $value
     *
     * @dataProvider getValidValues
     */
    public function testOrElseRetrievesGivenValueOnEmptyOptional($value) : void
    {
        $this->assertSame($value, Optional::newEmpty()->orElse($value));
    }

    /**
     * @param mixed $value
     *
     * @dataProvider getValidValues
     */
    public function testOrElseRetrievesOptionalValueWhenValueIsPresent($value) : void
    {
        $this->assertSame($value, Optional::of($value)->orElse(new stdClass()));
    }

    /**
     * @param mixed $value
     *
     * @throws ReflectionException
     *
     * @dataProvider getValidValues
     */
    public function testOrElseGetRetrievesCallableReturnValueOnEmptyOptional($value) : void
    {
        /** @var callable|MockObject $fallback */
        $fallback = $this->getMockBuilder(stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();

        $fallback->expects($this->once())->method('__invoke')->will($this->returnValue($value));

        $this->assertSame($value, Optional::newEmpty()->orElseGet($fallback));
    }

    /**
     * @param mixed $value
     *
     * @throws ReflectionException
     * @throws Exception
     *
     * @dataProvider getValidValues
     */
    public function testOrElseThrowRetrievesGivenValueWhenValueIsAvailable($value) : void
    {
        /** @var callable|MockObject $exceptionFactory */
        $exceptionFactory = $this->getMockBuilder(stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();

        $exceptionFactory->expects($this->never())->method('__invoke');

        $this->assertSame($value, Optional::of($value)->orElseThrow($exceptionFactory));
    }

    /**
     * @throws ReflectionException
     */
    public function testOrElseThrowThrowsExceptionOnEmptyOptional() : void
    {
        $exception = new Exception();
        /** @var callable|MockObject $exceptionFactory */
        $exceptionFactory = $this->getMockBuilder(stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();

        $exceptionFactory->expects($this->once())->method('__invoke')->will($this->returnValue($exception));

        try {
            Optional::newEmpty()->orElseThrow($exceptionFactory);

            $this->fail('No exception was thrown, expected Optional#orElseThrow() to throw one');
        } catch (Throwable $caught) {
            $this->assertSame($exception, $caught);
        }
    }

    /**
     * @param mixed $value
     *
     * @throws ReflectionException
     *
     * @dataProvider getValidValues
     */
    public function testOrElseGetRetrievesOptionalValueIfValueIsPresent($value) : void
    {
        /** @var callable|MockObject $fallback */
        $fallback = $this->getMockBuilder(stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();

        $fallback->expects($this->never())->method('__invoke');

        $this->assertSame($value, Optional::of($value)->orElseGet($fallback));
    }

    public function testInequality() : void
    {
        $value1 = new stdClass();
        $value2 = new stdClass();

        $this->assertFalse(Optional::of($value1)->equals(Optional::of($value2)));
        $this->assertFalse(Optional::of($value1)->equals($value1));
        $this->assertFalse(Optional::of($value1)->equals('foo'));
        $this->assertTrue(Optional::newEmpty()->equals(Optional::newEmpty()));
    }

    /**
     * @param mixed $value
     *
     * @dataProvider getValidValues
     */
    public function testEquals($value) : void
    {
        $this->assertTrue(Optional::of($value)->equals(Optional::of($value)));
    }

    /**
     * @param mixed $value
     *
     * @dataProvider getValidValues
     */
    public function testInequalityWithEmptyOptional($value) : void
    {
        $this->assertFalse(Optional::of($value)->equals(Optional::newEmpty()));
        $this->assertFalse(Optional::newEmpty()->equals(Optional::of($value)));
    }

    public function testStringCast() : void
    {
        $this->assertSame('Optional.empty', (string) Optional::newEmpty());
        $this->assertSame('Optional[foo]', (string) Optional::of('foo'));
    }

    /**
     * Data provider: provides valid Optional values
     *
     * @return mixed[][]
     *
     * @throws ReflectionException
     */
    public function getValidValues() : array
    {
        return [
            [new stdClass()],
            [$this->createMock(stdClass::class)],
            [''],
            ['foo'],
            [123],
            [123.456],
            [['foo', 'bar']],
            [[]],
        ];
    }
}
