<?php
namespace MOC\V\TestSuite\Tests\Core\SecureKernel;

use MOC\V\Core\SecureKernel\Component\Exception\ComponentException;
use MOC\V\Core\SecureKernel\Exception\SecureKernelException;

/**
 * Class ExceptionTest
 *
 * @package MOC\V\TestSuite\Tests\Core\SecureKernel
 */
class ExceptionTest extends \PHPUnit_Framework_TestCase
{

    public function testSecureKernelException()
    {

        try {
            throw new SecureKernelException();
        } catch (\Exception $E) {
            $this->assertInstanceOf('\MOC\V\Core\SecureKernel\Exception\SecureKernelException', $E);
        }

        try {
            throw new ComponentException();
        } catch (\Exception $E) {
            $this->assertInstanceOf('\MOC\V\Core\SecureKernel\Component\Exception\ComponentException', $E);
        }

    }

}
