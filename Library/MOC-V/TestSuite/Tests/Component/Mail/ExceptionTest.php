<?php
namespace MOC\V\TestSuite\Tests\Component\Mail;

use MOC\V\Component\Mail\Component\Exception\ComponentException;
use MOC\V\Component\Mail\Exception\MailException;

/**
 * Class ExceptionTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Mail
 */
class ExceptionTest extends \PHPUnit_Framework_TestCase
{

    public function testMailException()
    {

        try {
            throw new MailException();
        } catch( \Exception $E ) {
            $this->assertInstanceOf( '\MOC\V\Component\Mail\Exception\MailException', $E );
        }

        try {
            throw new ComponentException();
        } catch( \Exception $E ) {
            $this->assertInstanceOf( '\MOC\V\Component\Mail\Component\Exception\ComponentException', $E );
        }
    }
}
