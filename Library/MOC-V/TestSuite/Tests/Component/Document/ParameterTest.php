<?php
namespace MOC\V\TestSuite\Tests\Component\Document;

use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperOrientationParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperSizeParameter;

/**
 * Class ParameterTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Document
 */
class ParameterTest extends \PHPUnit_Framework_TestCase
{

    public function testAbstractParameter()
    {

        /** @var \MOC\V\Component\Document\Component\Parameter\Parameter $MockParameter */
        $MockParameter = $this->getMockForAbstractClass( 'MOC\V\Component\Document\Component\Parameter\Parameter' );

        $Parameter = new $MockParameter();
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\Parameter\Parameter', $Parameter );

    }

    public function testFileParameter()
    {

        try {
            new FileParameter( null );
        } catch( \Exception $E ) {
            $this->assertInstanceOf( 'MOC\V\Component\Document\Component\Exception\Repository\EmptyFileException', $E );
        }

        $Parameter = new FileParameter( __FILE__ );
        $this->assertEquals( __FILE__, $Parameter->getFile() );

        try {
            $Parameter->setFile( __DIR__ );
        } catch( \Exception $E ) {
            $this->assertInstanceOf( 'MOC\V\Component\Document\Component\Exception\Repository\TypeFileException', $E );
        }

    }

    public function testPaperOrientationParameter()
    {

        try {
            new PaperOrientationParameter( null );
        } catch( \Exception $E ) {
            $this->assertInstanceOf( 'MOC\V\Component\Document\Component\Exception\ComponentException', $E );
        }

        $Parameter = new PaperOrientationParameter();
        $this->assertEquals( 'PORTRAIT', $Parameter->getOrientation() );
    }

    public function testPaperSizeParameter()
    {

        try {
            new PaperSizeParameter( null );
        } catch( \Exception $E ) {
            $this->assertInstanceOf( 'MOC\V\Component\Document\Component\Exception\ComponentException', $E );
        }

        $Parameter = new PaperSizeParameter();
        $this->assertEquals( 'A4', $Parameter->getSize() );
    }
}
