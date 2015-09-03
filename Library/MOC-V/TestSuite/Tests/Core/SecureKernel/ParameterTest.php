<?php
namespace MOC\V\TestSuite\Tests\Core\SecureKernel;

/**
 * Class ParameterTest
 *
 * @package MOC\V\TestSuite\Tests\Core\SecureKernel
 */
class ParameterTest extends \PHPUnit_Framework_TestCase
{

    public function testAbstractParameter()
    {

        /** @var \MOC\V\Core\SecureKernel\Component\Parameter\Parameter $MockParameter */
        $MockParameter = $this->getMockForAbstractClass('MOC\V\Core\SecureKernel\Component\Parameter\Parameter');

        $Parameter = new $MockParameter();
        $this->assertInstanceOf('MOC\V\Core\SecureKernel\Component\Parameter\Parameter', $Parameter);

    }
}
