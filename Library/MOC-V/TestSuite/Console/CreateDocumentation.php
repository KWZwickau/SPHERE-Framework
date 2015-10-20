<?php
/**
 * Setup: Php
 */
header('Content-type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
session_write_close();
date_default_timezone_set('Europe/Berlin');
/**
 * Setup: Loader
 */
require_once( __DIR__.'/../../Core/AutoLoader/AutoLoader.php' );
use MOC\V\Component\Documentation\Component\Parameter\Repository\DirectoryParameter;
use MOC\V\Component\Documentation\Component\Parameter\Repository\ExcludeParameter;
use MOC\V\Component\Documentation\Documentation;
use MOC\V\Core\AutoLoader\AutoLoader;

AutoLoader::getNamespaceAutoLoader('\MOC\V', __DIR__.'/../../');

Documentation::getDocumentation(
    'MOC',
    'Mark V',
    new DirectoryParameter(__DIR__.'/../../'),
    new DirectoryParameter(__DIR__.'/../../../MOC-Framework-Mark-V-Documentation'),
    new ExcludeParameter(array(
        '/.idea/*',
        '/.git/*',
        '*/TestSuite/*',
        '*/Vendor/Symfony/*',
        '*/Vendor/PhpSecLib/*',
        '*/Vendor/SimplePhpCaptcha/*',
        '*/Vendor/Doctrine2Annotations/*',
        '*/Vendor/Doctrine2Cache/*',
        '*/Vendor/Doctrine2Collections/*',
        '*/Vendor/Doctrine2Common/*',
        '*/Vendor/Doctrine2DBAL/*',
        '*/Vendor/Doctrine2Instantiator/*',
        '*/Vendor/Doctrine2Lexer/*',
        '*/Vendor/Doctrine2ORM/*',
        '*/Vendor/PhpExcel/*',
        '*/Vendor/DomPdf/*',
        '*/Vendor/mPdf/*',
        '*/Vendor/ApiGen/*',
        '*/Vendor/Template/*',
        '*/Vendor/Twig/*',
        '*/Vendor/TwigExtension/*',
        '*/Repository/TwigTemplate/*',
        '*/Vendor/Smarty/*',
        '*/Repository/SmartyTemplate/*',
        '*/Vendor/EdenPhpMail/*'
    ))
);
