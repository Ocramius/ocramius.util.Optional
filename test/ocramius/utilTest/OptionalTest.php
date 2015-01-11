<?php

namespace ocramius\utilTest;


use ocramius\util\Optional;
use PHPUnit_Framework_TestCase;

/**
 * Tests for {@see \ocramius\util\Optional}
 *
 * @covers \ocramius\util\Optional
 */
class OptionalTest extends PHPUnit_Framework_TestCase
{
    public function testNewEmptyAlwaysReturnsSameInstance()
    {
        $this->assertSame(Optional::newEmpty(), Optional::newEmpty());
    }

    public function testNewEmptyProducesEmptyInstance()
    {
        $this->assertFalse(Optional::newEmpty()->isPresent());
    }
}
