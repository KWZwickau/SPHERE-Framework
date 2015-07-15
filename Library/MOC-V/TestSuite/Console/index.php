<?php
namespace Bar;

use MOC\V\Component\Captcha\Captcha;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Core\AutoLoader\AutoLoader;

/**
 * Setup: Php
 */
header( 'Content-type: text/html; charset=utf-8' );
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
session_start();
session_write_close();
date_default_timezone_set( 'Europe/Berlin' );
/**
 * Setup: Loader
 */
require_once( __DIR__.'/../../Core/AutoLoader/AutoLoader.php' );
AutoLoader::getNamespaceAutoLoader( '\MOC\V', __DIR__.'/../../' );

var_dump( Captcha::getCaptcha()->createCaptcha()->getCaptcha() );

/*
$File = FileSystem::getUniversalFileLoader( __FILE__ );
var_dump( $File->getLocation() );
var_dump( $File->getRealPath() );

$File = FileSystem::getSymfonyFinder( __FILE__ );
var_dump( $File->getLocation() );
var_dump( $File->getRealPath() );

$File = FileSystem::getSymfonyFinder( '/TestSuite/Console/index.php' );
var_dump( $File->getLocation() );
var_dump( $File->getRealPath() );

$File = FileSystem::getUniversalFileLoader( '/TestSuite/Console/index.php' );
var_dump( $File->getLocation() );
var_dump( $File->getRealPath() );

//$ValueTest = array(
//    '23.11.1980',
//    'öäüß;',
//    '001'
//);
//foreach ($ValueTest as $Value) {
//    /** @var PhpExcel $Document */
//    $Document = Document::getDocument( 'test.xlsx' );
//    $Document->setPaperOrientationParameter( new PaperOrientationParameter( 'LANDSCAPE' ) );
//    $Document->setValue( $Document->getCell( 'A1' ), $Value );
//    $Document->saveFile();
//    $Document = Document::getDocument( 'test.xlsx' );
//    var_dump( $Document->getValue( $Document->getCell( 'A1' ) ) );
//}
