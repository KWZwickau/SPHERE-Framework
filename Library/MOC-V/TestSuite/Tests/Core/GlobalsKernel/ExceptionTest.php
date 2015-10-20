<?php
namespace MOC\V\TestSuite\Tests\Core\GlobalsKernel;

use MOC\V\Core\GlobalsKernel\Component\Exception\ComponentException;
use MOC\V\Core\GlobalsKernel\Exception\GlobalsKernelException;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class ExceptionTest
 *
 * @package MOC\V\TestSuite\Tests\Core\GlobalsKernel
 */
class ExceptionTest extends AbstractTestCase
{

    public function testGlobalsKernelException()
    {

        try {
            throw new GlobalsKernelException();
        } catch (\Exception $E) {
            $this->assertInstanceOf('\MOC\V\Core\GlobalsKernel\Exception\GlobalsKernelException', $E);
        }

        try {
            throw new ComponentException();
        } catch (\Exception $E) {
            $this->assertInstanceOf('\MOC\V\Core\GlobalsKernel\Component\Exception\ComponentException', $E);
        }

    }

}
