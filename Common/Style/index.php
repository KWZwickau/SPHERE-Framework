<?php
namespace SPHERE;

use MOC\V\Core\AutoLoader\AutoLoader;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Extension\Extension;

/**
 * Setup: Php
 */
header('Content-type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Europe/Berlin');
session_start();
session_write_close();
set_time_limit(240);
ob_implicit_flush();
ini_set('memory_limit', '1024M');

/**
 * Setup: Loader
 */
require_once( __DIR__.'/../../Library/MOC-V/Core/AutoLoader/AutoLoader.php' );
AutoLoader::getNamespaceAutoLoader('MOC\V', __DIR__.'/../../Library/MOC-V');
AutoLoader::getNamespaceAutoLoader('SPHERE', __DIR__.'/../../', 'SPHERE');

/**
 * Setup: LESS-Parser
 */

require_once( __DIR__.'/../../Library/LessPhp/1.7.0.5/lessc.inc.php' );
$Parser = array('cache_dir' => __DIR__.'/Resource', 'compress' => false);
$Less = new \Less_Parser($Parser);

$Less->parseFile(__DIR__.'/../../Library/Bootstrap/3.3.5/less/bootstrap.less');

// Grid

$Less->parse('@grid-gutter-width: 20px;');

//$Less->parse( '@body-bg: #000;' );
//$Less->parse( '@text-color: @gray-lighter;' );
//$Less->parse( '@icon-font-path: "../../../Library/Bootstrap.Glyphicons/1.9.0/glyphicons_halflings/web/html_css/fonts/";' );
//
//$Less->parse( '@headings-font-family: "CorpoALigCondensedRegular";' );
$Less->parse('@font-size-base: 13px;');

//$Less->parse( '@headings-color: @gray;' );
//$Less->parse( '@headings-small-color: @gray-light;' );
//$Less->parse( '@font-size-h1: floor((@font-size-base * 2.9));' );
//$Less->parse( '@font-size-h2: floor((@font-size-base * 2.45));' );
//$Less->parse( '@font-size-h3: floor((@font-size-base * 2.0));' );
//$Less->parse( '@font-size-h4: floor((@font-size-base * 1.55));' );
//$Less->parse( '@font-size-h5: floor((@font-size-base * 1.3));' );
//$Less->parse( '@font-size-h6: ceil((@font-size-base * 1.05));' );

//$Less->parse( '@link-color: @gray-lighter;' );
//$Less->parse( '@link-hover-color: #00adef;' );
$Less->parse('@link-hover-decoration: none;');

$Less->parse('@nav-link-padding: 9px 15px;');
//$Less->parse( '@nav-link-hover-bg: transparent;' );
//
$Less->parse('@navbar-height: 40px;');
//    $Less->parse( '@navbar-margin-bottom: 5px;' );
//
//$Less->parse( '@navbar-default-color: @gray-lighter;' );
//$Less->parse( '@navbar-default-bg: transparent;' );
//$Less->parse( '@navbar-default-border: transparent;' );
//
//$Less->parse( '@navbar-default-link-color: @link-color;' );
//$Less->parse( '@navbar-default-link-hover-color: @link-hover-color;' );
//$Less->parse( '@navbar-default-link-active-color: @link-hover-color;' );
//
//$Less->parse( '@nav-tabs-border-color: transparent;' );
//$Less->parse( '@nav-tabs-link-hover-border-color: transparent;' );
//
//$Less->parse( '@dropdown-bg: transparent;' );
//$Less->parse( '@dropdown-link-color: @link-color;' );
//$Less->parse( '@dropdown-link-hover-color: @link-hover-color;' );
//$Less->parse( '@dropdown-link-hover-bg: @dropdown-bg;' );
//$Less->parse( '@dropdown-divider-bg: @gray-dark;' );
//
//$Less->parse( '@breadcrumb-bg: transparent;' );
//$Less->parse( '@breadcrumb-color: @link-color;' );
//$Less->parse( '@breadcrumb-active-color: @link-hover-color;' );
//$Less->parse( '@breadcrumb-padding-vertical: 2px;' );
//$Less->parse( '@breadcrumb-padding-horizontal: 0;' );
//
//$Less->parse( '@table-cell-padding: 6px;' );
//$Less->parse( '@table-condensed-cell-padding: 5px;' );

// Panel

$Less->parse('@panel-heading-padding: 7px 10px;');
$Less->parse('@panel-body-padding: 5px 10px;');

// Thumbnail

$Less->parse('@thumbnail-padding: 6px;');
$Less->parse('@thumbnail-caption-padding: 9px;');
//$Less->parse( '@thumbnail-border: @gray-dark;' );
//$Less->parse( '@thumbnail-border-radius: 0;' );

// Component
$Less->parse('@padding-base-vertical: 4px;');
$Less->parse('@padding-base-horizontal: 10px;');
$Less->parse('@padding-large-vertical: 8px;');
$Less->parse('@padding-large-horizontal: 14px;');
$Less->parse('@padding-small-vertical: 3px;');
$Less->parse('@padding-small-horizontal: 8px;');
$Less->parse('@padding-xs-vertical: 1px;');
$Less->parse('@padding-xs-horizontal: 3px;');

$Less->parse('@border-radius-base: 3px;');
$Less->parse('@border-radius-large: 5px;');
$Less->parse('@border-radius-small: 2px;');

$Less->parse('@form-group-margin-bottom: 10px;');

$Style = FileSystem::getFileWriter(__DIR__.'/Bootstrap.css')->getLocation();
file_put_contents($Style, $Less->getCss());

(new Extension())->getDebugger()->screenDump($Less->AllParsedFiles());
