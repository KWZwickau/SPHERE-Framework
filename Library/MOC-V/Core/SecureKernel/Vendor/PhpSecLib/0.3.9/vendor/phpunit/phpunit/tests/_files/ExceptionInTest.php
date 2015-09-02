<?php
class ExceptionInTest extends PHPUnit_Framework_TestCase
{

    public $setUp = false;
    public $assertPreConditions = false;
    public $assertPostConditions = false;
    public $tearDown = false;
    public $testSomething = false;

    public function testSomething()
    {

        $this->testSomething = true;
        throw new Exception;
    }

    protected function setUp()
    {
        $this->setUp = true;
    }

    protected function assertPreConditions()
    {
        $this->assertPreConditions = true;
    }

    protected function assertPostConditions()
    {
        $this->assertPostConditions = true;
    }

    protected function tearDown()
    {
        $this->tearDown = true;
    }
}
