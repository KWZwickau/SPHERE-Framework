<?php //-->
/*
 * This file is part of the Type package of the Eden PHP Library.
 * (c) 2013-2014 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */

class Eden_Type_Tests_Type_ArrayTypeTest extends \PHPUnit_Framework_TestCase
{

    public function testCopy()
    {

        $data = array( 'first' => 'bar', 'second' => 'foo' );
        $class = eden( 'type', $data )->copy( 'first', 'third' );
        $this->assertInstanceOf( 'Eden\\Type\\ArrayType', $class );
        $this->assertArrayHasKey( 'third', $class->get() );
    }

    public function testCount()
    {

        $data = array( 'bar', 'foo' );
        $num = eden( 'type', $data )->count();
        $this->assertCount( $num, $data );
    }

    public function testCut()
    {

        $data = array( 'bar', 'foo' );
        $class = eden( 'type', $data )->cut( 1 );
        $this->assertInstanceOf( 'Eden\\Type\\ArrayType', $class );
        $this->assertNotContains( 'foo', $class->get() );
    }

    public function testEach()
    {

        $data = array( 'bar', 'foo' );
        $test = $this;
        $class = eden( 'type', $data )->each(
            function ( $key, $value ) use ( $test, $data ) {

                $test->assertEquals( $data[$key], $value );
            }
        );
        $this->assertInstanceOf( 'Eden\\Type\\ArrayType', $class );
    }

    public function testIsEmpty()
    {

        $data = array();
        $result = eden( 'type', $data )->isEmpty();
        $this->assertTrue( $result );
    }

    public function testPaste()
    {

        $data = array( 'bar', 'foo' );
        $class = eden( 'type', $data );
        $this->assertInstanceOf( 'Eden\\Type\\ArrayType', $class->paste( 0, 'box', 'boo' ) );
        $this->assertArrayHasKey( 'boo', $class->get() );
    }

    public function testSerialize()
    {

        $data = array( 'foo', 'bar' );
        $class = eden( 'type', $data );
        $this->assertJsonStringEqualsJsonString( json_encode( $class->get() ), $class->serialize() );
    }

    public function testSet()
    {

        $data = array( 'foo', 'bar' );
        $somedata = array( 'box', 'boo' );
        $class = eden( 'type', $data );
        $this->assertInstanceOf( 'Eden\\Type\\ArrayType', $class->set( $somedata ) );
    }

    public function testUnSerialize()
    {

        $somedata = array( 'a' => 'foo', 'b' => 'bar' );
        $serialized = '{"a":"foo","b":"bar"}';
        $class = eden( 'type', $somedata )->unserialize( $serialized );
        $this->assertInstanceOf( 'Eden\\Type\\ArrayType', $class );
        $this->assertJsonStringEqualsJsonString( json_encode( $somedata ), (string)$class );
    }

    public function testArrayAccess()
    {

        $data = eden( 'type', array( 'name' => 'John', 'age' => 31 ) );

        $this->assertFalse( isset( $data[2] ) );
        $this->assertEquals( 31, $data['age'] );
    }

    public function testIterable()
    {

        $data = array( 'foo', 'bar' );
        $class = eden( 'type', $data );

        foreach ($class as $key => $value) {
            $this->assertEquals( $class->current(), $value );
        }
    }

}
