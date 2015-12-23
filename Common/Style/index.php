<?php
namespace SPHERE;

use MOC\V\Core\AutoLoader\AutoLoader;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\System\Extension\Extension;

/**
 * Setup: Php
 */
header('Content-type: text/html; charset=utf-8');
error_reporting(E_ALL);
date_default_timezone_set('Europe/Berlin');
session_start();
session_write_close();
set_time_limit(240);
ob_implicit_flush();

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

$Less->parse('@font-size-base: 13px;');
$Less->parse('@link-hover-decoration: none;');
$Less->parse('@nav-link-padding: 9px 15px;');
$Less->parse('@navbar-height: 40px;');

// Panel

$Less->parse('@panel-heading-padding: 7px 10px;');
$Less->parse('@panel-body-padding: 5px 10px;');

// Thumbnail

$Less->parse('@thumbnail-padding: 6px;');
$Less->parse('@thumbnail-caption-padding: 9px;');

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

$Less->parse('@btn-default-bg: rgb(231, 231, 231);');

$Less->parse('@well-bg: rgb(242, 242, 242);');

$Style = FileSystem::getFileWriter(__DIR__.'/Bootstrap.css')->getLocation();
file_put_contents($Style, $Less->getCss());

(new Extension())->getDebugger()->screenDump($Less->AllParsedFiles());
