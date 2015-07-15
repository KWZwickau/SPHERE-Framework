<?php
namespace MOC\V\TestSuite\Tests\Component\Router;

use MOC\V\Component\Router\Component\Parameter\Repository\RouteParameter;

/**
 * Class ParameterTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Router
 */
class ParameterTest extends \PHPUnit_Framework_TestCase
{

    public function testAbstractParameter()
    {

        /** @var \MOC\V\Component\Router\Component\Parameter\Parameter $MockParameter */
        $MockParameter = $this->getMockForAbstractClass( 'MOC\V\Component\Router\Component\Parameter\Parameter' );

        $Parameter = new $MockParameter();
        $this->assertInstanceOf( 'MOC\V\Component\Router\Component\Parameter\Parameter', $Parameter );

    }

    public function testRouteParameter()
    {

        $Route = new RouteParameter( '/', 'NoClass::NoMethod' );

        $this->assertInternalType( 'string', $Route->getController() );
        $Route->setParameterDefault( 'Name', 'Value' );
        $this->assertInternalType( 'array', $Route->getParameterDefault() );
        $this->assertInternalType( 'string', $Route->getParameterDefault( 'Name' ) );
        $Route->setParameterPattern( 'Name', 'Pattern' );
        $this->assertInternalType( 'array', $Route->getParameterPattern() );
        $this->assertInternalType( 'string', $Route->getPath() );

        try {
            new RouteParameter( '/', 'WrongFormat:WithController' );
        } catch( \Exception $E ) {
            $this->assertInstanceOf( 'MOC\V\Component\Router\Component\Exception\ComponentException', $E );
        }
    }
}
