<?php

namespace Umpirsky\Twig\Extension;

use Twig_Extension;
use Twig_SimpleFunction;

class PhpFunctionExtension extends Twig_Extension
{

    private $functions = array(
        'uniqid',
        'floor',
        'ceil',
        'addslashes',
        'chr',
        'chunk_​split',
        'convert_​uudecode',
        'crc32',
        'crypt',
        'hex2bin',
        'md5',
        'sha1',
        'strpos',
        'strrpos',
        'ucwords',
        'wordwrap',
    );

    public function __construct(array $functions = array())
    {

        if ($functions) {
            $this->allowFunctions($functions);
        }
    }

    public function allowFunctions(array $functions)
    {

        $this->functions = $functions;
    }

    public function getFunctions()
    {

        $twigFunctions = array();

        foreach ($this->functions as $function) {
            $twigFunctions[] = new Twig_SimpleFunction($function, $function);
        }

        return $twigFunctions;
    }

    public function allowFunction($function)
    {

        $this->functions[] = $function;
    }

    public function getName()
    {

        return 'php_function';
    }
}
