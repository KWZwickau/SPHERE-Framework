<?php
namespace MOC\V\TestSuite\Tests\Core\AutoLoader;

use MOC\V\Core\AutoLoader\Component\Parameter\Repository\DirectoryParameter;
use MOC\V\Core\AutoLoader\Component\Parameter\Repository\NamespaceParameter;

class ParameterTest extends \PHPUnit_Framework_TestCase
{

    public function testAbstractParameter()
    {

        /** @var \MOC\V\Core\AutoLoader\Component\Parameter\Parameter $MockParameter */
        $MockParameter = $this->getMockForAbstractClass( 'MOC\V\Core\AutoLoader\Component\Parameter\Parameter' );

        $Parameter = new $MockParameter();
        $this->assertInstanceOf( 'MOC\V\Core\AutoLoader\Component\Parameter\Parameter', $Parameter );

    }

    public function testNamespaceParameter()
    {

        try {
            new NamespaceParameter( '' );
        } catch( \Exception $E ) {
            $this->assertInstanceOf( 'MOC\V\Core\AutoLoader\Component\Exception\Repository\EmptyNamespaceException',
                $E );
        }

        $Parameter = new NamespaceParameter( null );
        $this->assertEquals( null, $Parameter->getNamespace() );

        $Parameter = new NamespaceParameter( __NAMESPACE__ );
        $this->assertEquals( __NAMESPACE__, $Parameter->getNamespace() );

        $Parameter->setNamespace( 'MOC\V\TestSuite' );
        $this->assertEquals( 'MOC\V\TestSuite', $Parameter->getNamespace() );

    }

    public function testDirectoryParameter()
    {

        try {
            new DirectoryParameter( null );
        } catch( \Exception $E ) {
            $this->assertInstanceOf( 'MOC\V\Core\AutoLoader\Component\Exception\Repository\EmptyDirectoryException',
                $E );
        }

        $Parameter = new DirectoryParameter( __DIR__ );
        $this->assertEquals( __DIR__, $Parameter->getDirectory() );
        try {
            $Parameter->setDirectory( 'MOC\V\TestSuite' );
        } catch( \Exception $E ) {
            $this->assertInstanceOf( 'MOC\V\Core\AutoLoader\Component\Exception\Repository\DirectoryNotFoundException',
                $E );
        }

    }

}
