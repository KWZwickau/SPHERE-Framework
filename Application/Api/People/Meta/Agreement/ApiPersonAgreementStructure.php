<?php
namespace SPHERE\Application\Api\People\Meta\Agreement;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Frontend\FrontendStudentAgreement;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\System\Extension\Extension;

class ApiPersonAgreementStructure extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('editPersonAgreementStructure');

        $Dispatcher->registerMethod('openCreateCategoryModal');
        $Dispatcher->registerMethod('saveCreateCategoryModal');
        $Dispatcher->registerMethod('openEditCategoryModal');
        $Dispatcher->registerMethod('saveEditCategoryModal');
        $Dispatcher->registerMethod('openDestroyCategoryModal');
        $Dispatcher->registerMethod('saveDestroyCategoryModal');

        $Dispatcher->registerMethod('openCreateTypeModal');
        $Dispatcher->registerMethod('saveCreateTypeModal');
        $Dispatcher->registerMethod('openEditTypeModal');
        $Dispatcher->registerMethod('saveEditTypeModal');
        $Dispatcher->registerMethod('openDestroyTypeModal');
        $Dispatcher->registerMethod('saveDestroyTypeModal');

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
     * @return ModalReceiver
     */
    public static function receiverModal($Title = '', $Identifer = '')
    {

        return (new ModalReceiver($Title, new Close()))->setIdentifier($Identifer);
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateCategoryModal($PersonId)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal('', 'ModalAgreementStructureCreateCategory'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiPersonAgreementStructure::API_TARGET => 'openCreateCategoryModal',
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
    public static function pipelineSaveCreateCategory($PersonId)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal('', 'ModalAgreementStructureCreateCategory'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiPersonAgreementStructure::API_TARGET => 'saveCreateCategoryModal',
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
    public static function pipelineOpenEditCategoryModal($PersonId, $CategoryId)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal('', 'ModalAgreementStructureEditCategory'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiPersonAgreementStructure::API_TARGET => 'openEditCategoryModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'CategoryId' => $CategoryId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $PersonId
     * @param int $CategoryId
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditCategory($PersonId, $CategoryId)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal('', 'ModalAgreementStructureEditCategory'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiPersonAgreementStructure::API_TARGET => 'saveEditCategoryModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'CategoryId' => $CategoryId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDestroyCategoryModal($PersonId, $CategoryId)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal('', 'ModalAgreementStructureDestroyCategory'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiPersonAgreementStructure::API_TARGET => 'openDestroyCategoryModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'CategoryId' => $CategoryId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $PersonId
     * @param int $CategoryId
     *
     * @return Pipeline
     */
    public static function pipelineSaveDestroyCategory($PersonId, $CategoryId)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal('', 'ModalAgreementStructureDestroyCategory'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiPersonAgreementStructure::API_TARGET => 'saveDestroyCategoryModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'CategoryId' => $CategoryId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateTypeModal($PersonId, $CategoryId)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal('', 'ModalAgreementStructureCreateType'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiPersonAgreementStructure::API_TARGET => 'openCreateTypeModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'CategoryId' => $CategoryId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineSaveCreateType($PersonId, $CategoryId)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal('', 'ModalAgreementStructureCreateType'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiPersonAgreementStructure::API_TARGET => 'saveCreateTypeModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'CategoryId' => $CategoryId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditTypeModal($PersonId, $TypeId)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal('', 'ModalAgreementStructureEditType'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiPersonAgreementStructure::API_TARGET => 'openEditTypeModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'TypeId' => $TypeId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $PersonId
     * @param int $TypeId
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditType($PersonId, $TypeId)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal('', 'ModalAgreementStructureEditType'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiPersonAgreementStructure::API_TARGET => 'saveEditTypeModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'TypeId' => $TypeId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDestroyTypeModal($PersonId, $TypeId)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal('', 'ModalAgreementStructureDestroyType'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiPersonAgreementStructure::API_TARGET => 'openDestroyTypeModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'TypeId' => $TypeId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $PersonId
     * @param int $TypeId
     *
     * @return Pipeline
     */
    public static function pipelineSaveDestroyType($PersonId, $TypeId)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal('', 'ModalAgreementStructureDestroyType'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiPersonAgreementStructure::API_TARGET => 'saveDestroyTypeModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'TypeId' => $TypeId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineCloseModal($Identifier)
    {

        $Pipeline = new Pipeline();
        // Close Modal
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal('', $Identifier)))->getEmitter());
        return $Pipeline;
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function editPersonAgreementStructure($PersonId = null)
    {

        $CategoryList = array();
        //ToDO neue Anbindung
        if(($tblStudentAgreementCategoryList = Student::useService()->getStudentAgreementCategoryAll())){
            foreach($tblStudentAgreementCategoryList as $tblStudentAgreementCategory){
                $CategoryList[$tblStudentAgreementCategory->getName()][] = new Bold($tblStudentAgreementCategory->getName()) .new PullRight(
                    (new Link(new Edit(), '#'))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineOpenEditCategoryModal($PersonId, $tblStudentAgreementCategory->getId()))
                    .(new Link(new DangerText(new Disable()), '#'))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineOpenDestroyCategoryModal($PersonId, $tblStudentAgreementCategory->getId()))
                );
                if(($tblStudentAgreementTypeList = Student::useService()->getStudentAgreementTypeAllByCategory($tblStudentAgreementCategory))){
                    foreach($tblStudentAgreementTypeList as $tblStudentAgreementType){
                        $CategoryList[$tblStudentAgreementCategory->getName()][] = $tblStudentAgreementType->getName().new PullRight(
                            (new Link(new Edit(), '#'))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineOpenEditTypeModal($PersonId, $tblStudentAgreementType->getId()))
                            .(new Link(new DangerText(new Disable()), '#'))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineOpenDestroyTypeModal($PersonId, $tblStudentAgreementType->getId()))
                        );
                    }
                }
                if(count($CategoryList[$tblStudentAgreementCategory->getName()]) < 9
                    || count($CategoryList[$tblStudentAgreementCategory->getName()]) == 0
                ){
                    $CategoryList[$tblStudentAgreementCategory->getName()][] = (new Link(new Plus().'Typ hinzufügen', '#'))
                    ->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineOpenCreateTypeModal($PersonId, $tblStudentAgreementCategory->getId()));
                }
            }
        }

        $LayoutColumnList = array();
        foreach($CategoryList as $Content){
            $LayoutColumnList[] = new LayoutColumn(new Listing($Content), 3);
        }
        if(count($LayoutColumnList) < 4){
            $LayoutColumnList[] = new LayoutColumn(new Listing(array(
                (new Link(new Plus().'Kategorie hinzufügen', '#'))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineOpenCreateCategoryModal($PersonId))
            )), 3);
        }

        //ToDO neues Frontend
        return (new FrontendStudentAgreement())->getEditStudentAgreementStructure($PersonId)
            .new Well(
                ApiPersonAgreementStructure::receiverModal('Kategorie hinzufügen', 'ModalAgreementStructureCreateCategory')
                .ApiPersonAgreementStructure::receiverModal('Kategorie bearbeiten', 'ModalAgreementStructureEditCategory')
                .ApiPersonAgreementStructure::receiverModal('Kategorie entfernen', 'ModalAgreementStructureDestroyCategory')
                .ApiPersonAgreementStructure::receiverModal('Typ hinzufügen', 'ModalAgreementStructureCreateType')
                .ApiPersonAgreementStructure::receiverModal('Typ bearbeiten', 'ModalAgreementStructureEditType')
                .ApiPersonAgreementStructure::receiverModal('Typ entfernen', 'ModalAgreementStructureDestroyType')
                .new Layout(new LayoutGroup(new LayoutRow($LayoutColumnList)))
                .(new Primary('Zurück', ApiPersonEdit::getEndpoint(), new ChevronLeft()))
                    ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelPersonAgreementContent($PersonId))
            );
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function openCreateCategoryModal($PersonId)
    {

        $form = FrontendPersonAgreement::getCategoryForm();
        // Buttons hinzufügen
        $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
            (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineSaveCreateCategory($PersonId)),
            (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineCloseModal('ModalAgreementStructureCreateCategory'))
        )))));
        return new Well($form);
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function saveCreateCategoryModal($PersonId, $Meta)
    {

        $isError = false;
        if(!isset($Meta['Category']) || $Meta['Category'] == ''){
            $form = FrontendPersonAgreement::getCategoryForm();
            // Fehler
            $form->setError('Meta[Category]', 'Bitte geben Sie etwas ein');
            // Buttons hinzufügen
            $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
                (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineSaveCreateCategory($PersonId)),
                (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineCloseModal('ModalAgreementStructureCreateCategory'))
            )))));
            $isError = true;
        } else {
            //ToDO neue Anbindung
            if(Student::useService()->getStudentAgreementCategoryByName($Meta['Category'])){
                $form = FrontendPersonAgreement::getCategoryForm();
                // Fehler
                $form->setError('Meta[Category]', 'Name der Kategorie ist bereits in Verwendung');
                // Buttons hinzufügen
                $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
                    (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineSaveCreateCategory($PersonId)),
                    (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineCloseModal('ModalAgreementStructureCreateCategory'))
                )))));
                $isError = true;
            }
        }
        if(!$isError){
            $Name = $Meta['Category'];
            $Description = isset($Meta['Description']) ?? $Meta['Description'];
            //ToDO neue Anbindung
            Student::useService()->createPersonAgreementCategory($Name, $Description);
            return new Success('Anlegen war Erfolgreich!')
                .ApiPersonAgreementStructure::pipelineEditPersonAgreementStructure($PersonId)
                .ApiPersonAgreementStructure::pipelineCloseModal('ModalAgreementStructureCreateCategory');
        }
        return new Well($form);
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function openEditCategoryModal($PersonId, $CategoryId)
    {
        //ToDO neue Anbindung
        if(($tblPersonAgreementCategory = Student::useService()->getPersonAgreementCategoryById($CategoryId))){
            $_POST['Meta']['Category'] = $tblPersonAgreementCategory->getName();
        }

        $form = FrontendPersonAgreement::getCategoryForm();
        // Buttons hinzufügen
        $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
            (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineSaveEditCategory($PersonId, $CategoryId)),
            (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineCloseModal('ModalAgreementStructureEditCategory'))
        )))));
        return new Well($form);
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function saveEditCategoryModal($PersonId, $CategoryId, $Meta)
    {

        $isError = false;
        //ToDO neue Anbindung
        $tblPersonAgreementCategory = Student::useService()->getPersonAgreementCategoryById($CategoryId);
        if(!isset($Meta['Category']) || $Meta['Category'] == ''){
            $form = FrontendPersonAgreement::getCategoryForm();
            // Fehler
            $form->setError('Meta[Category]', 'Bitte geben Sie etwas ein');
            // Buttons hinzufügen
            $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
                (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineSaveEditCategory($PersonId, $CategoryId)),
                (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineCloseModal('ModalAgreementStructureEditCategory'))
            )))));
            $isError = true;
        } else {
            if($Meta['Category'] != $tblPersonAgreementCategory->getName()
                //ToDO neue Anbindung
            && Student::useService()->getPersonAgreementCategoryByName($Meta['Category'])){
                $form = FrontendPersonAgreement::getCategoryForm();
                // Fehler
                $form->setError('Meta[Category]', 'Name der Kategorie ist bereits in Verwendung');
                // Buttons hinzufügen
                $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
                    (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineSaveEditCategory($PersonId, $CategoryId)),
                    (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineCloseModal('ModalAgreementStructureEditCategory'))
                )))));
                $isError = true;
            }
        }
        if(!$isError){
            $Name = $Meta['Category'];
            $Description = isset($Meta['Description']) ?? $Meta['Description'];
            Student::useService()->updatePersonAgreementCategory($tblPersonAgreementCategory, $Name, $Description);
            return new Success('Bearbeiten war Erfolgreich!')
                .ApiPersonAgreementStructure::pipelineEditPersonAgreementStructure($PersonId)
                .ApiPersonAgreementStructure::pipelineCloseModal('ModalAgreementStructureEditCategory');
        }
        return new Well($form);
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function openDestroyCategoryModal($PersonId, $CategoryId)
    {

        //ToDO neue Anbindung
        $tblPersonAgreementCategory = Student::useService()->getPersonAgreementCategoryById($CategoryId);
        $CategoryName = $tblPersonAgreementCategory->getName();
        $TypeWithCount = array();
        if(($tblPersonAgreementTypeList = Student::useService()->getPersonAgreementTypeAllByCategory($tblPersonAgreementCategory))){
            foreach($tblPersonAgreementTypeList as $tblPersonAgreementType){
                $AgreementCount = 0;
                $tblPersonAgreementList = Student::useService()->getPersonAgreementAllByType($tblPersonAgreementType);
                if($tblPersonAgreementList){
                    $AgreementCount = count($tblPersonAgreementList);
                }
                $TypeWithCount[] = $tblPersonAgreementType->getName().new ToolTip(new Muted(new Small(' ('.$AgreementCount.')')), 'Verwendungen');
            }
        }

        if(empty($TypeWithCount)){
            $TypeWithCount[] = new Success('Keine Typen zur Kategorie hinterlegt', null, false, 5, 5);
        }

        $Panel = new Panel('Wollen Sie die Kategorie '.new Bold($CategoryName).' wirklich entfernen?', $TypeWithCount, Panel::PANEL_TYPE_DANGER);
        $ButtonYes = (new Primary('Ja', '#', new SuccessIcon()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineSaveDestroyCategory($PersonId, $CategoryId));
        $ButtonNo = (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineCloseModal('ModalAgreementStructureDestroyCategory'));

        return new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn($Panel),
            new LayoutColumn($ButtonYes.$ButtonNo)
        ))));
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function saveDestroyCategoryModal($PersonId, $CategoryId)
    {

        $tblPersonAgreementCategory = Student::useService()->getPersonAgreementCategoryById($CategoryId);
        if(Student::useService()->destroyPersonAgreementCategory($tblPersonAgreementCategory)){
            return new Success('Kategorie wurde entfernt')
                .ApiPersonAgreementStructure::pipelineEditPersonAgreementStructure($PersonId)
                .ApiPersonAgreementStructure::pipelineCloseModal('ModalAgreementStructureDestroyCategory');
        }
        return new Danger('Kategorie konnte nicht entfernt werden');
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function openCreateTypeModal($PersonId, $CategoryId)
    {

        $form = FrontendPersonAgreement::getTypeForm();
        // Buttons hinzufügen
        $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
            (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineSaveCreateType($PersonId, $CategoryId)),
            (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineCloseModal('ModalAgreementStructureCreateType'))
        )))));
        return new Well($form);
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function saveCreateTypeModal($PersonId, $CategoryId, $Meta)
    {

        $isError = false;
        $tblPersonAgreementCategory = Student::useService()->getPersonAgreementCategoryById($CategoryId);
        if(!isset($Meta['Type']) || $Meta['Type'] == ''){
            $form = FrontendPersonAgreement::getTypeForm();
            // Fehler
            $form->setError('Meta[Type]', 'Bitte geben Sie etwas ein');
            // Buttons hinzufügen
            $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
                (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineSaveCreateType($PersonId, $CategoryId)),
                (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineCloseModal('ModalAgreementStructureCreateType'))
            )))));
            $isError = true;
        } else {
            if(Student::useService()->getPersonAgreementTypeByName($Meta['Type'])){
                $form = FrontendPersonAgreement::getTypeForm();
                // Fehler
                $form->setError('Meta[Type]', 'Name des Typ\'s ist bereits in Verwendung');
                // Buttons hinzufügen
                $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
                    (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineSaveCreateType($PersonId, $CategoryId)),
                    (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineCloseModal('ModalAgreementStructureCreateType'))
                )))));
                $isError = true;
            }
        }
        if(!$isError){
            $Name = $Meta['Type'];
            $Description = isset($Meta['Description']) ?? $Meta['Description'];
            Student::useService()->createPersonAgreementType($tblPersonAgreementCategory, $Name, $Description);
            return new Success('Anlegen war Erfolgreich!')
                .ApiPersonAgreementStructure::pipelineEditPersonAgreementStructure($PersonId)
                .ApiPersonAgreementStructure::pipelineCloseModal('ModalAgreementStructureCreateType');
        }
        return new Well($form);
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function openEditTypeModal($PersonId, $TypeId)
    {

        if(($tblPersonAgreementType = Student::useService()->getPersonAgreementTypeById($TypeId))){
            $_POST['Meta']['Type'] = $tblPersonAgreementType->getName();
        }

        $form = FrontendPersonAgreement::getTypeForm();
        // Buttons hinzufügen
        $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
            (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineSaveEditType($PersonId, $TypeId)),
            (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineCloseModal('ModalAgreementStructureEditType'))
        )))));
        return new Well($form);
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function saveEditTypeModal($PersonId, $TypeId, $Meta)
    {

        $isError = false;
        $tblPersonAgreementType = Student::useService()->getPersonAgreementTypeById($TypeId);
        if(!isset($Meta['Type']) || $Meta['Type'] == ''){
            $form = FrontendPersonAgreement::getTypeForm();
            // Fehler
            $form->setError('Meta[Type]', 'Bitte geben Sie etwas ein');
            // Buttons hinzufügen
            $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
                (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineSaveEditType($PersonId, $TypeId)),
                (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineCloseModal('ModalAgreementStructureEditType'))
            )))));
            $isError = true;
        } else {
            if($Meta['Type'] != $tblPersonAgreementType->getName()
            && Student::useService()->getPersonAgreementTypeByName($Meta['Type'])){
                $form = FrontendPersonAgreement::getTypeForm();
                // Fehler
                $form->setError('Meta[Type]', 'Name der Kategorie ist bereits in Verwendung');
                // Buttons hinzufügen
                $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
                    (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineSaveEditType($PersonId, $TypeId)),
                    (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineCloseModal('ModalAgreementStructureEditType'))
                )))));
                $isError = true;
            }
        }
        if(!$isError){
            $Name = $Meta['Type'];
            $Description = isset($Meta['Description']) ?? $Meta['Description'];
            Student::useService()->updatePersonAgreementType($tblPersonAgreementType, $Name, $Description);
            return new Success('Bearbeiten war Erfolgreich!')
                .ApiPersonAgreementStructure::pipelineEditPersonAgreementStructure($PersonId)
                .ApiPersonAgreementStructure::pipelineCloseModal('ModalAgreementStructureEditType');
        }
        return new Well($form);
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function openDestroyTypeModal($PersonId, $TypeId)
    {

        $tblPersonAgreementType = Student::useService()->getPersonAgreementTypeById($TypeId);
        $TypeName = $tblPersonAgreementType->getName();
        $Agreement = '';
        $AgreementCount = 0;
        $tblPersonAgreementList = Student::useService()->getPersonAgreementAllByType($tblPersonAgreementType);
        if($tblPersonAgreementList){
            $AgreementCount = count($tblPersonAgreementList);
            $Agreement = new Warning('Dieser Typ wird '.$AgreementCount.' mal verwendet');
        }

        if(!$Agreement){
            $Agreement = new Success('der Typ wird nicht verwendet', null, false, 5, 5);
        }

        $Panel = new Panel('Wollen Sie den Typ '.new Bold($TypeName).' wirklich entfernen?', $Agreement, Panel::PANEL_TYPE_DANGER);
        $ButtonYes = (new Primary('Ja', '#', new SuccessIcon()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineSaveDestroyType($PersonId, $TypeId));
        $ButtonNo = (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineCloseModal('ModalAgreementStructureDestroyType'));

        return new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn($Panel),
            new LayoutColumn($ButtonYes.$ButtonNo)
        ))));
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function saveDestroyTypeModal($PersonId, $TypeId)
    {

        $tblPersonAgreementType = Student::useService()->getPersonAgreementTypeById($TypeId);
        if(Student::useService()->destroyPersonAgreementType($tblPersonAgreementType)){
            return new Success('Kategorie wurde entfernt')
                .ApiPersonAgreementStructure::pipelineEditPersonAgreementStructure($PersonId)
                .ApiPersonAgreementStructure::pipelineCloseModal('ModalAgreementStructureDestroyType');
        }
        return new Danger('Kategorie konnte nicht entfernt werden');
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineEditPersonAgreementStructure($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'PersonAgreementContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editPersonAgreementStructure',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }
}