<?php
namespace MOC\V\TestSuite\Tests\Core\HttpKernel;

use MOC\V\Core\HttpKernel\Component\Bridge\Repository\UniversalRequest;

/**
 * Class BridgeTest
 *
 * @package MOC\V\TestSuite\Tests\Core\HttpKernel
 */
class BridgeTest extends \PHPUnit_Framework_TestCase
{

    public function testUniversalRequest()
    {

        $Bridge = new UniversalRequest();

        $this->assertInternalType( 'string', $Bridge->getPathBase() );
        $this->assertInternalType( 'string', $Bridge->getPathInfo() );
        $this->assertInternalType( 'string', $Bridge->getUrlBase() );
        $this->assertInternalType( 'string', $Bridge->getPort() );

        $this->assertInternalType( 'array', $Bridge->getRequestGETArray() );
        $this->assertInternalType( 'array', $Bridge->getRequestPOSTArray() );
        $this->assertInternalType( 'array', $Bridge->getRequestCUSTOMArray() );
        $this->assertInternalType( 'array', $Bridge->getParameterArray() );
    }

}
