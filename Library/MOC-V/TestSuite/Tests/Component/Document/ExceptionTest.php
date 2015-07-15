<?php
namespace MOC\V\TestSuite\Tests\Component\Document;

use MOC\V\Component\Document\Component\Exception\ComponentException;
use MOC\V\Component\Document\Exception\DocumentException;

/**
 * Class ExceptionTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Document
 */
class ExceptionTest extends \PHPUnit_Framework_TestCase
{

    public function testDocumentException()
    {

        try {
            throw new DocumentException();
        } catch( \Exception $E ) {
            $this->assertInstanceOf( '\MOC\V\Component\Document\Exception\DocumentException', $E );
        }

        try {
            throw new ComponentException();
        } catch( \Exception $E ) {
            $this->assertInstanceOf( '\MOC\V\Component\Document\Component\Exception\ComponentException', $E );
        }
    }
}
