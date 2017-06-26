<?php
namespace SPHERE\Common;

use MOC\V\Component\Router\Component\Bridge\Repository\UniversalRouter;
use MOC\V\Component\Router\Component\Exception\Repository\MissingParameterException;
use MOC\V\Component\Router\Component\Parameter\Repository\RouteParameter;
use MOC\V\Core\HttpKernel\Vendor\Universal\Request;
use SPHERE\System\Extension\Extension;

/**
 * Class Router
 * @package SPHERE\Common
 */
class Router extends UniversalRouter
{

    /**
     * @param callable       $Controller
     * @param RouteParameter $Route
     *
     * @throws MissingParameterException
     * @return array
     */
    protected function handleArguments($Controller, RouteParameter $Route)
    {

        $Reflection = new \ReflectionMethod($Controller[0], $Controller[1]);
        $MethodParameters = $Reflection->getParameters();
        $RequestParameters = array_merge_recursive(
            (new Extension())->getGlobal()->REQUEST,
            (new Request())->getSymfonyRequest()->files->all()
        );
        $MethodArguments = array();
        /** @var \ReflectionParameter $MethodParameter */
        foreach ((array)$MethodParameters as $MethodParameter) {
            // @codeCoverageIgnoreStart
            if (array_key_exists($MethodParameter->name, $RequestParameters)) {
                $MethodArguments[] = $RequestParameters[$MethodParameter->name];
            } elseif (array_key_exists($MethodParameter->name, $Route->getParameterDefault())) {
                $MethodArguments[] = $Route->getParameterDefault($MethodParameter->name);
            } elseif ($MethodParameter->isDefaultValueAvailable()) {
                $MethodArguments[] = $MethodParameter->getDefaultValue();
            } else {
                throw new MissingParameterException($MethodParameter->name);
            }
            // @codeCoverageIgnoreEnd
        }
        return $MethodArguments;
    }
}