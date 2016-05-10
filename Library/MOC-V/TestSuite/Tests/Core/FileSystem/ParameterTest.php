<?php
namespace MOC\V\TestSuite\Tests\Core\FileSystem;

use MOC\V\Core\FileSystem\Component\Parameter\Repository\DirectoryParameter;
use MOC\V\Core\FileSystem\Component\Parameter\Repository\FileParameter;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class ParameterTest
 *
 * @package MOC\V\TestSuite\Tests\Core\FileSystem
 */
class ParameterTest extends AbstractTestCase
{

    public function testAbstractParameter()
    {

        /** @var \MOC\V\Core\FileSystem\Component\Parameter\Parameter $MockParameter */
        $MockParameter = $this->getMockForAbstractClass('MOC\V\Core\FileSystem\Component\Parameter\Parameter');

        $Parameter = new $MockParameter();
        $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\Parameter\Parameter', $Parameter);

    }

    public function testFileParameter()
    {

        try {
            new FileParameter(null);
        } catch (\Exception $E) {
            $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\Exception\Repository\EmptyFileException', $E);
        }

        $Parameter = new FileParameter(__FILE__);
        $this->assertEquals(__FILE__, $Parameter->getFile());

        try {
            $Parameter->setFile(__DIR__);
        } catch (\Exception $E) {
            $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\Exception\Repository\TypeFileException', $E);
        }

    }

    public function testDirectoryParameter()
    {

        try {
            new DirectoryParameter(null);
        } catch (\Exception $E) {
            $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\Exception\Repository\EmptyDirectoryException',
                $E);
        }

        $Parameter = new DirectoryParameter(__DIR__);
        $this->assertEquals(__DIR__, $Parameter->getDirectory());

        try {
            $Parameter->setDirectory(__FILE__);
        } catch (\Exception $E) {
            $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\Exception\Repository\TypeDirectoryException',
                $E);
        }

    }

}
