<?php //-->
/*
 * This file is part of the Utility package of the Eden PHP Library.
 * (c) 2013-2014 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */

class Eden_System_Tests_System_PathTest extends \PHPUnit_Framework_TestCase
{

    public function testAbsolute()
    {

        try {
            eden( 'system' )->path( 'some/path/' )->absolute();
        } catch( Exception $e ) {
            $this->assertInstanceOf( 'Eden\\System\\Exception', $e );
        }

        $class = eden( 'system' )->path( __FILE__ )->absolute();
        $this->assertInstanceOf( 'Eden\\System\\Path', $class );
    }

    public function testAppend()
    {

        $path = eden( 'system' )->path( 'some/path/' )->append( 'foo' );
        $this->assertEquals( '/some/path/foo', (string)$path );
    }

    public function testGetArray()
    {

        $array = eden( 'system' )->path( 'some/path/' )->getArray();
        $this->assertTrue( in_array( 'some', $array ) );
        $this->assertTrue( in_array( 'path', $array ) );
    }

    public function testPrepend()
    {

        $path = eden( 'system' )->path( 'some/path/' )->prepend( 'foo' );
        $this->assertEquals( '/foo/some/path', (string)$path );
    }

    public function testPop()
    {

        $this->assertEquals( 'path', eden( 'system' )->path( 'some/path/' )->pop() );
    }

    public function testReplace()
    {

        $path = eden( 'system' )->path( 'some/path/' )->replace( 'foo' );
        $this->assertEquals( '/some/foo', (string)$path );
    }

    public function testArrayAccess()
    {

        $path = eden( 'system' )->path( 'some/path/' );
        $this->assertEquals( 'some', $path[1] );
        $path['replace'] = 'foo';
        $this->assertEquals( 'foo', $path['last'] );
    }
}
