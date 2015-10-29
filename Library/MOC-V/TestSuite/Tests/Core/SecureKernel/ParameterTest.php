<?php
namespace MOC\V\TestSuite\Tests\Core\SecureKernel;

use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class ParameterTest
 *
 * @package MOC\V\TestSuite\Tests\Core\SecureKernel
 */
class ParameterTest extends AbstractTestCase
{

    public function testAbstractParameter()
    {

        /** @var \MOC\V\Core\SecureKernel\Component\Parameter\Parameter $MockParameter */
        $MockParameter = $this->getMockForAbstractClass('MOC\V\Core\SecureKernel\Component\Parameter\Parameter');

        $Parameter = new $MockParameter();
        $this->assertInstanceOf('MOC\V\Core\SecureKernel\Component\Parameter\Parameter', $Parameter);

    }
}
