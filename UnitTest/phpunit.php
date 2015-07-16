<?php
namespace SPHERE\TestSuite;

use MOC\V\Core\AutoLoader\AutoLoader;

require_once( __DIR__.'/../Library/MOC-V/Core/AutoLoader/AutoLoader.php' );

AutoLoader::getNamespaceAutoLoader( 'MOC\V', __DIR__.'/../Library/MOC-V', 'MOC\V' );
AutoLoader::getNamespaceAutoLoader( 'SPHERE\Application', __DIR__.'/../', 'SPHERE' );
AutoLoader::getNamespaceAutoLoader( 'SPHERE\Client', __DIR__.'/../', 'SPHERE' );
AutoLoader::getNamespaceAutoLoader( 'SPHERE\Common', __DIR__.'/../', 'SPHERE' );
AutoLoader::getNamespaceAutoLoader( 'SPHERE', __DIR__.'/../' );
AutoLoader::getNamespaceAutoLoader( 'Markdownify', __DIR__.'/../Library/Markdownify/2.1.6/src' );
AutoLoader::getNamespaceAutoLoader( 'SPHERE\TestSuite\Tests', __DIR__.'/Tests', 'SPHERE\TestSuite\Tests' );

set_include_path( get_include_path().PATH_SEPARATOR.__DIR__.'/../' );

date_default_timezone_set( 'Europe/Berlin' );

register_shutdown_function( function () {

    if (false !== ( $Path = realpath( __DIR__.'/../Library/MOC-V/Component/Template/Component/Bridge/Repository/SmartyTemplate' ) )) {
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

    if (false !== ( $Path = realpath( __DIR__.'/../Library/MOC-V/Component/Template/Component/Bridge/Repository/TwigTemplate' ) )) {
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
} );
