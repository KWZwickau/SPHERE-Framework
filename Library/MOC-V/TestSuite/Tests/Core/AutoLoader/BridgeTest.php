<?php
namespace MOC\V\TestSuite\Tests\Core\AutoLoader;

use MOC\V\Core\AutoLoader\Component\Bridge\Repository\MultitonNamespace;
use MOC\V\Core\AutoLoader\Component\Bridge\Repository\UniversalNamespace;
use MOC\V\Core\AutoLoader\Component\Parameter\Repository\DirectoryParameter;
use MOC\V\Core\AutoLoader\Component\Parameter\Repository\NamespaceParameter;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class BridgeTest
 *
 * @package MOC\V\TestSuite\Tests\Core\AutoLoader
 */
class BridgeTest extends AbstractTestCase
{

    public function testUniversalNamespace()
    {

        $Bridge = new UniversalNamespace();

        $this->assertInstanceOf('MOC\V\Core\AutoLoader\Component\IBridgeInterface',
            $Bridge->addNamespaceDirectoryMapping(
                new NamespaceParameter(__NAMESPACE__), new DirectoryParameter(__DIR__))
        );
        $this->assertInstanceOf('MOC\V\Core\AutoLoader\Component\IBridgeInterface',
            $Bridge->addNamespaceDirectoryMapping(
                new NamespaceParameter('\MOC\V'), new DirectoryParameter(__DIR__.'/../../../../'))
        );
        $this->assertInstanceOf('MOC\V\Core\AutoLoader\Component\IBridgeInterface',
            $Bridge->registerLoader()
        );

        $this->assertFalse(
            $Bridge->loadSourceFile('Error')
        );
        $this->assertFalse(
            $Bridge->loadSourceFile('MOC\V\NotAvailableClass')
        );
        $this->assertFalse(
            $Bridge->loadSourceFile('IErrorInterface')
        );
        $this->assertFalse(
            $Bridge->loadSourceFile('MOC\V\INotAvailableInterface')
        );

        $this->assertTrue(
            $Bridge->loadSourceFile(__CLASS__)
        );
        $this->assertTrue(
            $Bridge->loadSourceFile('MOC\V\Core\AutoLoader\Component\IBridgeInterface')
        );

        $this->assertInstanceOf('MOC\V\Core\AutoLoader\Component\IBridgeInterface',
            $Bridge->unregisterLoader()
        );

    }

    public function testMultitonNamespace()
    {

        $Bridge = new MultitonNamespace(new NamespaceParameter(__NAMESPACE__), new DirectoryParameter(__DIR__));

        try {
            $Bridge->addNamespaceDirectoryMapping(
                new NamespaceParameter(__NAMESPACE__), new DirectoryParameter(__DIR__)
            );
        } catch (\Exception $E) {
            $this->assertInstanceOf('MOC\V\Core\AutoLoader\Exception\AutoLoaderException', $E);
        }

        $this->assertFalse(
            $Bridge->loadSourceFile('Error')
        );
        $this->assertFalse(
            $Bridge->loadSourceFile('MOC\V\NotAvailableClass')
        );
        $this->assertFalse(
            $Bridge->loadSourceFile('IErrorInterface')
        );
        $this->assertFalse(
            $Bridge->loadSourceFile('MOC\V\INotAvailableInterface')
        );
        $this->assertTrue(
            $Bridge->loadSourceFile(__CLASS__)
        );
        $this->assertTrue(
            $Bridge->loadSourceFile('MOC\V\Core\AutoLoader\Component\IBridgeInterface')
        );
        $this->assertInstanceOf('MOC\V\Core\AutoLoader\Component\IBridgeInterface',
            $Bridge->unregisterLoader()
        );

        $Bridge = new MultitonNamespace(new NamespaceParameter('\MOC\V'),
            new DirectoryParameter(__DIR__.'/../../../../'), new NamespaceParameter('MOC\V'));

        $this->assertFalse(
            $Bridge->loadSourceFile('Error')
        );
        $this->assertFalse(
            $Bridge->loadSourceFile('MOC\V\NotAvailableClass')
        );
        $this->assertFalse(
            $Bridge->loadSourceFile('IErrorInterface')
        );
        $this->assertFalse(
            $Bridge->loadSourceFile('MOC\V\INotAvailableInterface')
        );
        $this->assertTrue(
            $Bridge->loadSourceFile(__CLASS__)
        );
        $this->assertTrue(
            $Bridge->loadSourceFile('MOC\V\Core\AutoLoader\Component\IBridgeInterface')
        );
        $this->assertInstanceOf('MOC\V\Core\AutoLoader\Component\IBridgeInterface',
            $Bridge->unregisterLoader()
        );

    }

}
