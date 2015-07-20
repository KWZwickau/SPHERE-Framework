<?php
namespace SPHERE;

use MOC\V\Core\AutoLoader\AutoLoader;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Setup: Php
 */
header( 'Content-type: text/html; charset=utf-8' );
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
date_default_timezone_set( 'Europe/Berlin' );
session_start();
session_write_close();
set_time_limit( 240 );
ob_implicit_flush();
ini_set('memory_limit', '1024M');

/**
 * Setup: Loader
 */
require_once( __DIR__.'/Library/MOC-V/Core/AutoLoader/AutoLoader.php' );
AutoLoader::getNamespaceAutoLoader( 'MOC\V', __DIR__.'/Library/MOC-V' );
AutoLoader::getNamespaceAutoLoader( 'SPHERE', __DIR__.'/', 'SPHERE' );

//new System\Database\Database( new Identifier( 'System', 'Protocol' ) );
//new System\Database\Database( new Identifier( 'System', 'Gatekeeper', 'Token' ) );

$Main = new Main();
$Main->runPlatform();

