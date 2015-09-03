<?php
namespace MOC\V\Component\Router\Component\Parameter\Repository;

use MOC\V\Component\Router\Component\Exception\ComponentException;
use MOC\V\Component\Router\Component\IParameterInterface;
use MOC\V\Component\Router\Component\Parameter\Parameter;

/**
 * Class RouteParameter
 *
 * @package MOC\V\Component\Router\Component\Parameter\Repository
 */
class RouteParameter extends Parameter implements IParameterInterface
{

    /** @var null|string $Path */
    private $Path = null;
    /** @var null|string $Controller */
    private $Controller = null;
    /** @var array $ParameterDefault */
    private $ParameterDefault = array();
    /** @var array $ParameterPattern */
    private $ParameterPattern = array();

    /**
     * @param string $Path
     * @param string $Controller
     */
    public function __construct($Path, $Controller)
    {

        $this->setPath($Path);
        $this->setController($Controller);
    }

    /**
     * @param null|string $Path
     */
    private function setPath($Path)
    {

        $this->Path = $Path;
    }

    /**
     * @param null|string $Controller
     *
     * @throws ComponentException
     */
    private function setController($Controller)
    {

        if (false === strpos($Controller, '::')) {
            throw new ComponentException($Controller);
        }
        $this->Controller = $Controller;
    }

    /**
     * @return string
     */
    public function getClass()
    {

        $List = explode('::', $this->getController(), 2);
        return current($List);
    }

    /**
     * @return null|string
     */
    public function getController()
    {

        return $this->Controller;
    }

    /**
     * @return string
     */
    public function getMethod()
    {

        $List = explode('::', $this->getController(), 2);
        return end($List);
    }

    /**
     * @param null|string $Name
     *
     * @return array|mixed
     */
    public function getParameterDefault($Name = null)
    {

        if (null === $Name) {
            return (array)$this->ParameterDefault;
        } else {
            return $this->ParameterDefault[$Name];
        }
    }

    /**
     * @param string $Name
     * @param mixed  $Value
     *
     * @return RouteParameter
     */
    public function setParameterDefault($Name, $Value)
    {

        $this->ParameterDefault[$Name] = $Value;
        return $this;
    }

    /**
     * @return array
     */
    public function getParameterPattern()
    {

        return $this->ParameterPattern;
    }

    /**
     * @param string $Name
     * @param string $Pattern
     */
    public function setParameterPattern($Name, $Pattern)
    {

        $this->ParameterPattern[$Name] = $Pattern;
    }

    /**
     * @return null|string
     */
    public function getPath()
    {

        return $this->Path;
    }

}
