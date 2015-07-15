<?php

/**
 * @requires extension I_DO_NOT_EXIST
 */
class Issue1374Test extends PHPUnit_Framework_TestCase
{

    public function testSomething()
    {

        $this->fail( 'This should not be reached' );
    }

    protected function setUp()
    {

        print __FUNCTION__;
    }

    protected function tearDown()
    {

        print __FUNCTION__;
    }
}
