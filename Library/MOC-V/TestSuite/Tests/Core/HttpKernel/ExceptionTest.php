<?php
namespace MOC\V\TestSuite\Tests\Core\HttpKernel;

use MOC\V\Core\HttpKernel\Component\Exception\ComponentException;
use MOC\V\Core\HttpKernel\Exception\HttpKernelException;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class ExceptionTest
 *
 * @package MOC\V\TestSuite\Tests\Core\HttpKernel
 */
class ExceptionTest extends AbstractTestCase
{

    public function testHttpKernelException()
    {

        try {
            throw new HttpKernelException();
        } catch (\Exception $E) {
            $this->assertInstanceOf('\MOC\V\Core\HttpKernel\Exception\HttpKernelException', $E);
        }

        try {
            throw new ComponentException();
        } catch (\Exception $E) {
            $this->assertInstanceOf('\MOC\V\Core\HttpKernel\Component\Exception\ComponentException', $E);
        }

    }

}
