<?php
namespace SPHERE\Application\Reporting\Gateway\Converter;

use SPHERE\Application\Reporting\Gateway\Fragment\AbstractFragment;

/**
 * Class Output
 *
 * @package SPHERE\Application\Reporting\Gateway\Converter
 */
class Output
{

    /** @var \DOMDocument $Document */
    private static $Document = null;
    /** @var AbstractFragment[] $FragmentList */
    protected $FragmentList = array();

    /**
     * Output constructor.
     */
    public function __construct()
    {

        self::$Document = new \DOMDocument('1.0', 'utf-8');
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

        $Root = self::$Document->createElement('import');
        self::$Document->appendChild( $Root );
        /** @var AbstractFragment $Fragment */
        foreach( (array)$this->FragmentList as $Fragment ) {
            $Root->appendChild( $Fragment->getXmlNode() );
        }
        return self::getDocument()->saveXML();
    }

    public static function getDocument()
    {

        return self::$Document;
    }
}
