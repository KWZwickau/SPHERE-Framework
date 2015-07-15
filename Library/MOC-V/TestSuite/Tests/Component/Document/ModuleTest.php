<?php
namespace MOC\V\TestSuite\Tests\Component\Document;

use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Vendor\Vendor;

/**
 * Class ModuleTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Document
 */
class ModuleTest extends \PHPUnit_Framework_TestCase
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

        $Template = new BridgeTest();
        $Template->tearDown();
    }

    public function testModule()
    {

        /** @var \MOC\V\Component\Document\Component\Bridge\Bridge $MockBridge */
        $MockBridge = $this->getMockBuilder( 'MOC\V\Component\Document\Component\Bridge\Bridge' )->getMock();
        $Vendor = new Vendor( new $MockBridge );
        $Module = new Document( $Vendor );

        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IVendorInterface',
            $Module->getVendorInterface()
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IVendorInterface',
            $Module->setBridgeInterface( $MockBridge )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Module->getBridgeInterface()
        );

    }

    public function testStaticExcelDocument()
    {

        $Document = Document::getExcelDocument( __DIR__.'/Content/test.xls' );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface', $Document );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Document->saveFile()
        );
        $Document = Document::getExcelDocument( __DIR__.'/Content/test.xls' );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface', $Document );
        $Document = Document::getExcelDocument( __DIR__.'/Content/test.xlsx' );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface', $Document );
        $Document = Document::getExcelDocument( __DIR__.'/Content/test.csv' );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface', $Document );
    }

    public function testStaticPdfDocument()
    {

        $Document = Document::getPdfDocument( __FILE__ );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface', $Document );

        $Document = Document::getPdfCreator( __FILE__ );
        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface', $Document );
    }

    public function testStaticDocument()
    {

        try {
            Document::getDocument( __FILE__ );
        } catch( \Exception $E ) {
            $this->assertInstanceOf( 'MOC\V\Component\Document\Exception\DocumentTypeException', $E );
        }
        try {
            Document::getDocument( 'Missing.pdf' );
        } catch( \Exception $E ) {

        }
        try {
            Document::getDocument( 'Missing.xls' );
        } catch( \Exception $E ) {

        }
        try {
            Document::getDocument( 'Missing.xlsx' );
        } catch( \Exception $E ) {

        }
        try {
            Document::getDocument( 'Missing.csv' );
        } catch( \Exception $E ) {

        }
    }
}
