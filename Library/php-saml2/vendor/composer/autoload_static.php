<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite84ec9ab0e179ec36eef85bc2ab66fe7
{
    public static $files = array (
        '320cde22f66dd4f5d3fd621d3e88b98f' => __DIR__ . '/..' . '/symfony/polyfill-ctype/bootstrap.php',
        '0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
        'd9d39f82a605ebe5918f683dd402334c' => __DIR__ . '/..' . '/padraic/humbug_get_contents/src/function.php',
        '3a50d90d85c7fe889a94ae1114b921ce' => __DIR__ . '/..' . '/padraic/humbug_get_contents/src/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Polyfill\\Mbstring\\' => 26,
            'Symfony\\Polyfill\\Ctype\\' => 23,
            'Symfony\\Component\\Yaml\\' => 23,
            'Symfony\\Component\\Stopwatch\\' => 28,
            'Symfony\\Component\\Filesystem\\' => 29,
            'Symfony\\Component\\EventDispatcher\\' => 34,
            'Symfony\\Component\\Debug\\' => 24,
            'Symfony\\Component\\Console\\' => 26,
            'Symfony\\Component\\Config\\' => 25,
            'Satooshi\\' => 9,
        ),
        'R' => 
        array (
            'RobRichards\\XMLSecLibs\\' => 23,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
        'O' => 
        array (
            'OneLogin\\' => 9,
        ),
        'H' => 
        array (
            'Humbug\\SelfUpdate\\' => 18,
            'Humbug\\' => 7,
        ),
        'C' => 
        array (
            'Composer\\CaBundle\\' => 18,
            'CodeClimate\\PhpTestReporter\\' => 28,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Polyfill\\Mbstring\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
        ),
        'Symfony\\Polyfill\\Ctype\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-ctype',
        ),
        'Symfony\\Component\\Yaml\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/yaml',
        ),
        'Symfony\\Component\\Stopwatch\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/stopwatch',
        ),
        'Symfony\\Component\\Filesystem\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/filesystem',
        ),
        'Symfony\\Component\\EventDispatcher\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/event-dispatcher',
        ),
        'Symfony\\Component\\Debug\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/debug',
        ),
        'Symfony\\Component\\Console\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/console',
        ),
        'Symfony\\Component\\Config\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/config',
        ),
        'Satooshi\\' => 
        array (
            0 => __DIR__ . '/..' . '/satooshi/php-coveralls/src/Satooshi',
        ),
        'RobRichards\\XMLSecLibs\\' => 
        array (
            0 => __DIR__ . '/..' . '/robrichards/xmlseclibs/src',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'OneLogin\\' => 
        array (
            0 => __DIR__ . '/..' . '/onelogin/php-saml/src',
        ),
        'Humbug\\SelfUpdate\\' => 
        array (
            0 => __DIR__ . '/..' . '/padraic/phar-updater/src',
        ),
        'Humbug\\' => 
        array (
            0 => __DIR__ . '/..' . '/padraic/humbug_get_contents/src',
        ),
        'Composer\\CaBundle\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/ca-bundle/src',
        ),
        'CodeClimate\\PhpTestReporter\\' => 
        array (
            0 => __DIR__ . '/..' . '/codeclimate/php-test-reporter/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'G' => 
        array (
            'Guzzle\\Tests' => 
            array (
                0 => __DIR__ . '/..' . '/guzzle/guzzle/tests',
            ),
            'Guzzle' => 
            array (
                0 => __DIR__ . '/..' . '/guzzle/guzzle/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite84ec9ab0e179ec36eef85bc2ab66fe7::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite84ec9ab0e179ec36eef85bc2ab66fe7::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInite84ec9ab0e179ec36eef85bc2ab66fe7::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
