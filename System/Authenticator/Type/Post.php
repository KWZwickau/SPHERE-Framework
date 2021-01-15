<?php
namespace SPHERE\System\Authenticator\Type;

use SPHERE\System\Authenticator\ITypeInterface;
use SPHERE\System\Debugger\Logger\ErrorLogger;
use SPHERE\System\Extension\Extension;

/**
 * Class Post
 *
 * @package SPHERE\System\Authenticator\Type
 */
class Post extends Extension implements ITypeInterface
{

    /** @var string $Secret */
    private $Secret = '';

    /**
     * @param array $Configuration
     */
    public function setConfiguration($Configuration)
    {

        $this->Secret = $Configuration['Secret'];
    }

    /**
     * @return string
     */
    public function getConfiguration()
    {

        return 'Post';
    }

    /**
     * @return bool|null
     */
    public function validateSignature()
    {

        $Global = $this->getGlobal();

//        $ListWithHTML = array();
//
//        if(!empty($Global->POST)){
//            foreach($Global->POST as $Key => &$Value) {
//                if('htmlEnabledSpecific_Tags' === $Key && !empty($Value)){
//                    foreach($Value as $FieldName) {
//                        $ListWithHTML[] = $FieldName;
//                    }
//                }
//            }
//
//            foreach($Global->POST as $Key => &$Value) {
//                if(in_array($Key, $ListWithHTML)){
//                    $Value = $this->preventXSSExtendWithHtml($Value);
//                } else {
//                    if(!empty($Value)){
//                        if(is_string($Value)){
//                            $Value = array('0' => $Value);
//                            $isString = true;
//                        }
//                        array_walk_recursive($Value, array($this, 'preventXSS'));
//
//                        if(isset($isString)){
//                            $Value = implode('', $Value);
//                        }
//                    }
//                }
//            }
//        }

        if(isset($Global->POST['htmlEnabledSpecific_Tags'])) {

//            foreach($Global->POST as $D0Key => &$dimension){
//                if(is_string($dimension)){
//                    $dimension = $this->exceptionDecision($dimension, $D0Key, $ListWithHTML);
//                } elseif(is_array($dimension)){
//                    foreach($dimension as $D1Key => &$dimensionOne){
//                        if(is_string($dimensionOne)){
//                            $dimensionOne = $this->exceptionDecision($dimensionOne, $D1Key, $ListWithHTML);
//                        } elseif(is_array($dimensionOne)){
//                            foreach($dimensionOne as $D2Key => &$dimensionTwo){
//                                if(is_string($dimensionTwo)){
//                                    $dimensionTwo = $this->exceptionDecision($dimensionTwo, $D2Key, $ListWithHTML);
//                                } else{
//                                    // Post array aktuell nur mit 2 ebenen erlaubt
//                                }
//                            }
//                        }
//                    }
//                }
//            }
//
//            $noDepth = array();
//            $firstDepth = array();
//            $secondDepth = array();
            // beinhaltet ausnahmen
            foreach($Global->POST['htmlEnabledSpecific_Tags'] as $Key => $ArrayString){
                $ArrayString = str_replace(']', '', $ArrayString);
                $AsArray = preg_split('/\[/', $ArrayString);
                    // dreidimensionales array
                if(isset($AsArray[0]) && isset($AsArray[1]) && isset($AsArray[2])){
                    $one = $AsArray[0];
                    $two = $AsArray[1];
                    $three = $AsArray[2];
                    $Global->POST['htmlEnabledSpecific_Tags'][$one][$two][$three] = 1;
                    // zweidimensionales array
                } elseif(isset($AsArray[0]) && isset($AsArray[1])){
                    $one = $AsArray[0];
                    $two = $AsArray[1];
                    $Global->POST['htmlEnabledSpecific_Tags'][$one][$two] = 1;
                    // eindimensionales array
                } elseif(isset($AsArray[0])){
                    $one = $AsArray[0];
                    $Global->POST['htmlEnabledSpecific_Tags'][$one] = 1;
                }
            }


//            $fieldSet = array_flip($Global->POST['htmlEnabledSpecific_Tags']);
            // feld1 => 0
            // feld2 => 1
            // Ausnahmen separiert

            $postWithHtmlField = array_intersect_key($Global->POST, $Global->POST['htmlEnabledSpecific_Tags']);
            array_walk_recursive($postWithHtmlField, array($this, 'preventXSSHtml'));
            // Ausnahmen aus dem Array entfernen
            $fieldSet['htmlEnabledSpecific_Tags'] = true;
            // regulär separiert
            $postWithoutHtmlField = array_diff_key($Global->POST, $fieldSet);
            array_walk_recursive($postWithoutHtmlField, array($this, 'preventXSS'));
            // array zusammenführen
            $Global->POST = $postWithHtmlField + $postWithoutHtmlField;
        } else {
            array_walk_recursive($Global->POST, array($this, 'preventXSS'));
        }
        array_walk_recursive($Global->POST, array($this, 'trimInput'));
        $Global->savePost();

        return true;
    }

    /**
     * @param array       $Data
     * @param null|string $Location
     *
     * @return array
     */
    public function createSignature($Data, $Location = null)
    {

        // MUST NOT USE
        $this->getLogger(new ErrorLogger())
            ->addLog(__METHOD__.' Error: SIGNATURE - MUST NOT BE USED!');
        return array();
    }

    /**
     * @param $Value
     */
    protected function trimInput(&$Value)
    {

        $Value = trim($Value);
    }

    /**
     * @param $Value
     */
    protected function preventXSS(&$Value)
    {

        $Value = strip_tags($Value);
    }
//
    /**
     * @param $Value
     */
    protected function preventXSSHtml(&$Value)
    {

        $Value = strip_tags($Value, '<p><strong><em><br><span><li><ul>');
    }

//    private function exceptionDecision($Value, $Key, $List)
//    {
//
//        if(in_array($Key, $List)){
//            $Value = $this->preventXSSExtendHtml($Value);
//        } else {
//            $Value = $this->preventXSSExtend($Value);
//        }
//        return $Value;
//
//    }
//
//    /**
//     * @param $Value
//     */
//    protected function preventXSSExtend($Value)
//    {
//
//        return strip_tags($Value);
//    }
//
//    /**
//     * @param $Value
//     */
//    protected function preventXSSExtendHtml($Value)
//    {
//
//        return strip_tags($Value, '<p><strong><em><br><span><li><ul>');
//    }
}
