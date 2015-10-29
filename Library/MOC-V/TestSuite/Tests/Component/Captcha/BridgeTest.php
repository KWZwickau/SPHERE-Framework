<?php
namespace MOC\V\TestSuite\Tests\Component\Captcha;

use MOC\V\Component\Captcha\Component\Bridge\Repository\SimplePhpCaptcha;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class BridgeTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Captcha
 */
class BridgeTest extends AbstractTestCase
{

    public function testSimplePhpCaptcha()
    {

        $Bridge = new SimplePhpCaptcha();
        $this->assertInstanceOf('\MOC\V\Component\Captcha\Component\Bridge\Repository\SimplePhpCaptcha',
            $Captcha = $Bridge->createCaptcha()
        );
        $Code = $Captcha->getCode();
        $this->assertInternalType('string', $Code);
        $Image = $Captcha->getCaptcha();
        $this->assertInternalType('string', $Image);
        $this->assertEquals(false, $Captcha->verifyCaptcha(''));
        $this->assertEquals(true, $Captcha->verifyCaptcha($Code));
    }

    /**
     * @codeCoverageIgnore
     */
    protected function setUp()
    {

        if (!function_exists('gd_info')) {
            $this->markTestSkipped(
                'GD Library required'
            );
        }
    }
}
