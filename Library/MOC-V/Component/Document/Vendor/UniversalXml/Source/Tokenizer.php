<?php
namespace MOC\V\Component\Document\Vendor\UniversalXml\Source;

/**
 * Class Tokenizer
 *
 * @package MOC\V\Component\Document\Vendor\UniversalXml\Source
 */
class Tokenizer extends Mask
{

    private $Content = '';
    private $PatternToken = '!(?<=<)[^?][^<>]*?(?=>)!is';
    private $Result = array();

    /**
     * @param string $Content
     */
    function __construct($Content)
    {

        $this->Content = $Content;
        $this->Content = $this->encodePayload($this->Content, self::PATTERN_COMMENT);
        $this->Content = $this->encodePayload($this->Content, self::PATTERN_CDATA);
        preg_match_all($this->PatternToken, $this->Content, $this->Result, PREG_OFFSET_CAPTURE);

        $this->Result = array_map(array($this, 'buildToken'), $this->Result[0]);
    }

    /**
     * @return array
     */
    public function getResult()
    {

        return $this->Result;
    }

    /**
     * @return string
     */
    public function getContent()
    {

        return $this->Content;
    }

    /**
     * @param array $Content
     *
     * @return Token
     */
    private function buildToken($Content)
    {

        return new Token($Content);
    }

}
