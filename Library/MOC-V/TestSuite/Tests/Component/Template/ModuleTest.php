<?php
namespace MOC\V\TestSuite\Tests\Component\Template;

use MOC\V\Component\Template\Template;
use MOC\V\Component\Template\Vendor\Vendor;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class ModuleTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Template
 */
class ModuleTest extends AbstractTestCase
{

    /**
     * @codeCoverageIgnore
     */
    public function tearDown()
    {

        if (false !== ( $Path = realpath(__DIR__.'/../../../../Component/Template/Component/Bridge/Repository/SmartyTemplate') )) {
            $Iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($Path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            /** @var \SplFileInfo $FileInfo */
            foreach ($Iterator as $FileInfo) {
                if ($FileInfo->getBasename() != 'README.md') {
                    unlink($FileInfo->getPathname());
                }
            }
        }

        if (false !== ( $Path = realpath(__DIR__.'/../../../../Component/Template/Component/Bridge/Repository/TwigTemplate') )) {
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

        /** @var \MOC\V\Component\Template\Component\Bridge\Bridge $MockBridge */
        $MockBridge = $this->getMockBuilder('MOC\V\Component\Template\Component\Bridge\Bridge')->getMock();
        $Vendor = new Vendor(new $MockBridge);
        $Module = new Template($Vendor);

        $this->assertInstanceOf('MOC\V\Component\Template\Component\IVendorInterface',
            $Module->getVendorInterface()
        );
        $this->assertInstanceOf('MOC\V\Component\Template\Component\IVendorInterface',
            $Module->setBridgeInterface($MockBridge)
        );
        $this->assertInstanceOf('MOC\V\Component\Template\Component\IBridgeInterface',
            $Module->getBridgeInterface()
        );

    }

    public function testStaticTwigTemplate()
    {

        $Template = Template::getTwigTemplate(__FILE__);
        $this->assertInstanceOf('MOC\V\Component\Template\Component\IBridgeInterface', $Template);
    }

    public function testStaticSmartyTemplate()
    {

        $Template = Template::getSmartyTemplate(__FILE__);
        $this->assertInstanceOf('MOC\V\Component\Template\Component\IBridgeInterface', $Template);
    }

    public function testStaticTemplate()
    {

        try {
            Template::getTemplate(__FILE__);
        } catch (\Exception $E) {
            $this->assertInstanceOf('MOC\V\Component\Template\Exception\TemplateTypeException', $E);
        }
        try {
            Template::getTemplate('Missing.twig');
        } catch (\Exception $E) {

        }
        try {
            Template::getTemplate('Missing.tpl');
        } catch (\Exception $E) {

        }
    }
}
