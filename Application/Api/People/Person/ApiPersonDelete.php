<?php
namespace SPHERE\Application\Api\People\Person;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Api\People\Search\ApiPersonSearch;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element\Ruler;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
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
use SPHERE\Common\Frontend\Link\Repository\Primary;
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

        $Dispatcher->registerMethod('showInitialLoadContent');
        $Dispatcher->registerMethod('loadDeleteGuardContent');
        $Dispatcher->registerMethod('showLoadContent');
        $Dispatcher->registerMethod('getFilter');
        $Dispatcher->registerMethod('getFilterAdd');
        $Dispatcher->registerMethod('serviceDeleteGuardContent');
        $Dispatcher->registerMethod('serviceReloadTable');

        $Dispatcher->registerMethod('getCustodyTable');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverBlock($Content = '', $Identifier = '')
    {
        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

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
        return (new ModalReceiver(null, null, false))->setIdentifier('ModalReceiver');
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
     * @return Pipeline
     */
    public static function pipelineOpenDeleteGuardModal()
    {
        $pipeline = new Pipeline();

        $emitter = new ServerEmitter(ApiPersonDelete::receiverModal(), ApiPersonDelete::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonDelete::API_TARGET => 'showInitialLoadContent',
        ));
        $pipeline->appendEmitter($emitter);

        $emitter = new ServerEmitter(ApiPersonDelete::receiverModal(), ApiPersonDelete::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonDelete::API_TARGET => 'loadDeleteGuardContent',
        ));
//        $emitter->setPostPayload(array(
//            'GroupId' => $GroupId
//        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param $number
     * @return Pipeline
     */
    public static function pipelineAddFilter($number)
    {
        $pipeline = new Pipeline();

        $emitter = new ServerEmitter(ApiPersonDelete::receiverService('Filter'.$number), ApiPersonDelete::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonDelete::API_TARGET => 'getFilter',
        ));
        $emitter->setPostPayload(array(
            'number' => $number
        ));
        if($number == 3){
            // Gruppen korrekt vorauswählen
            $GroupStaffId = $GroupClubId = 0;
            if(($tblGroupStaff = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STAFF))){
                $GroupStaffId = $tblGroupStaff->getId();
            }
            if(($tblGroupClub = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CLUB))){
                $GroupClubId = $tblGroupClub->getId();
            }

            $emitter->setPostPayload(array(
                'number' => $number,
                'GroupList[1]' => $GroupStaffId,
                'GroupList[2]' => $GroupClubId
            ));
        }
        $pipeline->appendEmitter($emitter);

        $emitter = new ServerEmitter(ApiPersonDelete::receiverService('Filter'.++$number), ApiPersonDelete::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonDelete::API_TARGET => 'getFilterAdd',
        ));
        $emitter->setPostPayload(array(
            'number' => $number
        ));
        $pipeline->appendEmitter($emitter);
        return $pipeline;
    }

    public static function pipelineFilter()
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(ApiPersonDelete::receiverBlock('', 'CustodyTable'), ApiPersonDelete::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonDelete::API_TARGET => 'showInitialLoadContent',
        ));
        $pipeline->appendEmitter($emitter);

        $emitter = new ServerEmitter(ApiPersonDelete::receiverBlock('', 'CustodyTable'), ApiPersonDelete::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonDelete::API_TARGET => 'getCustodyTable',
        ));
        $pipeline->appendEmitter($emitter);
        return $pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadModal()
    {
        $pipeline = new Pipeline();

        $emitter = new ServerEmitter(ApiPersonDelete::receiverModal(), ApiPersonDelete::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonDelete::API_TARGET => 'showLoadContent',
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineReloadModal($PersonReduce = array(), $InitialCount = '')
    {
        $pipeline = new Pipeline();

        $emitter = new ServerEmitter(ApiPersonDelete::receiverModal(), ApiPersonDelete::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonDelete::API_TARGET => 'showLoadContent',
        ));
        $emitter->setPostPayload(array(
            'PersonReduce' => $PersonReduce,
//            'GroupId' => $GroupId,
            'InitialCount' => $InitialCount,
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineSaveDeleteGuardModal($PersonReduce = array(), $InitialCount = '')
    {
        $pipeline = new Pipeline();

        $emitter = new ServerEmitter(ApiPersonDelete::receiverService('load'), ApiPersonDelete::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonDelete::API_TARGET => 'serviceDeleteGuardContent',
        ));
        $emitter->setPostPayload(array(
            'PersonReduce' => $PersonReduce,
//            'GroupId' => $GroupId,
            'InitialCount' => $InitialCount
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineReloadTable()
    {
        $pipeline = new Pipeline();

        $emitter = new ServerEmitter(ApiPersonDelete::receiverModal(), ApiPersonDelete::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonDelete::API_TARGET => 'serviceReloadTable',
        ));
//        $emitter->setPostPayload(array(
//            'GroupId' => $GroupId
//        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @return string
     */
    public function showInitialLoadContent()
    {

        return new Title('Lädt Daten, bitte haben Sie etwas Geduld...')
            .(new ProgressBar(0, 100, 0))->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_SUCCESS)->setSize('10px');
    }

    /**
     * @return string
     */
    public function loadDeleteGuardContent(): string
    {

        // filter placement (receiver)
        for($i = 3; $i <= 8; $i++) {
            $FormColumnArray[] = new FormColumn(self::receiverService('Filter'.$i), ($i==3?9:3));
        }
        $FormColumnArray[] = new FormColumn((new Primary('Filter', '#', new Filter()))->ajaxPipelineOnClick(ApiPersonDelete::pipelineFilter()));

        // TableContent
        // Gruppen beim Initial laden mit berücksichtigen
        $tblGroupIdList = array();
        if(($tblGroupStaff = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STAFF))){
            $tblGroupIdList[] = $tblGroupStaff->getId();
        }
        if(($tblGroupClub = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CLUB))){
            $tblGroupIdList[] = $tblGroupClub->getId();
        }
        $Receiver = ApiPersonDelete::receiverBlock($this->getCustodyTable($tblGroupIdList), 'CustodyTable');

        return $this->getModalHeaderDeleteGuard()
            .ApiPersonDelete::pipelineAddFilter(3)
            .new Form(new FormGroup(new FormRow(
                $FormColumnArray
            )))
            .new Ruler()
            .$Receiver
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
                .new Container(new DangerText('Es können maximal '.new Bold(self::API_FORM_LIMIT).' Personen auf einmal entfernt werden.'))
                    , null, false, '5', '8')
                , 6),
            new LayoutColumn(new Danger('Personen mit aktivem Account können nicht entfernt werden, bitte wenden Sie sich dafür an Ihren Administrator.'
                    , null, false, '5', '8')
                , 6),
        ))));
    }

    /**
     * @param array $GroupList
     * @return string
     */
    public function getCustodyTable(array $GroupList = array())
    {

        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CUSTODY);
        $tblPersonReduceList = array();
        if($tblGroup && ($tblPersonList = $tblGroup->getPersonList())){
            foreach($tblPersonList as $tblPerson){
                // Person überspringen, wenn diese in einer der "exkludierten" Gruppen ist.
                if(!empty($GroupList)){
                    $isInGroup = false;
                    foreach($GroupList as $GroupId){
                        if(($tblGroupTemp = Group::useService()->getGroupbyId($GroupId))) {
                            if(Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroupTemp)){
                                $isInGroup = true;
                                break;
                            }
                        }
                    }
                    if($isInGroup){
                        continue;
                    }
                }



                $activePerson = false;
                if(($tblPersonChildList = Relationship::useService()->getPersonChildByPerson($tblPerson))){
                    foreach($tblPersonChildList as $tblPersonChild){
                        // Aktueller Schulverlauf
//                        if(DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPersonChild)){
                        // Aktuelle Schulverlauf "mit Abgängern in diesem Schuljahr"
                        if(DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPersonChild)){
                            $activePerson = true;
                            break;
                        } elseif(!DivisionCourse::useService()->getStudentEducationListByPerson($tblPersonChild)) {
                            // Interessenten / Kita ohne Schulverlauf gelten auch als Aktiv
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
        if($tblPersonReduceList){
            array_walk($tblPersonReduceList, function(TblPerson $tblPersonReduce) use (&$TableContent, &$TableAll,
                &$TableSelect1, &$TableSelect2, &$TableSelectMore
            ){
                $item = array();
                $item['Checkbox'] = new CheckBox('PersonReduce['.$tblPersonReduce->getId().']', ' ', $tblPersonReduce->getId());
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

                $isAccount = false;
                if(($tblAccountList = Account::useService()->getAccountAllByPerson($tblPersonReduce))){
                    // Personen mit Account können nicht gelöscht werden
                    $item['Checkbox'] = '';
                    $item['ActiveAccount'] = new Center(new DangerText(new Bold(current($tblAccountList)->getUsername())));
                    $isAccount = true;
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
                        if(count($TableAll) <= self::API_FORM_LIMIT){
                            if(!$isAccount){
                                $TableAll[] = 'PersonReduce['.$tblPersonReduce->getId().']';
                            }
                        }
                        $item['Group'] = implode('<br/>', $GroupList);
                    }
                }
                array_push($TableContent, $item);
            });
        }
        if(empty($TableContent)){
            return new Warning('Keine Person, die den Kriterien entspricht');
        }

        $Table = new TableData($TableContent, new \SPHERE\Common\Frontend\Table\Repository\Title('Vorschläge', 'zum entfernen'),
            array(
                'Checkbox'     => 'entfernen',
                'EntityCreate'  => 'Erstellung',
                'Name'          => 'Name',
                'Child'         => 'Verknüpfte Person',
                'Group'         => 'Personengruppen',
                'ActiveAccount' => 'B.-Konto'
            ), array(
                'order' => array(
                    array('1', 'desc'),
                    array('2', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'de_date', 'targets' => array(1)),
//                    array('type' => 'natural', 'targets' => array(4)),
                    array('orderable' => false, 'width' => '1%', 'targets' => array(0)), // ,-1)
                ),
                "paging" => false, // Deaktiviert Blättern
                "iDisplayLength" => -1,    // Alle Einträge zeigen
                "searching" => false, // Deaktiviert Suche
//                    "info" => false, // Deaktiviert Such-Info)
//                        "responsive" => false,
            )
        );

        return new ToggleSelective('Alle wählen/abwählen ('.count($TableAll).')', $TableAll)
            .new Form(new FormGroup(new FormRow(array(
                new FormColumn($Table),
                    (!empty($TableAll)
                        ? new FormColumn(
                            new Danger('Hiermit werden die ausgewählten Sorgeberechtigten Personen bzw. Personendaten dauerhaft gelöscht.'
                                , null, false, '15', '0')
                        )
                        : new FormColumn(
                            new Warning('Keine löschbare Person, die den Kriterien entspricht')
                        )
                    )
            )))
                ,(!empty($TableAll)
                ? (new \SPHERE\Common\Frontend\Link\Repository\Danger('Löschen', '#',new Remove()))
                    ->ajaxPipelineOnClick(ApiPersonDelete::pipelineLoadModal())
                : null)
            );
    }

    public function getFilterAdd($number)
    {

        $PrimaryButton = new Primary('', '#', new Plus(), array(), 'weitere exkludierende Gruppe');
        $PrimaryButton->ajaxPipelineOnClick(APIPersonDelete::pipelineAddFilter($number));
        return new Container('<span style="font-size: 12pt">&nbsp;</span><br/><div style="margin-bottom: 8.5px;">'.$PrimaryButton.'</div>');
    }

    public function getFilter($number)
    {

        $tblGroupList = Group::useService()->getGroupAll();
        foreach($tblGroupList as &$tblGroup) {
            if ($tblGroup->getMetaTable() === TblGroup::META_TABLE_COMMON) {
                $tblGroup = false;
            }
        }
        $tblGroupList = array_filter($tblGroupList);
        if($number > 3){
            return (new SelectBox('GroupList['.$number.']', 'Gruppe exkludieren', array('{{ Name }}' => $tblGroupList)))->configureLibrary();
        } else {
            // Initial laden aller 3 Selectboxen
            return
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn((new SelectBox('GroupList[1]', 'Gruppe exkludieren', array('{{ Name }}' => $tblGroupList)))->configureLibrary(), 4),
                    new LayoutColumn((new SelectBox('GroupList[2]', 'Gruppe exkludieren', array('{{ Name }}' => $tblGroupList)))->configureLibrary(), 4),
                    new LayoutColumn((new SelectBox('GroupList['.$number.']', 'Gruppe exkludieren', array('{{ Name }}' => $tblGroupList)))->configureLibrary(), 4)
                )))) ;
        }
    }

    /**
     * @param $PersonReduce
     * @param $InitialCount
     * @return string
     */
    public function showLoadContent($PersonReduce = array(), $InitialCount = '')
    {

        if(empty($PersonReduce)){
            return new Warning('Es konnten keine Personen gelöscht werden.')
            .ApiPersonDelete::pipelineReloadTable();
        }
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
            .ApiPersonDelete::pipelineSaveDeleteGuardModal($PersonReduce, $InitialCount);
    }

    /**
     * @param $PersonReduce
     * @param $InitialCount
     * @return Pipeline|string
     */
    public function serviceDeleteGuardContent($PersonReduce = array(), $InitialCount = '')
    {

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
            $PersonReduce = array_filter($PersonReduce);
            if(!empty($PersonReduce)){
                return ApiPersonDelete::pipelineReloadModal($PersonReduce, $InitialCount);
            }
        }

        return ApiPersonDelete::pipelineReloadTable();
    }

    /**
     * @return string
     */
    public function serviceReloadTable()
    {

        sleep(2);
        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CUSTODY);
        return ApiPersonDelete::pipelineClose()
            .ApiPersonSearch::pipelineLoadGroupSelectBox('G' . $tblGroup->getId());
    }
}