<?php
namespace SPHERE\UnitTest\Console;

use MOC\V\Core\AutoLoader\AutoLoader;
use SPHERE\Application\Transfer\Gateway\Converter\Output;
use SPHERE\Application\Transfer\Gateway\Fragment\People;
use SPHERE\Application\Transfer\Gateway\Item\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Transfer\Gateway\Fragment\Meta;

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
ini_set('display_errors',1 );

/**
 * Setup: Loader
 */
require_once(__DIR__ . '/../../Library/MOC-V/Core/AutoLoader/AutoLoader.php');
AutoLoader::getNamespaceAutoLoader('MOC\V', __DIR__ . '/../../Library/MOC-V');
AutoLoader::getNamespaceAutoLoader('SPHERE', __DIR__ . '/../../', 'SPHERE');
AutoLoader::getNamespaceAutoLoader('Markdownify', __DIR__ . '/../../Library/Markdownify/2.1.6/src');

$O = new Output();
$P = new People();

$P1 = new Person( array(
    new TblPerson()
), 'TblPerson');
$P1->setPayload( array( 'FirstName' => 'Test' ) );

$P->addItem( $P1 );

$M = new Meta();

$P->addFragment( $M );

$O->addFragment( $P );

highlight_string( $O->getXml() );
