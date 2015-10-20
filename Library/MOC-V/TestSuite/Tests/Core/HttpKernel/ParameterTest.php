<?php
namespace MOC\V\TestSuite\Tests\Core\HttpKernel;

use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class ParameterTest
 *
 * @package MOC\V\TestSuite\Tests\Core\HttpKernel
 */
class ParameterTest extends AbstractTestCase
{

    public function testAbstractParameter()
    {

        /** @var \MOC\V\Core\HttpKernel\Component\Parameter\Parameter $MockParameter */
        $MockParameter = $this->getMockForAbstractClass('MOC\V\Core\HttpKernel\Component\Parameter\Parameter');

        $Parameter = new $MockParameter();
        $this->assertInstanceOf('MOC\V\Core\HttpKernel\Component\Parameter\Parameter', $Parameter);

    }
}
