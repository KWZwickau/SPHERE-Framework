<?php
namespace SPHERE\UnitTest;

/**
 * MUST start session at this point for tests
 */
\session_start();

require_once( __DIR__.'/../Library/MOC-V/Core/AutoLoader/AutoLoader.php' );
use MOC\V\Core\AutoLoader\AutoLoader;

AutoLoader::getNamespaceAutoLoader('SPHERE', __DIR__.'/../', 'SPHERE');
AutoLoader::getNamespaceAutoLoader('MOC\V', __DIR__.'/../Library/MOC-V', 'MOC\V');

set_include_path(get_include_path().PATH_SEPARATOR.__DIR__.'/../');

date_default_timezone_set('Europe/Berlin');
