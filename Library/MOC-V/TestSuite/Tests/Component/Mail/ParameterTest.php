<?php
namespace MOC\V\TestSuite\Tests\Component\Mail;

use MOC\V\Component\Mail\Component\Parameter\Repository\FileParameter;

/**
 * Class ParameterTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Mail
 */
class ParameterTest extends \PHPUnit_Framework_TestCase
{

    public function testAbstractParameter()
    {

        /** @var \MOC\V\Component\Mail\Component\Parameter\Parameter $MockParameter */
        $MockParameter = $this->getMockForAbstractClass( 'MOC\V\Component\Mail\Component\Parameter\Parameter' );

        $Parameter = new $MockParameter();
        $this->assertInstanceOf( 'MOC\V\Component\Mail\Component\Parameter\Parameter', $Parameter );

    }

    public function testFileParameter()
    {

        try {
            new FileParameter( null );
        } catch( \Exception $E ) {
            $this->assertInstanceOf( 'MOC\V\Component\Mail\Component\Exception\Repository\EmptyFileException', $E );
        }

        $Parameter = new FileParameter( __FILE__ );
        $this->assertEquals( __FILE__, $Parameter->getFile() );
        $this->assertInstanceOf( '\SplFileInfo', $Parameter->getFileInfo() );
        $this->assertEquals( __FILE__, $Parameter->__toString() );

        try {
            $Parameter->setFile( __DIR__ );
        } catch( \Exception $E ) {
            $this->assertInstanceOf( 'MOC\V\Component\Mail\Component\Exception\Repository\TypeFileException', $E );
        }

    }
}
