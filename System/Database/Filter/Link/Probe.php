<?php
namespace SPHERE\System\Database\Filter\Link;

use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Probe
 *
 * @package SPHERE\System\Database\Filter\Link
 */
class Probe
{

    const LOGIC_AND = 0;
    const LOGIC_OR = 1;
    /** @var null|AbstractService $Service */
    protected $Service = null;
    /** @var string $GetterAll */
    protected $GetterAll = '';
    /** @var string $GetterId */
    protected $GetterId = '';

    /**
     * Probe constructor.
     *
     * @param AbstractService $Service
     * @param string          $GetterMethodAll
     * @param string          $GetterMethodId
     *
     * @throws \Exception
     */
    public function __construct(AbstractService $Service, $GetterMethodAll, $GetterMethodId)
    {

        $this->setupService($Service);
        $this->setupGetterAll($GetterMethodAll);
        $this->setupGetterId($GetterMethodId);
    }

    /**
     * @param AbstractService $Service
     *
     * @return $this
     * @throws \Exception
     */
    private function setupService(AbstractService $Service)
    {

        if ($this->isValidService($Service)) {
            $this->Service = $Service;
        } else {
            throw new \Exception('Invalid Service '.get_class($Service));
        }
        return $this;
    }

    /**
     * @param string|object $Class
     *
     * @return bool
     */
    private function isValidService($Class)
    {

        $Service = new \ReflectionClass($Class);
        return $Service->isSubclassOf('\SPHERE\System\Database\Binding\AbstractService');
    }

    /**
     * @param string $Method
     *
     * @return $this
     * @throws \Exception
     */
    private function setupGetterAll($Method)
    {

        if ($this->isValidGetter($Method)) {
            $this->GetterAll = $Method;
        } else {
            throw new \Exception('Invalid Getter "'.$Method.'" @'.get_class($this->Service));
        }
        return $this;
    }

    /**
     * @param string $Method
     *
     * @return bool
     */
    private function isValidGetter($Method)
    {

        $Service = new \ReflectionClass($this->Service);
        return $Service->hasMethod($Method);
    }

    /**
     * @param string $Method
     *
     * @return $this
     * @throws \Exception
     */
    private function setupGetterId($Method)
    {

        if ($this->isValidGetter($Method)) {
            $this->GetterId = $Method;
        } else {
            throw new \Exception('Invalid Getter "'.$Method.'" @'.get_class($this->Service));
        }
        return $this;
    }

    /**
     * @param int   $Id
     * @param array $Search array( 'Property' => 'Value' )
     * @param int   $Logic  LOGIC_AND|LOGIC_OR
     *
     * @return bool|Element
     * @throws \Exception
     */
    public function findId($Id, $Search, $Logic = self::LOGIC_AND)
    {

        $Search = $this->sanitizeSearch($Logic, $Search);
        $Entity = $this->getId($Id);
        if (!empty( $Search ) && !empty( $Entity )) {
            if (!$this->isValidEntity($Entity, $Search)) {
                $Entity = false;
            }
        }
        if (empty( $Entity )) {
            return false;
        }
        return $Entity;
    }

    /**
     * @param int    $Logic
     * @param array  $Search
     * @param string $Delimiter
     *
     * @return array|bool
     */
    private function sanitizeSearch($Logic, $Search, $Delimiter = '!')
    {

        if ($this->isLogicSearch($Search)) {
            Debugger::screenDump('Logic-Search');
            foreach ($Search as $Logic => $Criteria) {
                switch ($Logic) {
                    case self::LOGIC_AND:
                        $Search[$Logic] = $this->sanitizeSearchAND($Criteria, $Delimiter);
                        break;
                    case self::LOGIC_OR:
                        $Search[$Logic] = $this->sanitizeSearchOR($Criteria, $Delimiter);
                        break;
                    default:
                        $Search[$Logic] = $this->sanitizeSearchAND($Criteria, $Delimiter);
                }
            }
        } else {

            Debugger::screenDump('Simple-Search');
            switch ($Logic) {
                case self::LOGIC_AND:
                    $Search = $this->sanitizeSearchAND($Search, $Delimiter);
                    break;
                case self::LOGIC_OR:
                    $Search = $this->sanitizeSearchOR($Search, $Delimiter);
                    break;
                default:
                    $Search = $this->sanitizeSearchAND($Search, $Delimiter);
            }
        }
        return $Search;
    }

    /**
     * @param array $Search
     *
     * @return bool
     */
    private function isLogicSearch($Search)
    {

        $KeyList = array_keys($Search);
        sort($KeyList);
        return ( $KeyList === range(0, count($Search) - 1) );
    }

    /**
     * @param array  $Search
     * @param string $Delimiter
     *
     * @return bool|array
     */
    private function sanitizeSearchAND($Search, $Delimiter = '!')
    {

        if (empty( $Search )) {
            return false;
        } else {
            array_walk($Search, function (&$Value) use ($Delimiter) {

                if (empty( $Value )) {
                    $Value = '(.*)';
                } else {
                    $WordList = explode(' ', $Value);
                    array_walk($WordList, function (&$Item) use ($Delimiter) {

                        if (empty( $Item )) {
                            $Item = false;
                        } else {
                            $Item = preg_quote($Item, $Delimiter);
                        }
                    });
                    $WordList = array_filter($WordList);
                    if (empty( $WordList )) {
                        $Value = '(.*)';
                    } else {
                        $Value = '(?=.*'.implode(')(?=.*', $WordList).')';
                    }
                }
            });
        }
        return $Search;
    }

    /**
     * @param array  $Search
     * @param string $Delimiter
     *
     * @return bool|array
     */
    private function sanitizeSearchOR($Search, $Delimiter = '!')
    {

        if (empty( $Search )) {
            return false;
        } else {
            array_walk($Search, function (&$Value) use ($Delimiter) {

                if (empty( $Value )) {
                    $Value = '(.*)';
                } else {
                    $WordList = explode(' ', $Value);
                    array_walk($WordList, function (&$Item) use ($Delimiter) {

                        if (empty( $Item )) {
                            $Item = false;
                        } else {
                            $Item = preg_quote($Item, $Delimiter);
                        }
                    });
                    $WordList = array_filter($WordList);
                    if (empty( $WordList )) {
                        $Value = '(.*)';
                    } else {
                        $Value = '((?=.*'.implode(')|(?=.*', $WordList).')){1}';
                    }
                }
            });
        }
        return $Search;
    }

    /**
     * @param int $Id
     *
     * @return bool|Element
     */
    public function getId($Id)
    {

        return $this->Service->{$this->GetterId}($Id);
    }

    /**
     * @param Element $Entity
     * @param array   $Search
     *
     * @return bool
     * @throws \Exception
     */
    private function isValidEntity(Element $Entity, $Search)
    {

        if ($this->isLogicSearch($Search)) {

            foreach ($Search as $SearchPattern) {
                foreach ($SearchPattern as $Property => $Value) {
                    if (method_exists($Entity, 'get'.$Property)) {
                        $Check = $Entity->{'get'.$Property}();
//                        Debugger::screenDump('!'.$Value.'!is'.' '.$Check.' '.preg_match('!'.$Value.'!is', $Check));
                        if (!preg_match('!'.$Value.'!is', $Check)) {
                            return false;
                        }
                    } else {
                        throw new \Exception('Invalid Property '.$Property.' in '.get_class($Entity));
                    }
                }
            }
        } else {

            foreach ($Search as $Property => $Value) {
                if (method_exists($Entity, 'get'.$Property)) {
                    $Check = $Entity->{'get'.$Property}();
//                    Debugger::screenDump('!'.$Value.'!is'.' '.$Check.' '.preg_match('!'.$Value.'!is', $Check));
                    if (!preg_match('!'.$Value.'!is', $Check)) {
                        return false;
                    }
                } else {
                    throw new \Exception('Invalid Property '.$Property.' in '.get_class($Entity));
                }
            }
        }
        return true;
    }

    /**
     * @param array $Search array( 'Property' => 'Value' )
     * @param int   $Logic  LOGIC_AND|LOGIC_OR
     *
     * @return bool|Element[]
     * @throws \Exception
     */
    public function findAll($Search, $Logic = self::LOGIC_AND)
    {

        $Search = $this->sanitizeSearch($Logic, $Search);
        $EntityList = $this->getAll();
        if (!empty( $Search ) && !empty( $EntityList )) {
            array_walk($EntityList, function (Element &$Entity) use ($Search) {

                if (!$this->isValidEntity($Entity, $Search)) {
                    $Entity = false;
                }
            });
            $EntityList = array_filter($EntityList);
        }
        if (empty( $EntityList )) {
            return false;
        }
        return $EntityList;
    }

    /**
     * @return bool|Element[]
     */
    public function getAll()
    {

        return $this->Service->{$this->GetterAll}();
    }
}
