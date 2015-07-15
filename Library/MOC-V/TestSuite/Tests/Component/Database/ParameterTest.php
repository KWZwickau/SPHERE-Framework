<?php
namespace MOC\V\TestSuite\Tests\Component\Database;

/**
 * Class ParameterTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Database
 */
class ParameterTest extends \PHPUnit_Framework_TestCase
{

    public function testAbstractParameter()
    {

        /** @var \MOC\V\Component\Database\Component\Parameter\Parameter $MockParameter */
        $MockParameter = $this->getMockForAbstractClass( 'MOC\V\Component\Database\Component\Parameter\Parameter' );

        $Parameter = new $MockParameter();
        $this->assertInstanceOf( 'MOC\V\Component\Database\Component\Parameter\Parameter', $Parameter );

    }

}
