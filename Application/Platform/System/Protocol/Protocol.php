<?php
namespace SPHERE\Application\Platform\System\Protocol;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\System\Protocol\Service\Entity\TblProtocol;
use SPHERE\Application\Setting\MyAccount\MyAccount;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Protocol
 *
 * @package SPHERE\Application\System\Platform\Protocol
 */
class Protocol implements IModuleInterface
{

    private string $MarkColor = 'yellow';

    public static function registerModule()
    {

        /**
         * Register Navigation
         */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Protokoll'), new Link\Icon(new Listing()))
        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                'Protocol::frontendProtocol'
            )
        );
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {

    }

    /**
     * @param null|array $Filter
     *
     * @return Stage
     * @throws \Exception
     */
    public function frontendProtocol($Filter = null)
    {

        ini_set('memory_limit', '2G');
        require_once( __DIR__.'/Difference/finediff.php' );

        $Stage = new Stage('Protokoll', 'Aktivitäten');

        $Form = new Form(new FormGroup(new FormRow(array(
            new FormColumn(
                new Panel('Metadaten', array(
//                    new SelectBox('Filter[ProtocolDatabase]', 'Datenbank',
//                        array_merge( array( 0 => '' ),
//                            array_combine(
//                                Protocol::useService()->getProtocolDatabaseNameList(),
//                                Protocol::useService()->getProtocolDatabaseNameList()
//                            )
//                        )
//                    ),
                    new AutoCompleter('Filter[ProtocolDatabase]', 'Datenbank', '',
                                Protocol::useService()->getProtocolDatabaseNameList()
                    ),
                    new TextField('Filter[EntityCreate]', 'Timestamp', 'Timestamp'),
                ), Panel::PANEL_TYPE_INFO)
                , 4),
            new FormColumn(
                new Panel('Metadaten', array(
                    new TextField('Filter[ConsumerAcronym]', 'Mandant-Kürzel', 'Mandant-Kürzel'),
                    new TextField('Filter[ConsumerName]', 'Mandant-Name', 'Mandant-Name'),
                    new TextField('Filter[AccountUsername]', 'Benutzerkonto', 'Benutzerkonto'),
                ), Panel::PANEL_TYPE_INFO)
                , 4),
            new FormColumn(
                new Panel('Payload', array(
                    new TextField('Filter[EntityFrom]', 'Daten-Original', 'Daten-Original'),
                    new TextField('Filter[EntityTo]', 'Daten-Ergebnis', 'Daten-Ergebnis'),
//                    new \SPHERE\Common\Frontend\Message\Repository\Info('Id suche ohne Leerzeichen begrenzt möglich (z.B. "Id=500")')
                ), Panel::PANEL_TYPE_INFO)
                , 4)
        ))), new Primary('Suchen'));

        // standard color theme
        $style = '<style>del {background-color: #FFA0A0;} ins {background-color: #A0FFA0;} pre {font-size: 10px;}</style>';
        if(($tblAccount = Account::useService()->getAccountBySession())){
            if(($SettingSurface = MyAccount::useService()->getSettingByAccount($tblAccount, 'Surface'))){
                if($SettingSurface->getValue() == 3){
                    $this->MarkColor = '#805d03';
                    $style = '<style>del {background-color: #6c1717;} ins {background-color: #105c10;} pre {font-size: 10px;}</style>';
                }
            }
        }

        $Message = array();
        if (!empty( $Filter )) {
            array_walk($Filter, function (&$Input) {
                if (!empty( $Input )) {
                    $Input = explode(' ', $Input);
                    foreach ($Input as &$SearchString) {
                        if (preg_match('!([^\s]+)=([^\s]+)!is', $SearchString, $SearchArray)) {
                            $SearchString = $SearchArray[1].'";s:'.strlen($SearchArray[2]).':"'.$SearchArray[2];
                            // Test intager
//                             $SearchValueType = 's:'.strlen($SearchArray[2]).':"'; // string
//                             // #fix linked (service)tables save as integer not like the Id as string
//                             // and delete close " but this is necessary for finding
//                             if(strpos($SearchArray[1], "service") !== false){
//                                 $SearchValueType = 'i:'; // integer
//                             }
//                             $SearchString = $SearchArray[1].'";'.$SearchValueType.$SearchArray[2];
                        }
                    }
                    $Input = array_filter($Input);
                } else {
                    $Input = false;
                }
            });
            $Filter = array_filter($Filter);
        }
        if (!empty( $Filter )) {
            $Result = (new Pile())
                ->addPile(Protocol::useService(), new TblProtocol(), null, 'Id')
                ->searchPile(array(
                    $Filter
                ));
            foreach ($Result as $Index => $Payload) {
                $Result[$Index] = current($Payload)->__toArray();
            }
            foreach ($Result as $Index => $Payload) {

                $tableName = '';
                if($Result[$Index]['EntityFrom']){
                    $startPosition = strpos($Result[$Index]['EntityFrom'], 'Entity\\') + 7;
                    $endPosition = strpos($Result[$Index]['EntityFrom'], '"', $startPosition);
                    $tableName = substr($Result[$Index]['EntityFrom'], $startPosition, $endPosition - $startPosition);
                } elseif($Result[$Index]['EntityTo']){
                    $startPosition = strpos($Result[$Index]['EntityTo'], 'Entity\\') + 7;
                    $endPosition = strpos($Result[$Index]['EntityTo'], '"', $startPosition);
                    $tableName = substr($Result[$Index]['EntityTo'], $startPosition, $endPosition - $startPosition);
                }

                $Result[$Index]['Meta'] = new \SPHERE\Common\Frontend\Layout\Repository\Listing(array(
                    $this->markFilter($Payload, $Filter, 'AccountUsername'),
                    $this->markFilter($Payload, $Filter, 'ProtocolDatabase'),
                    $tableName,
                    $this->markFilter($Payload, $Filter, 'ConsumerAcronym'),
                    $this->markFilter($Payload, $Filter, 'ConsumerName'),
                ));

                $Result[$Index]['EntityFrom'] = $this->convertObject($Payload['EntityFrom']);
                $Result[$Index]['EntityTo'] = $this->convertObject($Payload['EntityTo']);

                if ($Result[$Index]['EntityFrom'] instanceof Danger && $Result[$Index]['EntityTo'] instanceof Danger) {
                    $OpCode = \FineDiff::getDiffOpcodes(
                        '', '', \FineDiff::$characterGranularity
                    );
                } else {
                    if ($Result[$Index]['EntityFrom'] instanceof Danger) {
                        $OpCode = \FineDiff::getDiffOpcodes(
                            '', $Result[$Index]['EntityTo'], \FineDiff::$characterGranularity
                        );
                    } else {
                        if ($Result[$Index]['EntityTo'] instanceof Danger) {
                            $OpCode = \FineDiff::getDiffOpcodes(
                                $Result[$Index]['EntityFrom'], '', \FineDiff::$characterGranularity
                            );
                        } else {
                            $OpCode = \FineDiff::getDiffOpcodes(
                                $Result[$Index]['EntityFrom'], $Result[$Index]['EntityTo'],
                                \FineDiff::$characterGranularity
                            );
                        }
                    }
                }

                $Result[$Index]['EntityDiff'] = ( '<pre>'.\FineDiff::renderDiffToHTMLFromOpcodes($Result[$Index]['EntityFrom'],
                        $OpCode).'</pre>' );

                $Result[$Index]['EntityCreate'] = $this->markFilter($Result[$Index], $Filter, 'EntityCreate');

                $Result[$Index]['EntityFrom'] = $this->markFilter($Result[$Index], $Filter, 'EntityFrom');
                $Result[$Index]['EntityFrom'] = ( '<pre>'.$Result[$Index]['EntityFrom'].'</pre>' );
                $Result[$Index]['EntityTo'] = $this->markFilter($Result[$Index], $Filter, 'EntityTo');
                $Result[$Index]['EntityTo'] = ( '<pre>'.$Result[$Index]['EntityTo'].'</pre>' );
            }

        } else {
            $Result = array();
            $Message[] = new Warning('Bitte Daten filtern');
        }

        $Stage->setContent(
            $style.
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well($Form)
                        )
                    )
                    , new Title('Suche')),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            implode(' ', $Message),
                            new TableData($Result, null, array(
                                'EntityCreate' => 'Timestamp',
                                'Meta'         => 'Meta',
                                'EntityFrom'   => 'Daten-Original',
                                'EntityDiff'   => 'Daten-Änderung',
                                'EntityTo'     => 'Daten-Ergebnis',
                            ), array(
                                'responsive' => false,
                                'order' => array(
                                    array(0, 'desc')
                                ),
                                'columnDefs' => array(
                                    array('type' => 'de_datetime', 'targets' => 0),
                                    array('width' => '25%', 'targets' => array(2, 3, 4))
                                )
                            ))
                        ))
                    )
                    , new Title('Ergebnis')),
            ))
        );

        return $Stage;
    }

    /**
     * @return \SPHERE\Application\Platform\System\Protocol\Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Platform', 'System', 'Protocol'),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @param array  $Payload
     * @param array  $Search
     * @param string $Name
     *
     * @return string
     */
    private function markFilter($Payload, $Search, $Name)
    {

        if (isset( $Search[$Name] )) {
            if (!empty( $Search[$Name] )) {
                array_walk($Search[$Name], function (&$Text) {
//                    // #fix integer save problem -> on " is missing for correct coloring
//                    if(str_contains($Text, 'service') !== false){
//                        $positionValue = strpos($Text, ':', 0);
//                        $NameField = substr($Text, 0, $positionValue+1);
//                        $ValueField = substr($Text, $positionValue+1);
//                        $Text = $NameField.'"'.$ValueField;
//                    }
                    // mark the search with "="
                    $FilterArray = explode('"', $Text);
                    if (isset($FilterArray[0]) && isset($FilterArray[2])) {
                        $Text = $FilterArray[0].'] => '.$FilterArray[2];
                    }
                    $Text = '!'.preg_quote(trim($Text), '!').'!is';
                });
                return preg_replace($Search[$Name], '<span style="background-color: '.$this->MarkColor.';">${0}</span>',
                    $Payload[$Name]);
            }
        }
        return $Payload[$Name];
    }

    /**
     * @param string $Content
     *
     * @return Danger|string
     */
    private function convertObject($Content)
    {

        if (preg_match('!^O:[0-9]+:"([a-z0-9\\\]+)":.*?$!is', ($Content ?? ''), $Match)) {
            if (class_exists($Match[1], true)) {
                $Object = unserialize($Content);
                if (method_exists($Object, '__toArray')) {
                    $Array = $Object->__toArray();
                    if (isset( $Array['BinaryBlob'] )) {
                        $Array['BinaryBlob'] = (string)new Info('BINARY');
                    }
                    $Return = (string)print_r($Array, true);
                    return $Return;
                }
                return new Danger('NO STRUCTURE AVAILABLE');
            } else {
                return new Danger('NO STRUCTURE AVAILABLE');
            }
        }
        if (empty( $Content )) {
            return new Danger('NO DATA AVAILABLE');
        }
        return (string)$Content;
    }
}
