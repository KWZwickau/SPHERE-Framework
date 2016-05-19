<?php
namespace SPHERE\UnitTest;

/**
 * MUST start session at this point for tests
 */
\session_start();
ini_set('memory_limit', '1G');

require_once( __DIR__.'/../Library/MOC-V/Core/AutoLoader/AutoLoader.php' );
use MOC\V\Core\AutoLoader\AutoLoader;

AutoLoader::getNamespaceAutoLoader('SPHERE', __DIR__.'/../', 'SPHERE');
AutoLoader::getNamespaceAutoLoader('MOC\V', __DIR__.'/../Library/MOC-V', 'MOC\V');
AutoLoader::getNamespaceAutoLoader('phpFastCache', __DIR__.'/../Library/PhpFastCache/4.3.6/src');
AutoLoader::getNamespaceAutoLoader('Markdownify', __DIR__.'/../Library/Markdownify/2.1.6/src');

set_include_path(get_include_path().PATH_SEPARATOR.__DIR__.'/../');

date_default_timezone_set('Europe/Berlin');
