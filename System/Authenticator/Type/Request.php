<?php
namespace SPHERE\System\Authenticator\Type;

use SPHERE\System\Authenticator\ITypeInterface;
use SPHERE\System\Debugger\Logger\ErrorLogger;
use SPHERE\System\Extension\Extension;

/**
 * Class Request
 *
 * @package SPHERE\System\Authenticator\Type
 */
class Request extends Extension implements ITypeInterface
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

        return 'Request';
    }

    /**
     * @return bool|null
     */
    public function validateSignature()
    {

        $Global = $this->getGlobal();
        if(isset($Global->REQUEST['htmlEnabledSpecific_Tags'])) {
//            // beinhaltet ausnahmen
            foreach($Global->REQUEST['htmlEnabledSpecific_Tags'] as $Key => $ArrayString){
                $ArrayString = str_replace(']', '', $ArrayString);
                $AsArray = preg_split('/\[/', $ArrayString);
                    // dreidimensionales array
                if(isset($AsArray[0]) && isset($AsArray[1]) && isset($AsArray[2])){
                    $one = $AsArray[0];
                    $two = $AsArray[1];
                    $three = $AsArray[2];
                    $Global->REQUEST['htmlEnabledSpecific_Tags'][$one][$two][$three] = 1;
                    // zweidimensionales array
                } elseif(isset($AsArray[0]) && isset($AsArray[1])){
                    $one = $AsArray[0];
                    $two = $AsArray[1];
                    $Global->REQUEST['htmlEnabledSpecific_Tags'][$one][$two] = 1;
                    // eindimensionales array
                } elseif(isset($AsArray[0])){
                    $one = $AsArray[0];
                    $Global->REQUEST['htmlEnabledSpecific_Tags'][$one] = 1;
                }
            }
//            $fieldSet = array_flip($Global->REQUEST['htmlEnabledSpecific_Tags']);
//            // feld1 => 0
//            // feld2 => 1

            // Ausnahmen separiert
            $requestWithHtmlField = array_intersect_key($Global->REQUEST, $Global->REQUEST['htmlEnabledSpecific_Tags']);
            array_walk_recursive($requestWithHtmlField, array($this, 'preventXSSHtml'));
            // Ausnahmen Auflistung aus dem Array entfernen
            $fieldSet['htmlEnabledSpecific_Tags'] = true;
            // regulär separiert
            $requestWithoutHtmlField = array_diff_key($Global->REQUEST, $fieldSet);
            array_walk_recursive($requestWithoutHtmlField, array($this, 'preventXSS'));
            // array zusammenführen
            $Global->REQUEST = $requestWithHtmlField + $requestWithoutHtmlField;
        } else {
            array_walk_recursive($Global->REQUEST, array($this, 'preventXSS'));
        }
        array_walk_recursive($Global->REQUEST, array($this, 'trimInput'));
        $Global->saveRequest();

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

    /**
     * @param $Value
     */
    protected function preventXSSHtml(&$Value)
    {

        $Value = strip_tags($Value, '<p><strong><em><br><span><li><ul>');
    }
}
