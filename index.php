<?php
namespace SPHERE;

use MOC\V\Core\AutoLoader\AutoLoader;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Cache\Cache;
use SPHERE\System\Cache\Type\ApcSma;
use SPHERE\System\Cache\Type\Apcu;
use SPHERE\System\Cache\Type\ApcUser;
use SPHERE\System\Cache\Type\Memcached;
use SPHERE\System\Cache\Type\Memory;
use SPHERE\System\Cache\Type\OpCache;

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
ini_set( 'memory_limit', '1024M' );

/**
 * Setup: Loader
 */
require_once( __DIR__.'/Library/MOC-V/Core/AutoLoader/AutoLoader.php' );
AutoLoader::getNamespaceAutoLoader( 'MOC\V', __DIR__.'/Library/MOC-V' );
AutoLoader::getNamespaceAutoLoader( 'SPHERE', __DIR__.'/', 'SPHERE' );

if( false ) {
    (new Cache(new ApcSma()))->getCache()->clearCache();
    (new Cache(new Apcu()))->getCache()->clearCache();
    (new Cache(new ApcUser()))->getCache()->clearCache();
    (new Cache(new Memcached()))->getCache()->clearCache();
    (new Cache(new Memory()))->getCache()->clearCache();
    (new Cache(new OpCache()))->getCache()->clearCache();
}

//Debugger::$Enabled = true;

$Main = new Main();
$Main->runPlatform();

