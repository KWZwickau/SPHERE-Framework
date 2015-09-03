<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Application\UI;

use Nette;
use Nette\Application\BadRequestException;

/**
 * Helpers for Presenter & PresenterComponent.
 *
 * @author     David Grudl
 * @internal
 */
class PresenterComponentReflection extends Nette\Reflection\ClassType
{

    /** @var array getPersistentParams cache */
    private static $ppCache = array();

    /** @var array getPersistentComponents cache */
    private static $pcCache = array();

    /** @var array isMethodCallable cache */
    private static $mcCache = array();

    /**
     * @return array
     */
    public static function combineArgs(\ReflectionFunctionAbstract $method, $args)
    {

        $res = array();
        $i = 0;
        foreach ($method->getParameters() as $param) {
            $name = $param->getName();
            if (isset( $args[$name] )) { // NULLs are ignored
                $res[$i++] = $args[$name];
                $type = $param->isArray() ? 'array' : ( $param->isDefaultValueAvailable() ? gettype($param->getDefaultValue()) : 'NULL' );
                if (!self::convertType($res[$i - 1], $type)) {
                    $mName = $method instanceof \ReflectionMethod ? $method->getDeclaringClass()->getName().'::'.$method->getName() : $method->getName();
                    throw new BadRequestException("Invalid value for parameter '$name' in method $mName(), expected ".( $type === 'NULL' ? 'scalar' : $type ).".");
                }
            } else {
                $res[$i++] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : ( $param->isArray() ? array() : null );
            }
        }
        return $res;
    }

    /**
     * Non data-loss type conversion.
     *
     * @param  mixed
     * @param  string
     *
     * @return bool
     */
    public static function convertType(& $val, $type)
    {

        if ($val === null || is_object($val)) {
            // ignore
        } elseif ($type === 'array') {
            if (!is_array($val)) {
                return false;
            }
        } elseif (!is_scalar($val)) {
            return false;

        } elseif ($type !== 'NULL') {
            $old = $val = ( $val === false ? '0' : (string)$val );
            settype($val, $type);
            if ($old !== ( $val === false ? '0' : (string)$val )) {
                return false; // data-loss occurs
            }
        }
        return true;
    }

    /**
     * @param  string|NULL
     *
     * @return array of persistent parameters.
     */
    public function getPersistentParams($class = null)
    {

        $class = $class === null ? $this->getName() : $class; // TODO
        $params = &self::$ppCache[$class];
        if ($params !== null) {
            return $params;
        }
        $params = array();
        if (is_subclass_of($class, 'Nette\Application\UI\PresenterComponent')) {
            $defaults = get_class_vars($class);
            foreach (/**/
                $class::getPersistentParams()/**//*5.2*call_user_func(array($class, 'getPersistentParams'), $class)*/ as $name => $meta) {
                if (is_string($meta)) {
                    $name = $meta;
                }
                $params[$name] = array(
                    'def'   => $defaults[$name],
                    'since' => $class,
                );
            }
            foreach ($this->getPersistentParams(get_parent_class($class)) as $name => $param) {
                if (isset( $params[$name] )) {
                    $params[$name]['since'] = $param['since'];
                    continue;
                }

                $params[$name] = $param;
            }
        }
        return $params;
    }

    /**
     * @param  string|NULL
     *
     * @return array of persistent components.
     */
    public function getPersistentComponents($class = null)
    {

        $class = $class === null ? $this->getName() : $class;
        $components = &self::$pcCache[$class];
        if ($components !== null) {
            return $components;
        }
        $components = array();
        if (is_subclass_of($class, 'Nette\Application\UI\Presenter')) {
            foreach (/**/
                $class::getPersistentComponents()/**//*5.2*call_user_func(array($class, 'getPersistentComponents'), $class)*/ as $name => $meta) {
                if (is_string($meta)) {
                    $name = $meta;
                }
                $components[$name] = array('since' => $class);
            }
            $components = $this->getPersistentComponents(get_parent_class($class)) + $components;
        }
        return $components;
    }

    /**
     * Is a method callable? It means class is instantiable and method has
     * public visibility, is non-static and non-abstract.
     *
     * @param  string  method name
     *
     * @return bool
     */
    public function hasCallableMethod($method)
    {

        $class = $this->getName();
        $cache = &self::$mcCache[strtolower($class.':'.$method)];
        if ($cache === null) {
            try {
                $cache = false;
                $rm = Nette\Reflection\Method::from($class, $method);
                $cache = $this->isInstantiable() && $rm->isPublic() && !$rm->isAbstract() && !$rm->isStatic();
            } catch (\ReflectionException $e) {
            }
        }
        return $cache;
    }

}
