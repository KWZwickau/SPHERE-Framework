<?php
namespace SPHERE;

use MOC\V\Core\AutoLoader\AutoLoader;
use SPHERE\Common\Window\Display;
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

/**
 * Setup: Loader
 */
require_once( __DIR__.'/Library/MOC-V/Core/AutoLoader/AutoLoader.php' );
AutoLoader::getNamespaceAutoLoader( 'MOC\V', __DIR__.'/Library/MOC-V' );
AutoLoader::getNamespaceAutoLoader( 'SPHERE', __DIR__.'/', 'SPHERE' );

print ( new Display() )->getContent();

new System\Database\Configuration( new Identifier( 'System', 'Gatekeeper', 'Token' ) );

print ( new System\Extension\Configuration() )->getDebugger()->getProtocol();
