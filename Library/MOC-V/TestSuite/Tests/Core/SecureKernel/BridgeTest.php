<?php
namespace MOC\V\TestSuite\Tests\Core\SecureKernel;

use MOC\V\Core\SecureKernel\Component\Bridge\Repository\SFTP;

/**
 * Class BridgeTest
 *
 * @package MOC\V\TestSuite\Tests\Core\SecureKernel
 */
class BridgeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @throws \MOC\V\Core\SecureKernel\Component\Exception\ComponentException
     */
    public function testSFTP()
    {

        $Bridge = new SFTP();

        $Bridge->openConnection('host', 22);
        try {
            $Bridge->loginCredentialKey('user', __FILE__, 'password');
        } catch (\Exception $Exception) {
            $this->assertInstanceOf('\MOC\V\Core\SecureKernel\Component\Exception\ComponentException', $Exception);
        }
        try {
            $Bridge->changeDirectory('.');
        } catch (\Exception $Exception) {
            $this->assertInstanceOf('\MOC\V\Core\SecureKernel\Component\Exception\ComponentException', $Exception);
        }
        $Bridge->closeConnection();
    }
}
