<?php
namespace MOC\V\TestSuite\Tests\Core\GlobalsKernel;

use MOC\V\Core\GlobalsKernel\Component\Bridge\Repository\UniversalGlobals;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class BridgeTest
 *
 * @package MOC\V\TestSuite\Tests\Core\GlobalsKernel
 */
class BridgeTest extends AbstractTestCase
{

    public function testUniversalGlobals()
    {

        $Bridge = new UniversalGlobals();

        $this->assertInternalType('array', $GET = $Bridge->getGET());
        $Bridge->setGET($GET);
        $this->assertInternalType('array', $POST = $Bridge->getPOST());
        $Bridge->setPOST($POST);
        $this->assertInternalType('array', $SESSION = $Bridge->getSESSION());
        $Bridge->setSESSION($SESSION);
        $this->assertInternalType('array', $SERVER = $Bridge->getSERVER());
        $Bridge->setSERVER($SERVER);
    }
}
