<?php
namespace MOC\V\Component\Document\Vendor\UniversalXml\Source;

/**
 * Class Mask
 *
 * @package MOC\V\Component\Document\Vendor\UniversalXml\Source
 */
abstract class Mask
{

    const PATTERN_COMMENT = '!(?<=\!--).*?(?=//-->)!is';
    const PATTERN_CDATA = '!(?<=\!\[CDATA\[).*?(?=\]\]>)!is';

    /**
     * @param string $Payload
     * @param string $Pattern
     *
     * @return mixed
     */
    protected function encodePayload($Payload, $Pattern)
    {

        if ($Pattern == self::PATTERN_COMMENT) {
            return preg_replace($Pattern, '', $Payload);
        } else {
            return preg_replace_callback($Pattern, array($this, 'encodeMethod'), $Payload);
        }
    }

    /**
     * @param Node   $Node
     * @param string $Pattern
     */
    protected function decodePayload(Node &$Node, $Pattern)
    {

        $Match = array();
        preg_match($Pattern, $Node->getContent(), $Match);
        $Node->setContent($this->decodeMethod(empty( $Match ) ? '' : $Match[0]));
    }

    /**
     * @param $Payload
     *
     * @return string
     */
    private function decodeMethod($Payload)
    {

        return base64_decode($Payload);
    }

    /**
     * @param $Payload
     *
     * @return string
     */
    private function encodeMethod($Payload)
    {

        return base64_encode($Payload[0]);
    }
}
