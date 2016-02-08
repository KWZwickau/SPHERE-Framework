<?php
namespace MOC\V\Component\Document\Vendor\UniversalXml\Source;

/**
 * Class Parser
 *
 * @package MOC\V\Component\Document\Vendor\UniversalXml\Source
 */
class Parser extends Mask
{

    /** @var array $Stack */
    private $Stack = array();
    /** @var null|Node $Result */
    private $Result = null;

    /**
     * @param Tokenizer $Tokenizer
     */
    function __construct(Tokenizer $Tokenizer)
    {

        /** @var Token $Token */
        foreach ((array)$Tokenizer->getResult() as $Token) {
            // Convert Token to Node
            $Node = new Node($Token);
            // Handle Token by Type
            if ($Token->isOpenTag()) {
                $this->processOpen($Node);
            } elseif ($Token->isCloseTag()) {
                // Get Parent (OpenTag)
                /** @var Node $Parent */
                $Parent = array_pop($this->Stack);
                // Handle Close by Type
                switch ($Parent->getType()) {
                    case $Parent::TYPE_CONTENT : {
                        $this->processCloseContent($Parent, $Tokenizer, $Token);
                        break;
                    }
                    case $Parent::TYPE_STRUCTURE : {
                        $this->processCloseStructure($Parent);
                        break;
                    }
                    case $Parent::TYPE_CDATA : {
                        $this->processCloseCDATA($Parent);
                        break;
                    }
                }
            } elseif ($Token->isShortTag()) {
                $this->processShort($Node);
            } elseif ($Token->isCDATATag()) {
                $this->processCDATA($Node);
            } elseif ($Token->isCommentTag()) {
                $this->processComment($Node);
            }
        }
        // Set parsed Stack as Result
        $this->Result = array_pop($this->Stack);
    }

    /**
     * @param Node $Node
     */
    private function processOpen(Node $Node)
    {

        // Set Parent Type to Structure
        if (!empty( $this->Stack )) {
            $Parent = array_pop($this->Stack);
            $Parent->setType($Parent::TYPE_STRUCTURE);
            array_push($this->Stack, $Parent);
        }
        // Add Node to Stack
        array_push($this->Stack, $Node);
    }

    /**
     * @param Node      $Parent
     * @param Tokenizer $Tokenizer
     * @param Token     $Token
     */
    private function processCloseContent(Node $Parent, Tokenizer $Tokenizer, Token $Token)
    {

        // Get Content
        $LengthName = strlen($Parent->getName()) + 1;
        $LengthAttribute = strlen($Parent->getAttributeString()) + 1;
        $LengthAttribute = ( $LengthAttribute == 1 ? 0 : $LengthAttribute );
        $Parent->setContent(
            substr(
                $Tokenizer->getContent(),

                $Parent->getPosition()
                + $LengthName
                + $LengthAttribute,

                ( $Token->getPosition() - $Parent->getPosition() )
                - ( $LengthName + 1 )
                - ( $LengthAttribute )
            )
        );
        // Do Parent Close
        $Ancestor = array_pop($this->Stack);
        $Ancestor->addChild($Parent);
        array_push($this->Stack, $Ancestor);
    }

    /**
     * @param Node $Parent
     */
    private function processCloseStructure(Node $Parent)
    {

        // Set Ancestor <-> Parent Relation
        /** @var Node $Ancestor */
        $Ancestor = array_pop($this->Stack);
        if (is_object($Ancestor)) {
            // Do Parent Close
            $Ancestor->addChild($Parent);
            array_push($this->Stack, $Ancestor);
        } else {
            // No Ancestor -> Parent = Root
            array_push($this->Stack, $Parent);
        }
    }

    /**
     * @param Node $Parent
     */
    private function processCloseCDATA(Node $Parent)
    {

        // Set Ancestor <-> Parent Relation
        /** @var Node $Ancestor */
        $Ancestor = array_pop($this->Stack);
        // Do Parent Close
        $Ancestor->addChild($Parent);
        array_push($this->Stack, $Ancestor);
    }

    /**
     * @param Node $Node
     */
    private function processShort(Node $Node)
    {

        // Set Ancestor <-> Node Relation
        /** @var Node $Parent */
        $Ancestor = array_pop($this->Stack);
        $Ancestor->setType($Ancestor::TYPE_STRUCTURE);
        // Do Node Close
        $Ancestor->addChild($Node);
        array_push($this->Stack, $Ancestor);
    }

    /**
     * @param Node $Node
     */
    private function processCDATA(Node $Node)
    {

        // Set Parent Type/Content
        /** @var Node $Parent */
        $Parent = array_pop($this->Stack);
        $Parent->setType($Parent::TYPE_CDATA);
        $Parent->setContent($Node->getName());
        $this->decodePayload($Parent, self::PATTERN_CDATA);
        // Do Node Close
        array_push($this->Stack, $Parent);
    }

    /**
     * @param Node $Node
     */
    private function processComment(Node $Node)
    {

        // Set Parent Type/Content
        /** @var Node $Parent */
        $Parent = array_pop($this->Stack);
        $Node->setType($Node::TYPE_COMMENT);
        $Node->setContent($Node->getName());
        $Node->setName('__COMMENT__');
        $this->decodePayload($Node, self::PATTERN_COMMENT);
        // Do Node Close
        $Parent->addChild($Node);
        array_push($this->Stack, $Parent);
    }

    /**
     * @return Node|null
     */
    public function getResult()
    {

        return $this->Result;
    }
}
