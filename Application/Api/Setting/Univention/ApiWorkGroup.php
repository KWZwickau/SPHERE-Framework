<?php
namespace SPHERE\Application\Api\Setting\Univention;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Univention\UniventionWorkGroup;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Bold;

/**
 * Class ApiWorkGroup
 * @package SPHERE\Application\Api\Setting\Univention
 */
class ApiWorkGroup implements IApiInterface
{

    // registered method
    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = ''): string
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('serviceWorkgroup');
        $Dispatcher->registerMethod('reloadLoad');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     * @param int    $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverWorkgroup(string $Content = '', int $Identifier = 0): BlockReceiver
    {

        return (new BlockReceiver($Content))->setIdentifier('Workgroup'.$Identifier);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverLoad($Content = ''): BlockReceiver
    {

        return (new BlockReceiver($Content))->setIdentifier('Loading');
    }

    /**
     * @param int      $Identifier
     * @param int|null $CountMax
     *
     * @return Pipeline
     */
    public static function pipelineLoad(int $Identifier = 0, ?int $CountMax = null): Pipeline
    {

        $Receiver = self::receiverLoad(null);
        $Pipeline = new Pipeline(false);
        $Emitter = new ServerEmitter($Receiver, ApiWorkGroup::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'reloadLoad'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'CountMax' => $CountMax,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param int    $Identifier
     * @param string $ContentJson
     * @param int    $CountMax
     *
     * @return Pipeline
     */
    public static function pipelineServiceWorkgroup(int $Identifier , string $ContentJson = '', int $CountMax = 1): Pipeline
    {


        $Receiver = self::receiverWorkgroup('', $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiWorkGroup::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'serviceWorkgroup'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'  => $Identifier,
            'ContentJson' => $ContentJson,
            'CountMax'    => $CountMax,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param int $Identifier
     * @param int $CountMax
     *
     * @return string
     */
    public function reloadLoad(int $Identifier, int $CountMax): string
    {
        $WaitingCount = 100 / $CountMax * ($CountMax - $Identifier);
        $ProgressCount = 100 / $CountMax;
        $DoneCount = 100 / $CountMax * ($Identifier);
        $DoneCountNatural = $Identifier;

        if($CountMax == $Identifier){
            return
                new Standard('Zurück', '/Setting/Univention/WorkGroupApi', new ChevronLeft())
                .new Bold('<div style="position: absolute; left: 43%; font-size: 16px;"> '.$CountMax.' Stammgruppen an die API gesendet </div>')
                .new ProgressBar($DoneCount, 0, 0, 20);
        }

        return new Bold('<div style="position: absolute; left: 49.2%; font-size: 16px;"> '.$DoneCountNatural.'/'.$CountMax.' </div>')
            .new ProgressBar($DoneCount, $ProgressCount, $WaitingCount, 20);
    }

    /**
     * @param int    $Identifier
     * @param string $UserList
     * @param string $ApiType
     * @param int    $CountMax
     *
     * @return string
     */
    public function serviceWorkgroup(int $Identifier = 0, string $ContentJson = '', int $CountMax = 1): string
    {

        // avoid max_input_vars
        $ContentArray = json_decode($ContentJson, true);
        if(!empty($ContentArray)){
            $GroupContent = array_shift($ContentArray);
            $GroupName = $GroupContent['Group'];
            $UserList = $GroupContent['UserList'];
            $Type = $GroupContent['Type'];
            $school = $GroupContent['School'];
            $Panel = new Panel($GroupName, 'Typ nicht zuweisbar', Panel::PANEL_TYPE_WARNING);
            if($Type == 'create'){
                $Error = (new UniventionWorkGroup())->createUserWorkgroup($GroupName, $school, $UserList);
                if($Error){
                    $Panel = new Panel($GroupName, $Error, Panel::PANEL_TYPE_DANGER);
                } else {
                    $Panel = new Panel($GroupName, 'Gruppe wurde mit '.count($UserList).' Personen angelegt.', Panel::PANEL_TYPE_SUCCESS);
                }
            } elseif($Type == 'update'){
                $Acronym = Account::useService()->getMandantAcronym();
                $Error = (new UniventionWorkGroup())->updateUserWorkgroup($GroupName, $Acronym, $UserList);
                if($Error){
                    $Panel = new Panel($GroupName, $Error, Panel::PANEL_TYPE_DANGER);
                } else {
                    $Panel = new Panel($GroupName, 'Gruppe wurde mit '.count($UserList).' Personen bearbeitet.', Panel::PANEL_TYPE_INFO);
                }
            } elseif($Type == 'ok'){
                $Panel = new Panel($GroupName, 'Gruppe identisch vorhanden ('.count($UserList).' Personen)', Panel::PANEL_TYPE_DEFAULT);
            } elseif($Type == 'canNot'){
                $Panel = new Panel($GroupName, 'Erlaubte Zeichen [a-zA-Z0-9 -] und darf nicht mit "-" anfangen oder enden', Panel::PANEL_TYPE_DANGER);
            }

            $Identifier++;
            $ContentJson = json_encode($ContentArray);
            return $Panel
                .self::pipelineLoad($Identifier, $CountMax)
                .self::pipelineServiceWorkgroup($Identifier, $ContentJson, $CountMax);
        }
        return '';
    }

}