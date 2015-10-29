<?php
namespace MOC\V\TestSuite\Tests\Component\Database;

use MOC\V\Component\Database\Component\Exception\ComponentException;
use MOC\V\Component\Database\Component\Exception\Repository\NoConnectionException;
use MOC\V\Component\Database\Exception\DatabaseException;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class ExceptionTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Database
 */
class ExceptionTest extends AbstractTestCase
{

    public function testDatabaseException()
    {

        try {
            throw new DatabaseException();
        } catch (\Exception $E) {
            $this->assertInstanceOf('\MOC\V\Component\Database\Exception\DatabaseException', $E);
        }

        try {
            throw new ComponentException();
        } catch (\Exception $E) {
            $this->assertInstanceOf('\MOC\V\Component\Database\Component\Exception\ComponentException', $E);
        }

        try {
            throw new NoConnectionException();
        } catch (\Exception $E) {
            $this->assertInstanceOf('\MOC\V\Component\Database\Component\Exception\Repository\NoConnectionException',
                $E);
        }

    }

}
