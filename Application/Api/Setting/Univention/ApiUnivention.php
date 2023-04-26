<?php
namespace SPHERE\Application\Api\Setting\Univention;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Setting\Univention\UniventionUser;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;

/**
 * Class ApiUnivention
 * @package SPHERE\Application\Api\Setting\Univention
 */
class ApiUnivention implements IApiInterface
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
        $Dispatcher->registerMethod('serviceUser');
        $Dispatcher->registerMethod('reloadLoad');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     * @param int    $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverUser(string $Content = '', int $Identifier = 0): BlockReceiver
    {

        return (new BlockReceiver($Content))->setIdentifier('User'.$Identifier);
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
        $Emitter = new ServerEmitter($Receiver, ApiUnivention::getEndpoint());
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
     * @param string $UserList
     * @param string $ApiType
     * @param int    $CountMax
     *
     * @return Pipeline
     */
    public static function pipelineServiceUser(int $Identifier , string $UserList = '', string $ApiType = '', int $CountMax = 1): Pipeline
    {


        $Receiver = self::receiverUser('', $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiUnivention::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'serviceUser'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'UserList'   => $UserList,
            'ApiType'    => $ApiType,
            'CountMax'   => $CountMax,
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
                new Bold('<div style="position: absolute; left: 43%; font-size: 16px;"> '.$CountMax.' User an die API gesendet </div>')
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
    public function serviceUser(int $Identifier = 0, string $UserList = '', string $ApiType = '', int $CountMax = 1): string
    {

        // avoid max_input_vars
        $UserList = json_decode($UserList, true);
        if(!empty($UserList)){
            $User = array_shift($UserList);
            $UserString = $User['name'].': '.$User['firstname'].' '.$User['lastname'];

            // create with API
            if($ApiType == 'Create'){
                // school_classes leeres Array muss erneut gesetzt werden
                if(!isset($User['school_classes'])){
                    $User['school_classes'] = array();
                }
                $Error = (new UniventionUser())->createUser($User['name'], $User['email'],
                    $User['firstname'], $User['lastname'], $User['record_uid'],
                    $User['roles'], $User['schools'], $User['school_classes'],
                    $User['recoveryMail']);
                if($Error){
                    $UserString = new Panel($User['name'].' '.new Small(new Muted('('.$User['firstname'].' '.$User['lastname'].')')),
                        $Error, Panel::PANEL_TYPE_DANGER);
                } else {
                    $UserString = new Panel(new SuccessIcon().' '.$User['name'].' '.new Small(new Muted('('.$User['firstname'].' '.$User['lastname'].') ')),
                        '', Panel::PANEL_TYPE_SUCCESS);
                }
            }

            // update with API
            if($ApiType == 'Update'){
                // school_classes leeres Array muss erneut gesetzt werden
                if(!isset($User['school_classes'])){
                    $User['school_classes'] = array();
                }
                $Error = (new UniventionUser())->updateUser($User['name'], $User['email'],
                    $User['firstname'], $User['lastname'], $User['record_uid'],
                    $User['roles'], $User['schools'], $User['school_classes'],
                    $User['recoveryMail']);
//                $Error .= '<pre>'.print_r($User, true).'</pre>';
                if($Error){
                    $UserString = new Panel($User['name'].' '.new Small(new Muted('('.$User['firstname'].' '.$User['lastname'].')')),
                        $Error, Panel::PANEL_TYPE_DANGER);
                } else {
                    $UserString = new Panel(new SuccessIcon().' '.$User['name'].' '.new Small(new Muted('('.$User['firstname'].' '.$User['lastname'].') ')),
                        '', Panel::PANEL_TYPE_SUCCESS);
                }
            }

            // update with API
            if($ApiType == 'Delete'){
                $Error = (new UniventionUser())->deleteUser($User['name']);
                if($Error){
                    $UserString = new Panel($User['name'].' '.new Small(new Muted('('.$User['firstname'].' '.$User['lastname'].')')),
                        $Error, Panel::PANEL_TYPE_DANGER);
                } else {
                    $UserString = new Panel(new SuccessIcon().' '.$User['name'].' '.new Small(new Muted('('.$User['firstname'].' '.$User['lastname'].') ')),
                        '', Panel::PANEL_TYPE_SUCCESS);
                }
            }

            $Identifier++;
            $UserList = json_encode($UserList);
            return $UserString
                .self::pipelineLoad($Identifier, $CountMax)
                .self::pipelineServiceUser($Identifier, $UserList, $ApiType, $CountMax);
//                .self::receiverUser(self::pipelineServiceUser($Identifier, $UserList, $ApiType, $CountMax), $Identifier);
        }
        return '';
    }

}