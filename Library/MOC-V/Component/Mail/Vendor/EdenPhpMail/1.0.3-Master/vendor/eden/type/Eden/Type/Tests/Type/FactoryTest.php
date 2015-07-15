<?php //-->
/*
 * This file is part of the Type package of the Eden PHP Library.
 * (c) 2013-2014 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */

class Eden_Type_Tests_Type_FactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testType()
    {

        $class = eden( 'type' );
        $this->assertInstanceOf( 'Eden\\Type\\Factory', $class );

        $class = eden( 'type', 'something' );
        $this->assertInstanceOf( 'Eden\\Type\\StringType', $class );

        $class = eden( 'type', 1, 2, 3 );
        $this->assertInstanceOf( 'Eden\\Type\\ArrayType', $class );
    }

    public function testGetArray()
    {

        $class = eden( 'type' )->getArray( array( 'some data' ) );
        $this->assertInstanceOf( 'Eden\\Type\\ArrayType', $class );
    }

    public function testGetString()
    {

        $class = eden( 'type' )->getString( 'some data' );
        $this->assertInstanceOf( 'Eden\\Type\\StringType', $class );
    }
}
