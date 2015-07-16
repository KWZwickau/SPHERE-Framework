<?php
namespace SPHERE;

use MOC\V\Core\AutoLoader\AutoLoader;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Setup: Php
 */
header( 'Content-type: text/html; charset=utf-8' );
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
date_default_timezone_set( 'Europe/Berlin' );
session_start();
session_write_close();

/**
 * Setup: Loader
 */
require_once( __DIR__.'/Library/MOC-V/Core/AutoLoader/AutoLoader.php' );
AutoLoader::getNamespaceAutoLoader( 'MOC\V', __DIR__.'/Library/MOC-V' );
AutoLoader::getNamespaceAutoLoader( 'SPHERE', __DIR__.'/', 'SPHERE' );

$Main = new Main();
$Main->runPlatform();
/*
try {

    new System\Database\Configuration( new Identifier( 'System', 'Protocol' ) );
    new System\Database\Configuration( new Identifier( 'System', 'Gatekeeper', 'Token' ) );

    $Display = $Main->getDisplay();

    $Display->addServiceNavigation( new Link(
        new Link\Route( '/System/Authentication' ),
        new Link\Name( 'Anmelden' ),
        new Link\Icon( new Lock() )
    ) );

    print $Display->getContent();

} catch( \ErrorException $Exception ) {

    $Main->getDisplay()->setException( $Exception );

    print $Main->getDisplay()->getContent();
} catch( \Exception $Exception ) {

    $Main->getDisplay()->setException( $Exception );

    print $Main->getDisplay()->getContent();
}
