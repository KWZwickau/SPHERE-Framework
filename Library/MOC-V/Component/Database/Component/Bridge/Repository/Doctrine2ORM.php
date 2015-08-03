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
    function __construct()
    {

        AutoLoader::getNamespaceAutoLoader( 'Doctrine\ORM', __DIR__.'/../../../Vendor/Doctrine2ORM/2.5.0/lib' );
        AutoLoader::getNamespaceAutoLoader( 'Doctrine\Common',
            __DIR__.'/../../../Vendor/Doctrine2ORM/2.5.0/vendor/doctrine/cache/lib' );
        AutoLoader::getNamespaceAutoLoader( 'Doctrine\Common',
            __DIR__.'/../../../Vendor/Doctrine2ORM/2.5.0/vendor/doctrine/annotations/lib' );
        AutoLoader::getNamespaceAutoLoader( 'Doctrine\Common',
            __DIR__.'/../../../Vendor/Doctrine2ORM/2.5.0/vendor/doctrine/lexer/lib' );
        AutoLoader::getNamespaceAutoLoader( 'Doctrine\Common',
            __DIR__.'/../../../Vendor/Doctrine2ORM/2.5.0/vendor/doctrine/collections/lib' );
        AutoLoader::getNamespaceAutoLoader( 'Doctrine\Instantiator',
            __DIR__.'/../../../Vendor/Doctrine2ORM/2.5.0/vendor/doctrine/instantiator/src' );

        parent::__construct();
    }

}
