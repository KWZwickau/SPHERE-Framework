<?php
namespace MOC\V\TestSuite\Tests\Component\Mail;

use MOC\V\Component\Mail\Component\Exception\ComponentException;
use MOC\V\Component\Mail\Exception\MailException;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class ExceptionTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Mail
 */
class ExceptionTest extends AbstractTestCase
{

    public function testMailException()
    {

        try {
            throw new MailException();
        } catch (\Exception $E) {
            $this->assertInstanceOf('\MOC\V\Component\Mail\Exception\MailException', $E);
        }

        try {
            throw new ComponentException();
        } catch (\Exception $E) {
            $this->assertInstanceOf('\MOC\V\Component\Mail\Component\Exception\ComponentException', $E);
        }
    }
}
