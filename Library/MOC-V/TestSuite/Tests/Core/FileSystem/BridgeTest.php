<?php
namespace MOC\V\TestSuite\Tests\Core\FileSystem;

use MOC\V\Core\FileSystem\Component\Bridge\Repository\SymfonyFinder;
use MOC\V\Core\FileSystem\Component\Bridge\Repository\UniversalFileLoader;
use MOC\V\Core\FileSystem\Component\Bridge\Repository\UniversalFileWriter;
use MOC\V\Core\FileSystem\Component\Parameter\Repository\FileParameter;

class BridgeTest extends \PHPUnit_Framework_TestCase
{

    public function testSymfonyFinder()
    {

        new SymfonyFinder(
            new FileParameter( __FILE__ )
        );
    }

    public function testUniversalFileLoader()
    {

        new UniversalFileLoader(
            new FileParameter( __FILE__ )
        );
    }

    public function testUniversalFileWriter()
    {

        new UniversalFileWriter(
            new FileParameter( __FILE__ )
        );
    }
}
