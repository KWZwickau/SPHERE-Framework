<?php
namespace MOC\V\TestSuite\Tests\Core\GlobalsKernel;

use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class ParameterTest
 *
 * @package MOC\V\TestSuite\Tests\Core\GlobalsKernel
 */
class ParameterTest extends AbstractTestCase
{

    public function testAbstractParameter()
    {

        /** @var \MOC\V\Core\GlobalsKernel\Component\Parameter\Parameter $MockParameter */
        $MockParameter = $this->getMockForAbstractClass('MOC\V\Core\GlobalsKernel\Component\Parameter\Parameter');

        $Parameter = new $MockParameter();
        $this->assertInstanceOf('MOC\V\Core\GlobalsKernel\Component\Parameter\Parameter', $Parameter);

    }
}
