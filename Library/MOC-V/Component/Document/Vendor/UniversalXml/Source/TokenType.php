<?php
namespace MOC\V\Component\Document\Vendor\UniversalXml\Source;

/**
 * Class TokenType
 *
 * @package MOC\V\Component\Document\Vendor\UniversalXml\Source
 */
abstract class TokenType
{

    const TYPE_OPEN = 1;
    const TYPE_CLOSE = 2;
    const TYPE_SHORT = 3;
    const TYPE_CDATA = 4;
    const TYPE_COMMENT = 5;
    protected $Type = 0;

    /**
     * @return bool
     */
    public function isOpenTag()
    {

        return $this->Type == self::TYPE_OPEN;
    }

    /**
     * @return bool
     */
    public function isCloseTag()
    {

        return $this->Type == self::TYPE_CLOSE;
    }

    /**
     * @return bool
     */
    public function isShortTag()
    {

        return $this->Type == self::TYPE_SHORT;
    }

    /**
     * @return bool
     */
    public function isCDATATag()
    {

        return $this->Type == self::TYPE_CDATA;
    }

    /**
     * @return bool
     */
    public function isCommentTag()
    {

        return $this->Type == self::TYPE_COMMENT;
    }
}
