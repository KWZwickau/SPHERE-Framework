<?php
namespace MOC\V\Component\Document\Vendor\UniversalXml\Source;

/**
 * Class Node
 *
 * @package MOC\V\Component\Document\Vendor\UniversalXml\Source
 */
class Node extends NodeType
{

    /** @var null|int $Position */
    private $Position = null;
    /** @var null|Node $Parent */
    private $Parent = null;
    /** @var Node[] $ChildList */
    private $ChildList = array();

    private $Name = null;
    private $Content = null;
    private $AttributeList = array();

    /**
     * @param Token|null $Token
     */
    function __construct(Token $Token = null)
    {

        if (null !== $Token) {
            $this->Name = $Token->getName();
            $this->AttributeList = $Token->getAttributeList();
            $this->Position = $Token->getPosition();
            unset( $Token );
        }
    }

    /**
     * @return int|null
     */
    public function getPosition()
    {

        return $this->Position;
    }

    /**
     * @return null|Node
     */
    public function getParent()
    {

        return $this->Parent;
    }

    /**
     * @param Node $Value
     *
     * @return Node
     */
    public function setParent(Node $Value)
    {

        $this->Parent = $Value;

        return $this;
    }

    /**
     * @param      $Name
     * @param null $AttributeList
     * @param null $Index
     * @param bool $Recursive
     * @param bool $NameIsRegExp
     * @param bool $AttributeIsFuzzy
     *
     * @return bool|Node
     */
    public function getChild(
        $Name,
        $AttributeList = null,
        $Index = null,
        $Recursive = true,
        $NameIsRegExp = false,
        $AttributeIsFuzzy = false
    ) {

        /** @var Node $Node */
        foreach ($this->ChildList as $Node) {
            if ($Node->getName() == $Name || ( $NameIsRegExp && preg_match($Name, $Node->getName()) )) {
                if ($AttributeList === null && $Index === null) {
                    return $Node;
                } else {
                    if ($Index === null) {
                        if ($Node->getAttributeList() == $AttributeList) {
                            return $Node;
                        }
                        if ($AttributeIsFuzzy) {
                            $Fuzzy = true;
                            $Haystack = $Node->getAttributeList();
                            foreach ((array)$AttributeList as $Key => $Value) {
                                if (!isset( $Haystack[$Key] ) || $Haystack[$Key] != $Value) {
                                    $Fuzzy = false;
                                }
                            }
                            if ($Fuzzy) {
                                return $Node;
                            }
                        }
                    } else {
                        if ($AttributeList === null) {
                            if ($Index === 0) {
                                return $Node;
                            } else {
                                $Index--;
                            }
                        } else {
                            if ($Node->getAttributeList() == $AttributeList && $Index === 0) {
                                return $Node;
                            } else {
                                if ($Node->getAttributeList() == $AttributeList) {
                                    $Index--;
                                }
                            }
                        }
                    }
                }
            }
            if (true === $Recursive && !empty( $Node->ChildList )) {
                if (false !== ( $Result = $Node->getChild($Name, $AttributeList, $Index, $Recursive, $NameIsRegExp) )
                ) {
                    if (!is_object($Result)) {
                        $Index = $Result;
                    } else {
                        return $Result;
                    }
                }
            }
        }
        if ($Index !== null) {
            return $Index;
        } else {
            return false;
        }
    }

    /**
     * @return null
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @param $Value
     *
     * @return Node
     */
    public function setName($Value)
    {

        $this->Name = $Value;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributeList()
    {

        return $this->AttributeList;
    }

    /**
     * @return int
     */
    public function getChildListCount()
    {

        return count($this->getChildList());
    }

    /**
     * @return Node[]
     */
    public function getChildList()
    {

        return $this->ChildList;
    }

    /**
     * @param Node[] $NodeList
     *
     * @return Node
     */
    public function setChildList($NodeList)
    {

        $this->ChildList = array();
        array_walk($NodeList, function (Node $Node, $Index, Node $Self) {

            $Self->addChild($Node);
        }, $this);

        return $this;
    }

    /**
     * @param Node      $Node
     * @param null|Node $After
     *
     * @return Node
     */
    public function addChild(Node $Node, Node $After = null)
    {

        if ($After === null) {
            $Node->setParent($this);
            array_push($this->ChildList, $Node);
            $this->Content = null;
            $this->setType(self::TYPE_STRUCTURE);
        } else {
            $this->injectChild($Node, $After);
        }

        return $this;
    }

    /**
     * @param Node $Inject
     * @param Node $After
     */
    private function injectChild(Node $Inject, Node $After)
    {

        $Index = array_search($After, $this->ChildList) + 1;
        $Left = array_slice($this->ChildList, 0, $Index, true);
        $Right = array_slice($this->ChildList, $Index, null, true);
        $this->setChildList(array_merge($Left, array($Inject), $Right));
    }

    /**
     * @param $Name
     *
     * @return null
     */
    public function getAttribute($Name)
    {

        if (isset( $this->AttributeList[$Name] )) {
            return $this->AttributeList[$Name];
        } else {
            return null;
        }
    }

    /**
     * @param string     $Name
     * @param null|mixed $Value
     *
     * @return Node
     */
    public function setAttribute($Name, $Value = null)
    {

        if ($Value === null) {
            unset( $this->AttributeList[$Name] );
        } else {
            $this->AttributeList[$Name] = $Value;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {

        $FuncArgs = func_get_args();
        if (empty( $FuncArgs )) {
            $FuncArgs[0] = false;
            $FuncArgs[1] = 0;
        }
        // BUILD STRUCTURE STRING
        $Result = ''
            .( !$FuncArgs[0] ? '<?xml version="1.0" encoding="utf-8" standalone="yes"?>'."\n" : "\n" )
            .str_repeat("\t", $FuncArgs[1]);
        if ($this->getType() == self::TYPE_COMMENT) {
            $Result .= '<!-- '.$this->getContent().' //-->';
        } else {
            $Result .= '<'.trim($this->getName().' '.$this->getAttributeString());
        }
        if ($this->getContent() === null && empty( $this->ChildList )) {
            $Result .= ' />';
        } else {
            if ($this->getType() == self::TYPE_CONTENT) {
                $Result .= '>'.$this->getContent().'</'.$this->getName().'>';
            } else {
                if ($this->getType() == self::TYPE_CDATA) {
                    $Result .= '><![CDATA['.$this->getContent().']]></'.$this->getName().'>';
                } else {
                    if ($this->getType() == self::TYPE_STRUCTURE) {
                        $Result .= '>';
                        /** @var Node $Node */
                        foreach ($this->ChildList as $Node) {
                            $Result .= $Node->getCode(true, $FuncArgs[1] + 1);
                        }
                        $Result .= "\n".str_repeat("\t", $FuncArgs[1]).'</'.$this->getName().'>';
                    }
                }
            }
        }

        // RETURN STRUCTURE
        return $Result;
    }

    /**
     * @return null
     */
    public function getContent()
    {

        return $this->Content;
    }

    /**
     * @param null $Value
     *
     * @return Node
     */
    public function setContent($Value = null)
    {

        if (preg_match('![<>&]!is', $Value)) {
            $this->setType(self::TYPE_CDATA);
        } else {
            $this->setType(self::TYPE_CONTENT);
        }
        if (strlen($Value) == 0) {
            $this->Content = null;
        } else {
            $this->Content = $Value;
        }
        $this->ChildList = array();

        return $this;
    }

    /**
     * @return string
     */
    public function getAttributeString()
    {

        $AttributeList = $this->AttributeList;
        array_walk($AttributeList, create_function('&$Value,$Key', '$Value = $Key.\'="\'.$Value.\'"\';'));

        return implode(' ', $AttributeList);
    }

    public function __destruct()
    {

        /** @var Node $Node */
        unset( $this->Parent );
        array_walk($this->ChildList, function (Node $Node) {

            $Node->__destruct();
        });
        unset( $this );
    }
}
