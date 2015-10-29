<?php
namespace MOC\V\TestSuite\Tests\Component\Documentation;

use MOC\V\Component\Documentation\Component\Parameter\Repository\DirectoryParameter;
use MOC\V\Component\Documentation\Documentation;
use MOC\V\Component\Documentation\Vendor\Vendor;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class ModuleTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Documentation
 */
class ModuleTest extends AbstractTestCase
{

    /**
     * @codeCoverageIgnore
     */
    public function tearDown()
    {

        if (false !== ( $Path = realpath(__DIR__.'/Content') )) {
            $Iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($Path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            /** @var \SplFileInfo $FileInfo */
            foreach ($Iterator as $FileInfo) {
                if ($FileInfo->getBasename() != 'README.md') {
                    if ($FileInfo->isFile()) {
                        unlink($FileInfo->getPathname());
                    }
                    if ($FileInfo->isDir()) {
                        rmdir($FileInfo->getPathname());
                    }
                }
            }
        }
    }

    public function testModule()
    {

        /** @var \MOC\V\Component\Documentation\Component\Bridge\Bridge $MockBridge */
        $MockBridge = $this->getMockBuilder('MOC\V\Component\Documentation\Component\Bridge\Bridge')->getMock();
        $Vendor = new Vendor(new $MockBridge);
        $Module = new Documentation($Vendor);

        $this->assertInstanceOf('MOC\V\Component\Documentation\Component\IVendorInterface',
            $Module->getVendorInterface()
        );
        $this->assertInstanceOf('MOC\V\Component\Documentation\Component\IVendorInterface',
            $Module->setBridgeInterface($MockBridge)
        );
        $this->assertInstanceOf('MOC\V\Component\Documentation\Component\IBridgeInterface',
            $Module->getBridgeInterface()
        );
    }

    public function testStaticApiGenDocumentation()
    {

        $Documentation = Documentation::getApiGenDocumentation('MOC', 'Test',
            new DirectoryParameter(__DIR__),
            new DirectoryParameter(__DIR__.'/Content/')
        );
        $this->assertInstanceOf('MOC\V\Component\Documentation\Component\IBridgeInterface', $Documentation);
    }

    public function testStaticDocumentation()
    {

        $Documentation = Documentation::getDocumentation('MOC', 'Test',
            new DirectoryParameter(__DIR__),
            new DirectoryParameter(__DIR__.'/Content/')
        );
        $this->assertInstanceOf('MOC\V\Component\Documentation\Component\IBridgeInterface', $Documentation);
    }
}
