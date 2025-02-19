<?php

namespace SPHERE\Application\Api\People\Person;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Api\People\Search\ApiPersonSearch;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\ToggleSelective;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiPersonDelete
 *
 * @package SPHERE\Application\Api\Group\ApiPersonDelete
 */
class ApiPersonDelete extends Extension implements IApiInterface
{

    use ApiTrait;

    const API_GROUP_AMOUNT = 20;
    const API_FORM_LIMIT = 998;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('loadDeleteGuardContent');
        $Dispatcher->registerMethod('showLoadContent');
        $Dispatcher->registerMethod('serviceDeleteGuardContent');
        $Dispatcher->registerMethod('serviceReloadTable');

        return $Dispatcher->callMethod($Method);
    }

//    /**
//     * @param string $Content
//     * @param string $Identifier
//     *
//     * @return BlockReceiver
//     */
//    public static function receiverBlock($Content = '', $Identifier = '')
//    {
//        return (new BlockReceiver($Content))->setIdentifier($Identifier);
//    }

    /**
     * @param string $Identifier
     *
     * @return InlineReceiver
     */
    public static function receiverService($Identifier = '')
    {
        return (new InlineReceiver(''))->setIdentifier($Identifier);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal(): ModalReceiver
    {
        return (new ModalReceiver())->setIdentifier('ModalReceiver');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineClose(): Pipeline
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());

        return $Pipeline;
    }

    /**
     * @param int $GroupId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteGuardModal($GroupId)
    {
        $pipeline = new Pipeline();

        $emitter = new ServerEmitter(ApiPersonDelete::receiverModal(), ApiPersonDelete::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonDelete::API_TARGET => 'loadDeleteGuardContent',
        ));
        $emitter->setPostPayload(array(
            'GroupId' => $GroupId
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param int $GroupId
     *
     * @return Pipeline
     */
    public static function pipelineLoadModal($GroupId)
    {
        $pipeline = new Pipeline();

        $emitter = new ServerEmitter(ApiPersonDelete::receiverModal(), ApiPersonDelete::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonDelete::API_TARGET => 'showLoadContent',
        ));
        $emitter->setPostPayload(array(
            'GroupId' => $GroupId
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param int $GroupId
     *
     * @return Pipeline
     */
    public static function pipelineReloadModal($PersonReduce = array(), $GroupId = '', $InitialCount = '')
    {
        $pipeline = new Pipeline();

        $emitter = new ServerEmitter(ApiPersonDelete::receiverModal(), ApiPersonDelete::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonDelete::API_TARGET => 'showLoadContent',
        ));
        $emitter->setPostPayload(array(
            'PersonReduce' => $PersonReduce,
            'GroupId' => $GroupId,
            'InitialCount' => $InitialCount,
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineSaveDeleteGuardModal($PersonReduce = array(), $GroupId = '', $InitialCount = '')
    {
        $pipeline = new Pipeline();

        $emitter = new ServerEmitter(ApiPersonDelete::receiverService('load'), ApiPersonDelete::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonDelete::API_TARGET => 'serviceDeleteGuardContent',
        ));
        $emitter->setPostPayload(array(
            'PersonReduce' => $PersonReduce,
            'GroupId' => $GroupId,
            'InitialCount' => $InitialCount
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineReloadTable($GroupId = '')
    {
        $pipeline = new Pipeline();

        $emitter = new ServerEmitter(ApiPersonDelete::receiverModal(), ApiPersonDelete::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonDelete::API_TARGET => 'serviceReloadTable',
        ));
        $emitter->setPostPayload(array(
            'GroupId' => $GroupId
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param string $GroupId
     *
     * @return string
     */
    public function loadDeleteGuardContent(string $GroupId): string
    {

        $tblGroup = Group::useService()->getGroupById($GroupId);
        $tblPersonReduceList = array();
        if($tblGroup && ($tblPersonList = $tblGroup->getPersonList())){
            $tblRelationshipType = Relationship::useService()->getTypeById(TblType::IDENTIFIER_GUARDIAN);
            foreach($tblPersonList as $tblPerson){
                $activePerson = false;
                if(($tblPersonChildList = Relationship::useService()->getPersonChildByPerson($tblPerson))){
                    foreach($tblPersonChildList as $tblPersonChild){
                        // Aktueller Schulverlauf
                        if(DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPersonChild)){
                            $activePerson = true;
                            break;
                        }
                    }
                }
                if(!$activePerson){
                    $tblPersonReduceList[] = $tblPerson;
                }
            }
        }
        // Tabellen Ansicht
        $TableContent = array();
        $TableAll = array();
        $TableSelect1 = array();
        $TableSelect2 = array();
        $TableSelectMore = array();
        $isDeletableInTable = false;
        $CountLimit = $CountLimit1 = $CountLimit2 = $CountLimit3 = 0;
        if($tblPersonReduceList){
            array_walk($tblPersonReduceList, function(TblPerson $tblPersonReduce) use (&$TableContent, &$TableAll,
                &$TableSelect1, &$TableSelect2, &$TableSelectMore, &$isDeletableInTable,
                &$CountLimit, &$CountLimit1, &$CountLimit2, &$CountLimit3,
            ){
                $CountLimit++;
                $item = array();
//                $item['Selectbox'] = new CheckBox('PersonReduce[]', ' ', $tblPersonReduce->getId());
                $item['Selectbox'] = new CheckBox('PersonReduce['.$tblPersonReduce->getId().']', ' ', $tblPersonReduce->getId());
                $item['EntityCreate'] = $tblPersonReduce->getEntityCreate()->format('d.m.Y');
                $item['Name'] = '<span hidden>'.$tblPersonReduce->getLastName().'</span>'.$tblPersonReduce->getFullName();
                $item['Child'] = '';
                $item['Group'] = '';
                $item['ActiveAccount'] = new Center(new Check());

                if($tblAddress = $tblPersonReduce->fetchMainAddress()){
                    $item['Name'] .= '&nbsp;'.new ToolTip(new InfoIcon(), $tblAddress->getGuiString());
                }
                if(($tblPersonChildList = Relationship::useService()->getPersonChildByPerson($tblPersonReduce))){
                    $Child = array();
                    foreach($tblPersonChildList as $tblPersonChild){
                        $Child[] = $tblPersonChild->getFirstName().' '.$tblPersonChild->getLastName();
                    }
                    if(!empty($Child)){
                        $item['Child'] = implode('<br/>', $Child);
                    }
                }
                if(($tblGroupList = Group::useService()->getGroupAllByPerson($tblPersonReduce))){
                    $GroupList = array();
                    foreach($tblGroupList as $tblGroup){
                        if($tblGroup->getMetaTable() !== TblGroup::META_TABLE_COMMON){
                            $GroupList[] = $tblGroup->getName();
                        }
                    }
                    sort($GroupList);
                    if($GroupList){
                        sort($GroupList);
                        $count = count($GroupList);
                        switch($count) {
                            case 0:
                            break;
                            case 1:
                                $CountLimit1++;
                                if($CountLimit1 <= self::API_FORM_LIMIT) {
                                    $TableSelect1[] = 'PersonReduce[' . $tblPersonReduce->getId() . ']';
                                }
                                if($CountLimit <= self::API_FORM_LIMIT){
                                    $TableAll[] = 'PersonReduce['.$tblPersonReduce->getId().']';
                                }
                            break;
                            case 2:
                                $CountLimit2++;
                                if($CountLimit2 <= self::API_FORM_LIMIT) {
                                    $TableSelect2[] = 'PersonReduce[' . $tblPersonReduce->getId() . ']';
                                }
                                if($CountLimit <= self::API_FORM_LIMIT){
                                    $TableAll[] = 'PersonReduce['.$tblPersonReduce->getId().']';
                                }
                            break;
                            default:
                                $CountLimit3++;
                                if($CountLimit3 <= self::API_FORM_LIMIT) {
                                    $TableSelectMore[] = 'PersonReduce[' . $tblPersonReduce->getId() . ']';
                                }
                                if($CountLimit <= self::API_FORM_LIMIT){
                                    $TableAll[] = 'PersonReduce['.$tblPersonReduce->getId().']';
                                }
                            break;
                        }
                        $item['Group'] = implode('<br/>', $GroupList);
                        $item['GroupCount'] = $count;
                    }
                }
                if(($tblAccountList = Account::useService()->getAccountAllByPerson($tblPersonReduce))){
                    // Personen mit Account können nicht gelöscht werden
                    $item['Selectbox'] = '';
                    $item['ActiveAccount'] = new Center(new DangerText(new Bold(current($tblAccountList)->getUsername())));
                    // CountLimit für nicht auswählbare Einträge wieder subtrahieren
                    $CountLimit--;
                } else {
                    // Erkennung, ob mindestens ein Eintrag gelöscht werden kann
                    $isDeletableInTable = true;
                }
                array_push($TableContent, $item);
            });
        }
        if(empty($TableContent)){
            return $this->getModalHeaderDeleteGuard()
                .new Warning('Keine Person, die den Kriterien entspricht')
                .new PullClear(new PullRight(new Close()));
        }

        $Table = new TableData($TableContent, new \SPHERE\Common\Frontend\Table\Repository\Title('Vorschläge', 'zum entfernen'),
            array(
                'Selectbox'     => 'entfernen',
                'EntityCreate'  => 'Erstellung',
                'Name'          => 'Name',
                'Child'         => 'Verknüpfte Person',
                'GroupCount'    => 'Anzahl',
                'Group'         => 'Personengruppen',
                'ActiveAccount' => 'B.-Konto'
            ), array(
                'order' => array(
                    array('1', 'desc'),
                    array('2', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'de_date', 'targets' => array(1)),
                    array('type' => 'natural', 'targets' => array(4)),
                    array('orderable' => false, 'width' => '1%', 'targets' => array(0)), // ,-1)
                ),
                "paging" => false, // Deaktiviert Blättern
                "iDisplayLength" => -1,    // Alle Einträge zeigen
                "searching" => false, // Deaktiviert Suche
//                    "info" => false, // Deaktiviert Such-Info)
//                        "responsive" => false,
            ));

        return $this->getModalHeaderDeleteGuard()
//            .new ToggleCheckbox('Alle wählen/abwählen', $Table)
            .new ToggleSelective('Alle wählen/abwählen', $TableAll)
            .(count($TableSelect1) ? new ToggleSelective('wählen/abwählen 1 Gruppe', $TableSelect1) : null)
            .(count($TableSelect2) ? new ToggleSelective('wählen/abwählen 2 Gruppen', $TableSelect2) : null)
            .(count($TableSelectMore) ? new ToggleSelective('wählen/abwählen mehr als 2 Gruppen', $TableSelectMore) : null)
            .new Form(new FormGroup(new FormRow(array(
                new FormColumn($Table),
                new FormColumn(new Danger('Hiermit werden die ausgewählten Sorgeberechtigten Personen bzw. Personendaten dauerhaft gelöscht.'
                    , null, false, '15', '0'))
            ))), ($isDeletableInTable
                ? (new \SPHERE\Common\Frontend\Link\Repository\Danger('Löschen', '#',new Remove(), array('GroupId' => $GroupId)))
                    ->ajaxPipelineOnClick(ApiPersonDelete::pipelineLoadModal($GroupId))
                : null )
            )
            .new PullClear(new PullRight(new Close()));
    }

    /**
     * @return string
     */
    private function getModalHeaderDeleteGuard()
    {
        return new Title('Automatische Erkennung', 'löschbare Sorgeberechtigte')
        .new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(new Info(new Bold('Erkennungslogik')
                .' - Sorgeberechtigte, mit Sorgerecht / Bevollmächtigt / Vollmacht von Personen ohne aktuellem Schulverlauf.'
                .new Container('Es können maximal '.new Bold(self::API_FORM_LIMIT).' Personen auf einmal entfernt werden.')
                    , null, false, '5', '8')
                , 6),
            new LayoutColumn(new Danger('Personen mit aktivem Account können nicht entfernt werden, bitte wenden Sie sich dafür an Ihren Administrator.'
                    , null, false, '5', '8')
                , 6),
        ))));
    }

    /**
     * @param $PersonReduce
     * @param $GroupId
     * @param $InitialCount
     * @return string
     */
    public function showLoadContent($PersonReduce = array(), $GroupId = '', $InitialCount = '')
    {

        // beim ersten durchlauf InitialCount bestimmen
        if(!$InitialCount){
            $InitialCount = count($PersonReduce);
        }
        $currentCount = count($PersonReduce);
        // Bestimmen der bereits abgearbeiteten Einträge
        $Done = $InitialCount - $currentCount;
        // Teilen durch 0 vermeiden
        if($Done != 0){
            $Done = $Done / $InitialCount * 100;
        }
        // Teilen durch 0 vermeiden
        if($InitialCount != 0){
            // Work immer so groß wie maximale Gruppengröße zum Löschen
            $Work = self::API_GROUP_AMOUNT / $InitialCount * 100;
        } else {
            $Work = $InitialCount;
        }

        // bei 100 oer größer wird Work nicht dargestellt -> Plan auf 0
        if($Done + $Work >= 100){
            $Plan = 0;
            $Work = 100 - $Done;
        } else {
            $Plan = 100 - ($Done + $Work);
        }


        $EndString = '';
        if(($InitialCount - $currentCount) >= $InitialCount - self::API_GROUP_AMOUNT){
            $EndString = '<div style="height: 8px;"></div>'
                .new Success($InitialCount.' Personen erfolgreich entfernt')
                . new Muted('Neuladen in 2 Sec.');
        }

        return new Title('Personen werden entfernt, bitte haben Sie etwas Geduld...')
            .(new ProgressBar($Done, $Work, $Plan))->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_WARNING)->setSize('10px')
            .$EndString
            .ApiPersonDelete::pipelineSaveDeleteGuardModal($PersonReduce, $GroupId, $InitialCount);
    }

    /**
     * @param $PersonReduce
     * @param $GroupId
     * @param $InitialCount
     * @return Pipeline|string
     */
    public function serviceDeleteGuardContent($PersonReduce = array(), $GroupId = '', $InitialCount = '')
    {

        // Todo hier partiell arbeiten.
        $WorkCount = 0;
        if(!empty($PersonReduce)){
            foreach($PersonReduce as &$PersonId){
                $WorkCount ++;
                $tblPerson = Person::useService()->getPersonById($PersonId);
                Person::useService()->destroyPerson($tblPerson);
                $PersonId = false;
                if($WorkCount >= self::API_GROUP_AMOUNT){
                    break;
                }
            }
        }
        $PersonReduce = array_filter($PersonReduce);
        if(!empty($PersonReduce)){
            return ApiPersonDelete::pipelineReloadModal($PersonReduce, $GroupId, $InitialCount);
        }

        return ApiPersonDelete::pipelineReloadTable($GroupId);
    }

    /**
     * @return string
     */
    public function serviceReloadTable($GroupId = '')
    {

        sleep(2);
        return ApiPersonSearch::pipelineLoadGroupSelectBox('G' . $GroupId)
            .ApiPersonDelete::pipelineClose();
    }
}