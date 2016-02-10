<?php
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
\MOC\V\Core\AutoLoader\AutoLoader::getNamespaceAutoLoader('MOC\V', __DIR__ . '/../../Library/MOC-V');
\MOC\V\Core\AutoLoader\AutoLoader::getNamespaceAutoLoader('SPHERE', __DIR__ . '/../../', 'SPHERE');
\MOC\V\Core\AutoLoader\AutoLoader::getNamespaceAutoLoader('Markdownify', __DIR__ . '/../../Library/Markdownify/2.1.6/src');

//$FP = new \SPHERE\Application\Reporting\Gateway\Fragment\People();
//$IP = new \SPHERE\Application\Reporting\Gateway\Item\Person();
//
//$FP->addItem( $IP );
//
//print '<pre>';

///* create a dom document with encoding utf8 */
//$domtree = new DOMDocument('1.0', 'utf-8');
//
///* create the root element of the xml tree */
//$xmlRoot = $domtree->createElement("xml");
///* append it to the document created */
//$xmlRoot = $domtree->appendChild($xmlRoot);
//
//$currentTrack = $domtree->createElement("track");
//$currentTrack->setAttribute('reference',1);
//$currentTrack = $xmlRoot->appendChild($currentTrack);
//
///* you should enclose the following two lines in a cicle */
//$currentTrack->appendChild($domtree->createElement('path','song1.mp3'));
//$currentTrack->appendChild($domtree->createElement('title','title of song1.mp3'));
//
//$currentTrack->appendChild($domtree->createElement('path','song2.mp3'));
//$currentTrack->appendChild($domtree->createElement('title','title of song2.mp3'));
//
//print htmlentities( $domtree->saveXML() );

$O = new \SPHERE\Application\Reporting\Gateway\Converter\Output();
$P = new \SPHERE\Application\Reporting\Gateway\Fragment\People();

$P1 = new \SPHERE\Application\Reporting\Gateway\Item\Person( array(
    new \SPHERE\Application\People\Person\Service\Entity\TblPerson()
), 'TblPerson');
$P1->setPayload( array( 'FirstName' => 'Test' ) );

$P->addItem( $P1 );

$M = new \SPHERE\Application\Reporting\Gateway\Fragment\Meta();

$P->addFragment( $M );

$O->addFragment( $P );

highlight_string( $O->getXml() );
