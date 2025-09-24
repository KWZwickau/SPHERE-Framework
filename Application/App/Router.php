<?php

namespace SPHERE\Application\App;


use MOC\V\Component\Router\Component\IBridgeInterface;
use MOC\V\Component\Router\Component\Parameter\Repository\RouteParameter;
use MOC\V\Core\HttpKernel\Vendor\Universal\Request;
use ReflectionMethod;
use SPHERE\Common\Router as SPHERE_Router;
use SPHERE\System\Extension\Extension;
use Throwable;

/**
 *
 */
class Router extends SPHERE_Router
{
    public function addRoute(RouteParameter $RouteOption): IBridgeInterface
    {
        // Sanitize route
        $routeOptionSanitized = new RouteParameter(strtolower($RouteOption->getPath()), $RouteOption->getController());
        return parent::addRoute($routeOptionSanitized);
    }

    /**
     * @param callable       $Controller
     * @param RouteParameter $Route
     *
     * @return array
     * @throws AppException
     * @noinspection PhpMissingParentCallCommonInspection
     */
    protected function handleArguments($Controller, RouteParameter $Route): array
    {
        try {
            $reflectionMethod = new ReflectionMethod($Controller[0], $Controller[1]);
        } catch (Throwable $throwable) {
            throw new AppException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
        $reflectionParameters = $reflectionMethod->getParameters();
        $requestParameters = array_merge_recursive(
            (new Extension())->getGlobal()->REQUEST,
            (new Request())->getSymfonyRequest()->files->all()
        );
        $methodArguments = [];
        foreach ($reflectionParameters as $reflectionParameter) {
            if (array_key_exists($reflectionParameter->name, $requestParameters)) {
                $methodArguments[] = $requestParameters[$reflectionParameter->name];
            } elseif (array_key_exists($reflectionParameter->name, $Route->getParameterDefault())) {
                $methodArguments[] = $Route->getParameterDefault($reflectionParameter->name);
            } elseif ($reflectionParameter->isDefaultValueAvailable()) {
                $methodArguments[] = $reflectionParameter->getDefaultValue();
            }
        }
        return $methodArguments;
    }
}
