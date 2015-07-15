<?php
require_once( __DIR__.'/../../Core/AutoLoader/AutoLoader.php' );
use MOC\V\Component\Documentation\Component\Parameter\Repository\DirectoryParameter;
use MOC\V\Component\Documentation\Component\Parameter\Repository\ExcludeParameter;
use MOC\V\Component\Documentation\Documentation;
use MOC\V\Core\AutoLoader\AutoLoader;

AutoLoader::getNamespaceAutoLoader( '\MOC\V', __DIR__.'/../../' );

Documentation::getDocumentation(
    'MOC',
    'Mark V',
    new DirectoryParameter( __DIR__.'/../../' ),
    new DirectoryParameter( __DIR__.'/../../Documentation' ),
    new ExcludeParameter( array(
        '/.idea/*',
        '/.git/*',
        '/Documentation/*',
        '*/TestSuite/*',
        '*/Vendor/Symfony/*',
        '*/Vendor/ApiGen/*',
        '*/Vendor/Twig/*',
        '*/Repository/TwigTemplate/*',
        '*/Vendor/Smarty/*',
        '*/Repository/SmartyTemplate/*',
        '*/Vendor/Doctrine2DBAL/*',
        '*/Vendor/Doctrine2ORM/*',
        '*/Vendor/PhpExcel/*',
        '*/Vendor/DomPdf/*'
    ) )
);
