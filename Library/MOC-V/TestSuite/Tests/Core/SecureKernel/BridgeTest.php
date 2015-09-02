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
     * @
     * @throws \MOC\V\Core\SecureKernel\Component\Exception\ComponentException
     */
    public function testSFTP()
    {

        $Bridge = new SFTP();

        $Bridge->openConnection('host', 22);
        $Bridge->loginCredentialKey('user', __DIR__.'/sftp-ssh2-rsa-4096-private.ppk', 'password');
        $Bridge->changeDirectory('.');
        $Bridge->closeConnection();
    }

    protected function setUp()
    {

        $this->markTestSkipped(
            'SFTP Server required'
        );
    }

}
