<?php

namespace SPHERE\Application\Api\People\Meta\Support;


use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentFocusType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Clock;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Ruler;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\System\Extension\Extension;

class ApiSupport extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('openCreateSupportModal');
        $Dispatcher->registerMethod('saveCreateSupportModal');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Header
     * @param string $Footer
     *
     * @return ModalReceiver
     */
    public static function receiverModal($Header = '', $Footer = '')
    {

        return (new ModalReceiver($Header, $Footer))->setIdentifier('SupportModal');
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverInline($Content = '', $Identifier = '')
    {

        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }



    /**
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateSupportModal($PersonId)
    {
        $ComparePasswordPipeline = new Pipeline(false);
        $ComparePasswordEmitter = new ServerEmitter(ApiSupport::receiverModal('Förderantrag/Förderbescheid hinzufügen'), ApiSupport::getEndpoint());
        $ComparePasswordEmitter->setGetPayload(array(
            ApiSupport::API_TARGET => 'openCreateSupportModal',
        ));
        $ComparePasswordEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $ComparePasswordPipeline->appendEmitter($ComparePasswordEmitter);

        return $ComparePasswordPipeline;
    }

    public static function pipelineCreateSupportSave($PersonId)
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(ApiSupport::receiverModal('Förderantrag/Förderbescheid hinzufügen'), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateSupportModal'
        ));
        $Emitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Emitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function openCreateSupportModal($PersonId)
    {

        return new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Well(
                            $this->formSupport($PersonId)
                        )
                    )
                )
            )
        );
    }

    public static function pipelineClose()
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());
        return $Pipeline;
    }

    private function formSupport($PersonId)
    {

        $tblStudentFocusList = Student::useService()->getStudentFocusTypeAll();
        $SupportTypeList = Student::useService()->getSupportTypeAll();

        $tblStudentFocusList = $this->getSorter($tblStudentFocusList)->sortObjectBy('Name');
        $CheckboxList = array();
        /** @var TblStudentFocusType $tblStudentFocus */
        foreach($tblStudentFocusList as $tblStudentFocus){
            $CheckboxList[] = new CheckBox('Data[CheckboxList]['.$tblStudentFocus->getName().']', $tblStudentFocus->getName(), $tblStudentFocus->getId());
        }

        //ToDO Company einschränken
        $tblCompanyList = Company::useService()->getCompanyAll();

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        (new DatePicker('Data[Date]', '', 'Datum', new Calendar()))->setRequired()
                    , 6),
                    new FormColumn(
                        new SelectBox('Data[PrimaryFocus]', 'Primär geförderter Schwerpunkt', array('{{ Name }}' => $tblStudentFocusList))
                    , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        (new SelectBox('Data[SupportType]', 'Förderantrag', array('{{ Name }}' => $SupportTypeList), new Education()))->setRequired()
                    , 6),
                    new FormColumn(
                        new Listing($CheckboxList)
                    , 6),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        new SelectBox('Data[Company]', 'Förderschule', array('{{ Name }}' => $tblCompanyList), new Education()),
                        new Ruler(),
                        new TextField('Data[PersonSupport]', 'Schulbegleitung', 'Schulbegleitung', new PersonIcon()),
                        new Ruler(),
                        new TextField('Data[SupportTime]', 'Stundenbedarf pro Woche', 'Stundenbedarf pro Woche', new Clock()),
                        ), 6),
                    new FormColumn(
                        (new TextArea('Data[Remark]', 'Bemerkung', 'Bemerkung', new Edit()))
                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        (new Primary('Speichern', ApiSupport::getEndpoint(), new Save()))->ajaxPipelineOnClick(self::pipelineCreateSupportSave($PersonId))
                    )
                )),
            ))
        );
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function saveCreateSupportModal($PersonId)
    {

        $Global = $this->getGlobal();
        $Data = $Global->POST['Data'];
        if ($form = $this->checkInputSupport($PersonId, $Data)) {
            // display Errors on form
            return $form;
        }
        // do service

//        return 'Alles ok für\'s speichern';
        if (Student::useService()->createSupport($PersonId, $Data)
        ) {
             return new Success('Förderantrag wurde erfolgreich gespeichert.').self::pipelineClose();
        } else {
            return new Danger('Förderantrag konnte nicht gespeichert werden.').self::pipelineClose();
        }
    }

    /**
     * @param int   $PersonId
     * @param array $Data
     *
     * @return false|string|Form
     */
    private function checkInputSupport($PersonId, $Data)
    {
        $Error = false;
        $form = $this->formSupport($PersonId);
        if (isset($Data['Date']) && empty($Data['Date'])) {
            $form->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (isset($Data['SupportType']) && empty($Data['SupportType'])) {
            $form->setError('Data[SupportType]', 'Bitte geben Sie ein Verhalten des Förderantrag an');
            $Error = true;
        }
        if ($Error) {
            return new Well($form);
        }

        return $Error;
    }
}