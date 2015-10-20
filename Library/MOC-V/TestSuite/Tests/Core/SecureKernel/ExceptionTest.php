<?php
namespace MOC\V\TestSuite\Tests\Core\SecureKernel;

use MOC\V\Core\SecureKernel\Component\Exception\ComponentException;
use MOC\V\Core\SecureKernel\Exception\SecureKernelException;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class ExceptionTest
 *
 * @package MOC\V\TestSuite\Tests\Core\SecureKernel
 */
class ExceptionTest extends AbstractTestCase
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
