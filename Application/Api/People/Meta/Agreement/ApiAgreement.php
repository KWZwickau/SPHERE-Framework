<?php
namespace SPHERE\Application\Api\People\Meta\Agreement;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementCategory;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Aspect;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Extension\Extension;

class ApiAgreement extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('openOverViewModal');
        $Dispatcher->registerMethod('saveAgreement');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverOverViewModal()
    {

        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalAgreementOverViewReciever');
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenOverViewModal($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverOverViewModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiAgreement::API_TARGET => 'openOverViewModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelinesaveAgreement($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverOverViewModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiAgreement::API_TARGET => 'saveAgreement',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineCloseOverViewModal()
    {
        $Pipeline = new Pipeline(false);
        $Pipeline->appendEmitter((new CloseModal(self::receiverOverViewModal()))->getEmitter());
        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function openOverViewModal($PersonId)
    {

        $tblPerson = Person::useService()->getPersonById($PersonId);
        if(!$tblPerson){
            $HeadPanel = new Warning('Person wurde nicht gefunden');
            $WellAgreement = '';
        } else {
            $HeadPanel = new Panel('Schüler', $tblPerson->getLastFirstName(), Panel::PANEL_TYPE_INFO);
            $FormColumnList = array();
            $Global = $this->getGlobal();
            if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                $isShowButtons = false;
                if(($StudentAgreementTypeAll = Student::useService()->getStudentAgreementTypeAll())){
                    foreach($StudentAgreementTypeAll as $StudentAgreementType){
                        if($StudentAgreementType->getIsUnlocked()){
                            $isShowButtons = true;
                            break;
                        }
                    }
                }

                if(($tblStudentAgreementList = Student::useService()->getStudentAgreementAllByStudent($tblStudent))){
                    foreach($tblStudentAgreementList as $tblStudentAgreement){
                        if(($tblStudentAgreementType = $tblStudentAgreement->getTblStudentAgreementType())){
                            $Global->POST['Meta']['Agreement']
                            [$tblStudentAgreementType->getTblStudentAgreementCategory()->getId()]
                            [$tblStudentAgreementType->getId()] = 1;
                        }
                    }
                }
                $Global->savePost();

                if(($tblAgreementCategoryAll = Student::useService()->getStudentAgreementCategoryAll())){
                    array_walk($tblAgreementCategoryAll, function (TblStudentAgreementCategory $tblStudentAgreementCategory) use (&$FormColumnList) {
                        $Content[] = new Aspect(new Bold($tblStudentAgreementCategory->getName()));
                        $tblAgreementTypeAll = Student::useService()->getStudentAgreementTypeAllByCategory($tblStudentAgreementCategory);
                        if ($tblAgreementTypeAll) {
//                            $tblAgreementTypeAll = $this->getSorter($tblAgreementTypeAll)->sortObjectBy('Name');
                            array_walk($tblAgreementTypeAll, function (TblStudentAgreementType $tblStudentAgreementType) use (&$Content, $tblStudentAgreementCategory) {
                                $Checkbox = new CheckBox('Meta[Agreement]['.$tblStudentAgreementCategory->getId().']['.$tblStudentAgreementType->getId().']',
                                    $tblStudentAgreementType->getName(), 1);
                                if(!$tblStudentAgreementType->getIsUnlocked()){
                                    $Checkbox->setDisabled();
                                }
                                $Content[] = $Checkbox;
                            });
                            $FormColumnList[] = new FormColumn(new Listing($Content), 6);
                        }
                    });
                }

            }

            $form = new Form(new FormGroup(new FormRow($FormColumnList)));
            if($isShowButtons){
                $form->appendGridGroup(
                    new FormGroup(new FormRow(new FormColumn(array(
                        (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiAgreement::pipelinesaveAgreement($PersonId)),
                        (new Primary('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiAgreement::pipelineCloseOverViewModal())
                    ))))
                );
            }
            $WellAgreement = new Well($form);
        }


        return new Title('Einverständniserklärung')

            .new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            $HeadPanel
                        , 12),
                        new LayoutColumn(
                            $WellAgreement
                        ),
                    ))
                )
            );
    }

    public function saveAgreement($PersonId, $Meta)
    {

        if(($tblPerson = Person::useService()->getPersonById($PersonId))){
            Student::useService()->updateStudentAgreement($tblPerson, $Meta, true);
            return new Success('Änderung erfoglreich gespeichert')
                .ApiAgreement::pipelineCloseOverViewModal();
        }
        return new Danger('Person nicht gefunden');
    }
}