<?php

namespace Umpirsky\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\Extension\ExtensionInterface;

class PhpFunctionExtension extends AbstractExtension implements ExtensionInterface
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
        'gettype',
    );

    public function __construct(array $functions = array())
    {
        if ($functions) {
            $this->allowFunctions($functions);
        }
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

    public function allowFunctions(array $functions)
    {
        $this->functions = $functions;
    }

    public function getName()
    {
        return 'php_function';
    }
}

/**
 * Represents a template function.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Twig_SimpleFunction
{

    protected $name;
    protected $callable;
    protected $options;
    protected $arguments = array();

    public function __construct($name, $callable, array $options = array())
    {

        $this->name = $name;
        $this->callable = $callable;
        $this->options = array_merge(array(
            'needs_environment' => false,
            'needs_context'     => false,
            'is_safe'           => null,
            'is_safe_callback'  => null,
            'node_class'        => 'Twig_Node_Expression_Function',
        ), $options);
    }

    public function getName()
    {

        return $this->name;
    }

    public function getCallable()
    {

        return $this->callable;
    }

    public function getNodeClass()
    {

        return $this->options['node_class'];
    }

    public function getArguments()
    {

        return $this->arguments;
    }

    public function setArguments($arguments)
    {

        $this->arguments = $arguments;
    }

    public function needsEnvironment()
    {

        return $this->options['needs_environment'];
    }

    public function needsContext()
    {

        return $this->options['needs_context'];
    }

    public function getSafe(Twig_Node $functionArgs)
    {

        if (null !== $this->options['is_safe']) {
            return $this->options['is_safe'];
        }

        if (null !== $this->options['is_safe_callback']) {
            return call_user_func($this->options['is_safe_callback'], $functionArgs);
        }

        return array();
    }
}
