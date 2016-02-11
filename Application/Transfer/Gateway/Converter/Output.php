<?php
namespace SPHERE\Application\Transfer\Gateway\Converter;

use SPHERE\Application\Transfer\Gateway\Fragment\AbstractFragment;

/**
 * Class Output
 *
 * @package SPHERE\Application\Transfer\Gateway\Converter
 */
class Output
{

    /** @var \DOMElement $Root */
    private static $Root = null;
    /** @var \DOMDocument $Document */
    private static $Document = null;
    /** @var AbstractFragment[] $FragmentList */
    protected $FragmentList = array();

    /**
     * Output constructor.
     *
     * @param string $Version Current running KREDA-Version (e.g. 1.0.0)
     */
    public function __construct( $Version = '0.0.0' )
    {

        self::$Document = new \DOMDocument('1.0', 'utf-8');
        self::$Document->formatOutput = true;
        self::$Root = self::$Document->createElement('Import');
        self::$Root->setAttribute( 'Version', $Version );
        self::$Document->appendChild( self::$Root );

    }

    /**
     * @param AbstractFragment $Fragment
     *
     * @return $this
     */
    public function addFragment(AbstractFragment $Fragment)
    {

        array_push( $this->FragmentList, $Fragment );
        return $this;
    }

    public function getXml()
    {

        /** @var AbstractFragment $Fragment */
        foreach( (array)$this->FragmentList as $Fragment ) {
            self::$Root->appendChild( $Fragment->getXmlNode() );
        }
        return self::getDocument()->saveXML();
    }

    public static function getDocument()
    {

        return self::$Document;
    }
}
