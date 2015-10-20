<?php
namespace MOC\V\TestSuite\Tests\Component\Router;

use MOC\V\Component\Router\Component\Exception\ComponentException;
use MOC\V\Component\Router\Component\Exception\Repository\MissingParameterException;
use MOC\V\Component\Router\Exception\RouterException;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class ExceptionTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Router
 */
class ExceptionTest extends AbstractTestCase
{

    public function testRouterException()
    {

        try {
            throw new RouterException();
        } catch (\Exception $E) {
            $this->assertInstanceOf('\MOC\V\Component\Router\Exception\RouterException', $E);
        }

        try {
            throw new ComponentException();
        } catch (\Exception $E) {
            $this->assertInstanceOf('\MOC\V\Component\Router\Component\Exception\ComponentException', $E);
        }

        try {
            throw new MissingParameterException();
        } catch (\Exception $E) {
            $this->assertInstanceOf('\MOC\V\Component\Router\Component\Exception\Repository\MissingParameterException',
                $E);
        }
    }

}
