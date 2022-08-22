<?php
use PHPUnit\Framework\TestCase;


class FunctionalTest extends TestCase
{
    public function testFailure()
    {
        $this->assertTrue(false);
    }

    public function test2Success()
    {
        $this->assertTrue(true);
    }

    public function test3Failure()
    {
        $this->assertTrue(false);
    }
}