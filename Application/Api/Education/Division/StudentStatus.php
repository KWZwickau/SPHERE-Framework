<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 19.11.2018
 * Time: 11:45
 */

namespace SPHERE\Application\Api\Education\Division;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Extension\Extension;
use SPHERE\Common\Frontend\Link\Repository\Primary;

/**
 * Class StudentStatus
 *
 * @package SPHERE\Application\Api\Education\Division
 */
class StudentStatus extends Extension implements IApiInterface
{

    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('openDeactivateStudentModal');
        $Dispatcher->registerMethod('saveDeactivateStudentModal');

        $Dispatcher->registerMethod('saveActivateStudent');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {

        return (new ModalReceiver())->setIdentifier('ModalReciever');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineClose()
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());
        return $Pipeline;
    }

    /**
     * @param int $DivisionId
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeactivateStudentModal($DivisionId, $PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeactivateStudentModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionId' => $DivisionId,
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $DivisionId
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineDeactivateStudentSave($DivisionId, $PersonId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeactivateStudentModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionId' => $DivisionId,
            'PersonId' => $PersonId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);
        return $Pipeline;
    }

    /**
     * @param int $DivisionId
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineActivateStudentSave($DivisionId, $PersonId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveActivateStudent'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionId' => $DivisionId,
            'PersonId' => $PersonId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);
        return $Pipeline;
    }

    /**
     * @param int $DivisionId
     * @param int $PersonId
     *
     * @return string
     */
    public function openDeactivateStudentModal($DivisionId, $PersonId)
    {

        if (!($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
            return (new Danger('Klasse nicht gefunden', new Exclamation()));
        }
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return (new Danger('Person nicht gefunden', new Exclamation()));
        }

        $global = $this->getGlobal();
        $global->POST['Data']['UseGradesInNewDivision'] = true;
        $global->savePost();

        return new Title('Schüler deaktivieren')
            . new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                new Well(
                    new Form(
                        new FormGroup(
                            new FormRow(array(
                                new FormColumn(
                                    new CheckBox('Data[UseGradesInNewDivision]',
                                        'Noten in der neuen Klasse berücksichtigen', true)
                                ),
                                new FormColumn(
                                    (new Primary('Speichern', self::getEndpoint(), new Save()))
                                        ->ajaxPipelineOnClick(self::pipelineDeactivateStudentSave($DivisionId,
                                            $PersonId))
                                )
                            ))
                        )
                    )
                )
            ))));
    }

    /**
     * @param int $DivisionId
     * @param int $PersonId
     *
     * @return string
     */
    public function saveDeactivateStudentModal($DivisionId, $PersonId)
    {

        $Global = $this->getGlobal();
        $Data = isset($Global->POST['Data']) ? $Global->POST['Data'] : array();

        $UseGradesInNewDivision = isset($Data['UseGradesInNewDivision']);
        $LeaveDate = new \DateTime('now');

        if (($tblDivision = Division::useService()->getDivisionById($DivisionId))
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblDivisionStudent = Division::useService()->getDivisionStudentByDivisionAndPerson($tblDivision,
                $tblPerson))
            && Division::useService()->deactivateDivisionStudent($tblDivisionStudent, $LeaveDate,
                $UseGradesInNewDivision)
        ) {
            return new Success('Deaktivierung wurde erfolgreich gespeichert.')
                . self::pipelineClose()
                . new Redirect('/Education/Lesson/Division/Show', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblDivision->getId()));
        } else {
            return new Danger('Deaktivierung konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param int $DivisionId
     * @param int $PersonId
     *
     * @return string
     */
    public function saveActivateStudent($DivisionId, $PersonId)
    {

        if (($tblDivision = Division::useService()->getDivisionById($DivisionId))
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblDivisionStudent = Division::useService()->getDivisionStudentByDivisionAndPerson($tblDivision,
                $tblPerson))
            && Division::useService()->activateDivisionStudent($tblDivisionStudent)
        ) {
            return new Success('Aktivierung wurde erfolgreich gespeichert.')
                . self::pipelineClose()
                . new Redirect('/Education/Lesson/Division/Show', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblDivision->getId()));
        } else {
            return new Danger('Aktivierung konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }
}