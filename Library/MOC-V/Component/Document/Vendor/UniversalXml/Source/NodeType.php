<?php
namespace MOC\V\Component\Document\Vendor\UniversalXml\Source;

/**
 * Class NodeType
 *
 * @package MOC\V\Component\Document\Vendor\UniversalXml\Source
 */
abstract class NodeType
{

    const TYPE_STRUCTURE = 1;
    const TYPE_CONTENT = 2;
    const TYPE_CDATA = 3;
    const TYPE_COMMENT = 4;

    /** @var int $Type */
    private $Type = self::TYPE_CONTENT;

    /**
     * @return int
     */
    public function getType()
    {

        return $this->Type;
    }

    /**
     * @param int $Value
     *
     * @return Node
     */
    public function setType($Value)
    {

        $this->Type = $Value;

        return $this;
    }
}
