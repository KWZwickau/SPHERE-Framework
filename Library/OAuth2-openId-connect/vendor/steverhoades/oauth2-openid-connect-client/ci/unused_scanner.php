<?php

declare(strict_types=1);

/*
 * Config for insolita/unused-scanner package
 * Set $projectPath = getcwd(); if your put it under project root
 */

$projectPath = dirname(__file__, 2);

return [
    /**
     * Required params
     **/
    'composerJsonPath' => $projectPath . '/composer.json',
    'vendorPath' => $projectPath . '/vendor/',

    /**
     * Array of full directories path for scan;
     * Scan will be recursive
     * Put directories with most intensive imports in top of list for more quick result
     *
     * @see http://api.symfony.com/4.0/Symfony/Component/Finder/Finder.html#method_in
     **/
    'scanDirectories' => [
        $projectPath . '/src',
    ],

    /**
     * Optional params
     **/
    'skipPackages' => [ //List of packages that must be excluded from verification
    ],
    /**
     * Names relative to ones of scanDirectories
     *
     * @see http://api.symfony.com/4.0/Symfony/Component/Finder/Finder.html#method_exclude
     **/
    'excludeDirectories' => [
        'vendor',
    ],

    'scanFiles' => [],
    'extensions' => ['*.php'],
    'requireDev' => false, //Check composer require-dev section, default false

    /**
     * Optional, custom logic for check is file contains definitions of package
     *
     * @example
     * 'customMatch'=> function($definition, $packageName, \Symfony\Component\Finder\SplFileInfo $file):bool{
     *         $isPresent = false;
     *         if($packageName === 'phpunit/phpunit'){
     *              $isPresent = true;
     *         }
     *          if($file->getExtension()==='twig'){
     *            $isPresent = customCheck();
     *          }
     *         return $isPresent;
     * }
     **/
    'customMatch' => null,

    /**
     * Report mode options
     * Report mode enabled, when reportPath value is valid directory path
     * !!!Note!!! The scanning time and memory usage will be increased when report mode enabled,
     * it sensitive especially for big projects and when requireDev option enabled
     **/
    'reportPath' => null, //path in directory, where usage report will be stores;

    /**
     * Optional custom formatter (by default report stored as json)
     * $report array format
     * [
     *     'packageName'=> [
     *           'definition'=>['fileNames',...]
     *           ....
     *      ]
     *      ...
     * ]
     *
     * @example
     *  'reportFormatter'=>function(array $report):string{
     *     return print_r($report, true);
     * }
     **/
    'reportFormatter' => null,
    'reportExtension' => null, //by default - json, set own, if use custom formatter
];
