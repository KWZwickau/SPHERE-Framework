<?php
namespace MOC\V\TestSuite\Tests\Component\Documentation;

use MOC\V\Component\Documentation\Component\Exception\ComponentException;
use MOC\V\Component\Documentation\Component\Exception\Repository\EmptyDirectoryException;
use MOC\V\Component\Documentation\Component\Exception\Repository\TypeDirectoryException;
use MOC\V\Component\Documentation\Exception\DocumentationException;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class ExceptionTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Documentation
 */
class ExceptionTest extends AbstractTestCase
{

    public function testDocumentationException()
    {

        try {
            throw new DocumentationException();
        } catch (\Exception $E) {
            $this->assertInstanceOf('\MOC\V\Component\Documentation\Exception\DocumentationException', $E);
        }

        try {
            throw new ComponentException();
        } catch (\Exception $E) {
            $this->assertInstanceOf('\MOC\V\Component\Documentation\Component\Exception\ComponentException', $E);
        }

        try {
            throw new EmptyDirectoryException();
        } catch (\Exception $E) {
            $this->assertInstanceOf('\MOC\V\Component\Documentation\Component\Exception\Repository\EmptyDirectoryException',
                $E);
        }

        try {
            throw new TypeDirectoryException();
        } catch (\Exception $E) {
            $this->assertInstanceOf('\MOC\V\Component\Documentation\Component\Exception\Repository\TypeDirectoryException',
                $E);
        }
    }

}
