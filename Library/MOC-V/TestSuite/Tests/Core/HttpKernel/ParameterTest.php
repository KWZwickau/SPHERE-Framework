<?php
namespace MOC\V\TestSuite\Tests\Core\HttpKernel;

/**
 * Class ParameterTest
 *
 * @package MOC\V\TestSuite\Tests\Core\HttpKernel
 */
class ParameterTest extends \PHPUnit_Framework_TestCase
{

    public function testAbstractParameter()
    {

        /** @var \MOC\V\Core\HttpKernel\Component\Parameter\Parameter $MockParameter */
        $MockParameter = $this->getMockForAbstractClass( 'MOC\V\Core\HttpKernel\Component\Parameter\Parameter' );

        $Parameter = new $MockParameter();
        $this->assertInstanceOf( 'MOC\V\Core\HttpKernel\Component\Parameter\Parameter', $Parameter );

    }
}
