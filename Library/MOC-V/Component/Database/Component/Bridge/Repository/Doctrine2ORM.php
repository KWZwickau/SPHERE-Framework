<?php
namespace MOC\V\Component\Database\Component\Bridge\Repository;

use MOC\V\Component\Database\Component\IBridgeInterface;
use MOC\V\Core\AutoLoader\AutoLoader;

/**
 * Class Doctrine2ORM
 *
 * @package MOC\V\Component\Database\Component\Bridge
 */
class Doctrine2ORM extends Doctrine2DBAL implements IBridgeInterface
{

    /**
     *
     */
    public function __construct()
    {

        AutoLoader::getNamespaceAutoLoader('Doctrine\ORM', __DIR__.'/../../../Vendor/Doctrine2ORM/2.5.0/lib');
        AutoLoader::getNamespaceAutoLoader('Doctrine\Common\Cache',
            __DIR__.'/../../../Vendor/Doctrine2Cache/1.4.1/lib');
        AutoLoader::getNamespaceAutoLoader('Doctrine\Common\Annotations',
            __DIR__.'/../../../Vendor/Doctrine2Annotations/1.2.6/lib');
        AutoLoader::getNamespaceAutoLoader('Doctrine\Common\Lexer',
            __DIR__.'/../../../Vendor/Doctrine2Lexer/1.0.1/lib');
        AutoLoader::getNamespaceAutoLoader('Doctrine\Common\Collections',
            __DIR__.'/../../../Vendor/Doctrine2Collections/1.3.0/lib');
        AutoLoader::getNamespaceAutoLoader('Doctrine\Instantiator',
            __DIR__.'/../../../Vendor/Doctrine2Instantiator/1.0.5/src');

        parent::__construct();
    }

}
