<?php
namespace MOC\V\TestSuite\Tests\Component\Documentation;

use MOC\V\Component\Documentation\Component\Bridge\Repository\ApiGen;
use MOC\V\Component\Documentation\Component\Parameter\Repository\DirectoryParameter;

/**
 * Class BridgeTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Documentation
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

    public function testApiGen()
    {

        $this->assertInstanceOf( 'MOC\V\Component\Documentation\Component\Bridge\Repository\ApiGen', new ApiGen(
            'MOC',
            'Test',
            new DirectoryParameter( __DIR__ ),
            new DirectoryParameter( __DIR__.'/Content/' )
        ) );
    }

}
