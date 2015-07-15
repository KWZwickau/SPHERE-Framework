<?php
namespace MOC\V\TestSuite;

use MOC\V\Core\AutoLoader\AutoLoader;

require_once( __DIR__.'/../Core/AutoLoader/AutoLoader.php' );

AutoLoader::getNamespaceAutoLoader( '\MOC\V', __DIR__.'/../' );

set_include_path( get_include_path().PATH_SEPARATOR.__DIR__.'/../' );

date_default_timezone_set( 'Europe/Berlin' );
