<?php
namespace MOC\V\TestSuite\Tests\Component\Captcha;

use MOC\V\Component\Captcha\Component\Bridge\Repository\SimplePhpCaptcha;

/**
 * Class BridgeTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Captcha
 */
class BridgeTest extends \PHPUnit_Framework_TestCase
{

    public function testSimplePhpCaptcha()
    {

        $Bridge = new SimplePhpCaptcha();
    }

}
