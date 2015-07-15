<?php
namespace MOC\V\TestSuite\Tests\Component\Document;

use MOC\V\Component\Document\Component\Bridge\Repository\DomPdf;
use MOC\V\Component\Document\Component\Bridge\Repository\MPdf;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperOrientationParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperSizeParameter;
use MOC\V\Component\Template\Template;

/**
 * Class BridgeTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Document
 */
class BridgeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @codeCoverageIgnore
     */
    public function tearDown()
    {

        if (false !== ( $Path = realpath( __DIR__.'/Content' ) )) {
            $Iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator( $Path, \RecursiveDirectoryIterator::SKIP_DOTS ),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            /** @var \SplFileInfo $FileInfo */
            foreach ($Iterator as $FileInfo) {
                if (
                    $FileInfo->getBasename() != 'README.md'
                    && $FileInfo->getBasename() != 'BridgeTest.tpl'
                    && $FileInfo->getBasename() != 'BridgeTest.twig'
                ) {
                    if ($FileInfo->isFile()) {
                        unlink( $FileInfo->getPathname() );
                    }
                    if ($FileInfo->isDir()) {
                        rmdir( $FileInfo->getPathname() );
                    }
                }
            }
        }

        $Template = new \MOC\V\TestSuite\Tests\Component\Template\BridgeTest();
        $Template->tearDown();
    }

    public function testPhpExcelDocument()
    {

        $Bridge = new PhpExcel();

        try {
            $Bridge->loadFile( new FileParameter( __FILE__ ) );
        } catch( \Exception $Exception ) {
            $this->assertInstanceOf( 'MOC\V\Component\Document\Component\Exception\Repository\TypeFileException',
                $Exception );
        }
        $Bridge->newFile( new FileParameter( __DIR__.'/Content/BridgeTest-Excel.xlsx' ) );

        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setPaperSizeParameter( new PaperSizeParameter( 'A4' ) )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setPaperOrientationParameter( new PaperOrientationParameter( 'PORTRAIT' ) )
        );

        /**
         * Cell Index
         */

        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel\Cell',
            $Cell = $Bridge->getCell( 'A1' )
        );
        $this->assertEquals( 0, $Cell->getColumn() );
        $this->assertEquals( 1, $Cell->getRow() );

        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel\Cell',
            $Cell = $Bridge->getCell( 'B2' )
        );
        $this->assertEquals( 1, $Cell->getColumn() );
        $this->assertEquals( 2, $Cell->getRow() );

        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel\Cell',
            $Cell = $Bridge->getCell( 0, 0 )
        );
        $this->assertEquals( 0, $Cell->getColumn() );
        $this->assertEquals( 1, $Cell->getRow() );

        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel\Cell',
            $Cell = $Bridge->getCell( 1, 1 )
        );
        $this->assertEquals( 1, $Cell->getColumn() );
        $this->assertEquals( 2, $Cell->getRow() );

        /**
         * Cell Value
         */

        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setValue( $Cell, '01' )
        );
        $this->assertEquals( '01', $Bridge->getValue( $Cell ) );

        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setValue( $Cell, 'öäüß' )
        );
        $this->assertEquals( 'öäüß', $Bridge->getValue( $Cell ) );

        /**
         * Save File
         */

        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->saveFile()
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->loadFile( new FileParameter( __DIR__.'/Content/BridgeTest-Excel.xlsx' ) )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->saveFile( new FileParameter( __DIR__.'/Content/BridgeTest-Excel-As.csv' ) )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->loadFile( new FileParameter( __DIR__.'/Content/BridgeTest-Excel-As.csv' ) )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->saveFile( new FileParameter( __DIR__.'/Content/BridgeTest-Excel-As.xls' ) )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->loadFile( new FileParameter( __DIR__.'/Content/BridgeTest-Excel-As.xls' ) )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->saveFile( new FileParameter( __DIR__.'/Content/BridgeTest-Excel-As.xlsx' ) )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->loadFile( new FileParameter( __DIR__.'/Content/BridgeTest-Excel-As.xlsx' ) )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->saveFile( new FileParameter( __DIR__.'/Content/BridgeTest-Excel-As.html' ) )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->loadFile( new FileParameter( __DIR__.'/Content/BridgeTest-Excel-As.html' ) )
        );

    }

    public function testDomPdfDocument()
    {

        $Bridge = new DomPdf();

        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->loadFile( new FileParameter( __DIR__.'/Content/BridgeTest-Twig.pdf' ) )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setPaperSizeParameter( new PaperSizeParameter( 'A4' ) )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setPaperOrientationParameter( new PaperOrientationParameter( 'PORTRAIT' ) )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setContent( Template::getTemplate( __DIR__.'/Content/BridgeTest.twig' ) )
        );

        $this->assertStringStartsWith( '%PDF-', $Bridge->getContent() );

        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->saveFile()
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->saveFile( new FileParameter( __DIR__.'/Content/BridgeTest-Twig-As.pdf' ) )
        );

        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->loadFile( new FileParameter( __DIR__.'/Content/BridgeTest-Tpl.pdf' ) )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setPaperSizeParameter( new PaperSizeParameter( 'A4' ) )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setPaperOrientationParameter( new PaperOrientationParameter( 'PORTRAIT' ) )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setContent( Template::getTemplate( __DIR__.'/Content/BridgeTest.tpl' ) )
        );

        $this->assertStringStartsWith( '%PDF-', $Bridge->getContent() );

        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->saveFile()
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->saveFile( new FileParameter( __DIR__.'/Content/BridgeTest-Tpl-As.pdf' ) )
        );
    }

    public function testMPdfDocument()
    {

        $Bridge = new MPdf();

        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->loadFile( new FileParameter( __DIR__.'/Content/BridgeTest-Twig.pdf' ) )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setPaperSizeParameter( new PaperSizeParameter( 'A4' ) )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setPaperOrientationParameter( new PaperOrientationParameter( 'PORTRAIT' ) )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setContent( Template::getTwigTemplate( __DIR__.'/Content/BridgeTest.twig' ) )
        );
//
//        $this->assertStringStartsWith( '%PDF-', $Bridge->getContent() );
//
//        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
//            $Bridge->saveFile()
//        );
//        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
//            $Bridge->saveFile( new FileParameter( __DIR__.'/Content/BridgeTest-Twig-As.pdf' ) )
//        );
//
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->loadFile( new FileParameter( __DIR__.'/Content/BridgeTest-Tpl.pdf' ) )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setPaperSizeParameter( new PaperSizeParameter( 'A4' ) )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setPaperOrientationParameter( new PaperOrientationParameter( 'PORTRAIT' ) )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setContent( Template::getSmartyTemplate( __DIR__.'/Content/BridgeTest.tpl' ) )
        );
//
//        $this->assertStringStartsWith( '%PDF-', $Bridge->getContent() );
//
//        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
//            $Bridge->saveFile()
//        );
//        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
//            $Bridge->saveFile( new FileParameter( __DIR__.'/Content/BridgeTest-Tpl-As.pdf' ) )
//        );
    }
}
