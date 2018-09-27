<?php
namespace SPHERE\System\Extension\Repository\Sorter;

use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class StringGermanOrderSorter
 *
 * @package SPHERE\System\Extension\Repository\Sorter
 */
class StringGermanOrderSorter extends AbstractSorter
{

    /**
     * @param string  $Property Entity-Attribute (Getter)
     *
     * @param Element $First
     * @param Element $Second
     *
     * @return int -1,0,1
     */
    public function sortAsc($Property, Element $First, Element $Second)
    {

        if ($this->isSortable($Property, $First, $Second)) {

            $FirstString = $this->prepareString($this->getValue($Property, $First));
            $SecondString = $this->prepareString($this->getValue($Property, $Second));

            return ( $FirstString == $SecondString ) ? 0 : ( $FirstString > $SecondString ) ? 1 : -1;
        }
        return 0;
    }

    /**
     * @param $String
     *
     * @return string
     */
    private function prepareString($String)
    {

        $IsUmlautWithE = true;
        if(($tblSetting = Consumer::useService()->getSetting('Setting', 'Consumer', 'Service', 'Sort_UmlautWithE'))){
            $IsUmlautWithE = $tblSetting->getValue();
        }
        $IsSortWithShortWords = true;
        if(($tblSetting = Consumer::useService()->getSetting('Setting', 'Consumer', 'Service', 'Sort_WithShortWords'))){
            $IsSortWithShortWords = $tblSetting->getValue();
        }

        $Cut = array(
            "der",
            "die",
            "das",
            "den",
            "dem",
            "des",
            "ein",
            "eine",
            "einen",
            "einem",
            "eines",
            "the",
            "a",
            "an",
            "la",
            "le",
            "les",
            "un",
            "une",
            "des",
            "l'",
            "von",
            "de"
        );

        $String = strtolower($String);
        if($IsUmlautWithE){
            $String = preg_replace(array('!ä!iu', '!ö!iu', '!ü!iu', '!ß!iu'), array('ae', 'oe', 'ue', 'ss'), $String);
        } else {
            $String = preg_replace(array('!ä!iu', '!ö!iu', '!ü!iu', '!ß!iu'), array('a', 'o', 'u', 'ss'), $String);
        }

        if(!$IsSortWithShortWords){
            array_walk($Cut,function(&$V){
                $V = '!^'.preg_quote($V, '!').'\s+!i';
            });
            $String = preg_replace( $Cut, '', $String );
        }
        return $String;
    }

    /**
     * @param string  $Property Entity-Attribute (Getter)
     *
     * @param Element $First
     * @param Element $Second
     *
     * @return int -1,0,1
     */
    public function sortDesc($Property, Element $First, Element $Second)
    {

        if ($this->isSortable($Property, $First, $Second)) {

            $FirstString = $this->prepareString($this->getValue($Property, $First));
            $SecondString = $this->prepareString($this->getValue($Property, $Second));

            return ( $FirstString == $SecondString ) ? 0 : ( $FirstString > $SecondString ) ? -1 : 1;
        }
        return 0;
    }
}
