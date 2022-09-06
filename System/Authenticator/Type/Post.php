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

        if(isset($Global->POST['htmlEnabledSpecific_Tags'])) {
            // beinhaltet ausnahmen
            foreach($Global->POST['htmlEnabledSpecific_Tags'] as $Key => $ArrayString){
                // String als Array umbauen, damit das korrekte Feld von array_intersect_key gefunden werden kann
                // array_flip funktioniert bei einem mehrstufigen Array (≠ String) nicht
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
            // Ausnahmen separiert

            $postWithHtmlField = array_intersect_key($Global->POST, $Global->POST['htmlEnabledSpecific_Tags']);
            array_walk_recursive($postWithHtmlField, array($this, 'preventXSSAllowSpecific'));
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
    protected function preventXSSAllowSpecific(&$Value)
    {

        $Value = strip_tags($Value, '<p><strong><em><br><span><li><ul>');
    }
}
