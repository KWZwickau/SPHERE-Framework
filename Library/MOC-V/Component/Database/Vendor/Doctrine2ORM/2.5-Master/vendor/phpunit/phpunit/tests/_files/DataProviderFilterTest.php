<?php

class DataProviderFilterTest extends PHPUnit_Framework_TestCase
{

    public static function truthProvider()
    {

        return array(
            array( true ),
            array( true ),
            array( true ),
            array( true )
        );
    }

    public static function falseProvider()
    {

        return array(
            'false test'        => array( false ),
            'false test 2'      => array( false ),
            'other false test'  => array( false ),
            'other false test2' => array( false )
        );
    }

    /**
     * @dataProvider truthProvider
     */
    public function testTrue( $truth )
    {

        $this->assertTrue( $truth );
    }

    /**
     * @dataProvider falseProvider
     */
    public function testFalse( $false )
    {

        $this->assertFalse( $false );
    }
}
