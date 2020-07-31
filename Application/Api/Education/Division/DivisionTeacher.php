<?php

namespace SPHERE\Application\Api\Education\Division;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\Division\Division as DivisionApplication;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionTeacher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\MinusSign;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\System\Extension\Extension;

/**
 * Class DivisionTeacher
 * 
 * @package SPHERE\Application\Api\Education\Division
 */
class DivisionTeacher extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param string $Method Callable Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('tablePerson');
        $Dispatcher->registerMethod('serviceAddPerson');
        $Dispatcher->registerMethod('serviceRemovePerson');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverUsed($Content = '')
    {
        return (new BlockReceiver($Content))->setIdentifier('UsedReceiver');
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverService($Content = '')
    {
        return (new BlockReceiver($Content))->setIdentifier('ServiceReceiver');
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public static function getTableContentUsed(TblDivision $tblDivision)
    {
        $tblDivisionTeacherAllSelected = DivisionApplication::useService()->getDivisionTeacherAllByDivision($tblDivision);
        $usedList = array();
        if ($tblDivisionTeacherAllSelected) {
            array_walk($tblDivisionTeacherAllSelected, function (TblDivisionTeacher $tblDivisionTeacher) use ($tblDivision, &$usedList) {
                if (($tblPerson = $tblDivisionTeacher->getServiceTblPerson())) {
                    $address = ($tblAddress = $tblPerson->fetchMainAddress())
                        ? $tblAddress->getGuiString()
                        : new \SPHERE\Common\Frontend\Text\Repository\Warning('Keine Adresse hinterlegt');

                    $Item['Id'] = $tblPerson->getId();
                    $Item['DivisionId'] = $tblDivision->getId();
                    $Item['Name'] = $tblPerson->getLastFirstName();
                    $Item['Address'] = $address;
                    $Item['Description'] = $tblDivisionTeacher->getDescription();

                    array_push($usedList, $Item);
                }
            });
        }

        return $usedList;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public static function getTableContentAvailable(TblDivision $tblDivision)
    {
        $tblDivisionTeacherAllSelected = DivisionApplication::useService()->getDivisionTeacherAllByDivision($tblDivision);
        $tblTeacherAllList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable('TEACHER'));

        $tblTeacherSelectedList = array();
        if ($tblDivisionTeacherAllSelected){
            foreach ($tblDivisionTeacherAllSelected as $tblDivisionTeacher){
                if ($tblDivisionTeacher->getServiceTblPerson()){
                    $tblTeacherSelectedList[] = $tblDivisionTeacher->getServiceTblPerson();
                }
            }
        }

        if (!empty($tblTeacherSelectedList) && $tblTeacherAllList) {
            $tblTeacherAllList = array_udiff($tblTeacherAllList, $tblTeacherSelectedList,
                function (TblPerson $ObjectA, TblPerson $ObjectB) {
                    return $ObjectA->getId() - $ObjectB->getId();
                }
            );
        }

        $availableList = array();
        if ($tblTeacherAllList) {
            array_walk($tblTeacherAllList, function (TblPerson $tblPerson) use ($tblDivision, &$availableList) {
                $Item['Id'] = $tblPerson->getId();
                $Item['DivisionId'] = $tblDivision->getId();
                $Item['Name'] = $tblPerson->getLastFirstName();
                $Item['Address'] = ($tblAddress = $tblPerson->fetchMainAddress())
                    ? $tblAddress->getGuiString()
                    : new \SPHERE\Common\Frontend\Text\Repository\Warning('Keine Adresse hinterlegt');
                array_push($availableList, $Item);
            });
        }
        return $availableList;
    }

    /**
     * @param null $DivisionId
     * @param null $Description
     *
     * @return Layout
     */
    public static function tablePerson($DivisionId = null, $Description = null)
    {
        $_POST['Description'] = '';

        // get Content
        $tblDivision = DivisionApplication::useService()->getDivisionById($DivisionId);
        $ContentList = false;
        $ContentListAvailable = false;
        if ($tblDivision) {
            $ContentList = self::getTableContentUsed($tblDivision);
            $ContentListAvailable = self::getTableContentAvailable($tblDivision);
        }

        // Select
        $Table = array();
        if (is_array($ContentList)) {
            if (!empty($ContentList)) {
                foreach ($ContentList as $Person) {
                    $Table[] = array(
                        'Name' => $Person['Name'],
                        'Address' => $Person['Address'],
                        'Description' => $Person['Description'],
                        'Option' => (new Standard('', self::getEndpoint(), new MinusSign(), array(), 'Entfernen'))
                            ->ajaxPipelineOnClick(self::pipelineMinus($Person['Id'], $Person['DivisionId']))
                    );
                }
                // Anzeige
                $left = (new TableData($Table, new Title('Ausgewählte', 'Lehrer'), array(
                    'Name' => 'Name',
                    'Address'     => 'Adresse',
                    'Description' => 'Beschreibung',
                    'Option'      => ''
                ),
                    array(
                        'columnDefs' => array(
                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                            array('orderable' => false, 'width' => '1%', 'targets' => -1),
                        ),
                        'responsive' => false
                    )
                ))->setHash(__NAMESPACE__ . 'DivisionTeacherSelect' . 'Selected');
            } else {
                $left = new Info('Keine Lehrer ausgewählt');
            }
        } else {
            $left = new Warning('Klasse nicht gefunden');
        }

        // Select
        $TableAvailable = array();
        if (is_array($ContentListAvailable)) {
            if (!empty($ContentListAvailable)) {
                foreach ($ContentListAvailable as $Person) {
                    $TableAvailable[] = array(
                        'Name' => $Person['Name'],
                        'Address' => $Person['Address'],
                        'Option' => (new Form(
                            new FormGroup(
                                new FormRow(array(
                                    new FormColumn(
                                        new TextField('Description', 'z.B.: Stellvertreter')
                                        , 9),
                                    new FormColumn(
                                        (new Standard('', self::getEndpoint(), new PlusSign(), array(), 'Hinzufügen'))
                                        ->ajaxPipelineOnClick(self::pipelinePlus($Person['Id'], $Person['DivisionId'], $Description))
                                        , 3)
                                ))
                            )
                        ))->__toString()
                    );
                }
                // Anzeige
                $right = (new TableData($TableAvailable, new Title('Verfügbare', 'Lehrer'), array(
                    'Name' => 'Name',
                    'Address'     => 'Adresse',
                    'Option'     => 'Beschreibung'
                ),
                    array(
                        'columnDefs' => array(
                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                            array('orderable' => false, 'width' => '50%', 'targets' => -1),
                        ),
                        'responsive' => false
                    )
                ))->setHash(__NAMESPACE__ . 'DivisionTeacherSelect' . 'Available');
            } else {
                $right = new Info('Keine weiteren Lehrer verfügbar');
            }
        } else {
            $right = new Warning('Klasse nicht gefunden');
        }

        return
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    $left
                    , 6),
                new LayoutColumn(
                    $right
                    , 6)
            ))));
    }

    /**
     * @param null $Id
     * @param null $DivisionId
     *
     * @return Pipeline
     */
    public static function pipelineMinus($Id = null, $DivisionId = null)
    {
        $Pipeline = new Pipeline();

        // execute Service
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'serviceRemovePerson',
            'Id' => $Id,
            'DivisionId' => $DivisionId
        ));
        $Pipeline->appendEmitter($Emitter);

        // refresh Table
        $Emitter = new ServerEmitter(self::receiverUsed(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'tablePerson',
            'DivisionId' => $DivisionId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param null $Id
     * @param null $DivisionId
     */
    public function serviceRemovePerson($Id = null, $DivisionId = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($Id))
            && ($tblDivision = DivisionApplication::useService()->getDivisionById($DivisionId))
        ) {

            DivisionApplication::useService()->removeTeacherToDivision($tblDivision, $tblPerson);
        }
    }

    /**
     * @param null $Id
     * @param null $DivisionId
     * @param null $Description
     *
     * @return Pipeline
     */
    public static function pipelinePlus($Id = null, $DivisionId = null, $Description = null)
    {
        $Pipeline = new Pipeline();

        // execute Service
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'serviceAddPerson',
            'Id' => $Id,
            'DivisionId' => $DivisionId,
            'Description' => $Description
        ));
        $Pipeline->appendEmitter($Emitter);

        // refresh Table
        $Emitter = new ServerEmitter(self::receiverUsed(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'tablePerson',
            'DivisionId' => $DivisionId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param null $Id
     * @param null $DivisionId
     * @param null $Description
     */
    public function serviceAddPerson($Id = null, $DivisionId = null, $Description = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($Id))
            && ($tblDivision = DivisionApplication::useService()->getDivisionById($DivisionId))
        ) {
            DivisionApplication::useService()->addDivisionTeacher($tblDivision, $tblPerson, $Description ? $Description : '');
        }
    }
}