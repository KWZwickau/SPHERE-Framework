<?php
namespace MOC\V\TestSuite\Tests\Component\Template;

use MOC\V\Component\Template\Component\Bridge\Repository\SmartyTemplate;
use MOC\V\Component\Template\Component\Bridge\Repository\TwigTemplate;
use MOC\V\Component\Template\Component\Parameter\Repository\FileParameter;

/**
 * Class BridgeTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Template
 */
class BridgeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @codeCoverageIgnore
     */
    public function tearDown()
    {

        if (false !== ( $Path = realpath( __DIR__.'/../../../../Component/Template/Component/Bridge/Repository/SmartyTemplate' ) )) {
            $Iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator( $Path, \RecursiveDirectoryIterator::SKIP_DOTS ),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            /** @var \SplFileInfo $FileInfo */
            foreach ($Iterator as $FileInfo) {
                if ($FileInfo->getBasename() != 'README.md') {
                    unlink( $FileInfo->getPathname() );
                }
            }
        }

        if (false !== ( $Path = realpath( __DIR__.'/../../../../Component/Template/Component/Bridge/Repository/TwigTemplate' ) )) {
            $Iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator( $Path, \RecursiveDirectoryIterator::SKIP_DOTS ),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            /** @var \SplFileInfo $FileInfo */
            foreach ($Iterator as $FileInfo) {
                if ($FileInfo->getBasename() != 'README.md') {
                    if ($FileInfo->isFile()) {
                        unlink( $FileInfo->getPathname() );
                    }
                    if ($FileInfo->isDir()) {
                        rmdir( $FileInfo->getPathname() );
                    }
                }
            }
        }
    }

    public function testTwigTemplate()
    {

        $Bridge = new TwigTemplate();

        $Bridge->loadFile( new FileParameter( __FILE__ ), true );

        $Bridge->setVariable( 'Foo', 'Bar' );
        $Bridge->setVariable( 'Foo', array( 'Bar' ) );

        $Bridge->getContent();
    }

    public function testSmartyTemplate()
    {

        $Bridge = new SmartyTemplate();

        $Bridge->loadFile( new FileParameter( __FILE__ ), true );

        $Bridge->setVariable( 'Foo', 'Bar' );
        $Bridge->setVariable( 'Foo', array( 'Bar' ) );

        $Bridge->getContent();
    }

}
