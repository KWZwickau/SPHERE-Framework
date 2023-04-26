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
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
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
use SPHERE\Common\Frontend\Text\Repository\Italic;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\System\Extension\Extension;

class ApiStudentAgreementStructure extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('editStudentAgreementStructure');

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
            ApiStudentAgreementStructure::API_TARGET => 'openCreateCategoryModal',
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
            ApiStudentAgreementStructure::API_TARGET => 'saveCreateCategoryModal',
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
            ApiStudentAgreementStructure::API_TARGET => 'openEditCategoryModal',
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
            ApiStudentAgreementStructure::API_TARGET => 'saveEditCategoryModal',
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
            ApiStudentAgreementStructure::API_TARGET => 'openDestroyCategoryModal',
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
            ApiStudentAgreementStructure::API_TARGET => 'saveDestroyCategoryModal',
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
            ApiStudentAgreementStructure::API_TARGET => 'openCreateTypeModal',
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
            ApiStudentAgreementStructure::API_TARGET => 'saveCreateTypeModal',
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
            ApiStudentAgreementStructure::API_TARGET => 'openEditTypeModal',
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
            ApiStudentAgreementStructure::API_TARGET => 'saveEditTypeModal',
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
            ApiStudentAgreementStructure::API_TARGET => 'openDestroyTypeModal',
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
            ApiStudentAgreementStructure::API_TARGET => 'saveDestroyTypeModal',
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
    public function editStudentAgreementStructure($PersonId = null)
    {

        $CategoryList = array();

        if(($tblStudentAgreementCategoryList = Student::useService()->getStudentAgreementCategoryAll())){
            foreach($tblStudentAgreementCategoryList as $tblStudentAgreementCategory){
                $CategoryList[$tblStudentAgreementCategory->getName()][] = new PullClear(new Bold($tblStudentAgreementCategory->getName()) .new PullRight(
                    (new Link(new Edit(), '#'))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineOpenEditCategoryModal($PersonId, $tblStudentAgreementCategory->getId()))
                    .(new Link(new DangerText(new Disable()), '#'))
                        ->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineOpenDestroyCategoryModal($PersonId, $tblStudentAgreementCategory->getId()))
                ));
                if(($tblStudentAgreementTypeList = Student::useService()->getStudentAgreementTypeAllByCategory($tblStudentAgreementCategory))){
                    foreach($tblStudentAgreementTypeList as $tblStudentAgreementType){
                        if($tblStudentAgreementType->getIsUnlocked()){
                            $CategoryList[$tblStudentAgreementCategory->getName()][] = new PullClear(
                                $tblStudentAgreementType->getName() . ' ' . new ToolTip(new Info(), 'Lehrer können diesen Eintrag setzen') . new PullRight(
                                    (new Link(new Edit(), '#'))
                                        ->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineOpenEditTypeModal($PersonId, $tblStudentAgreementType->getId()))
                                    .(new Link(new DangerText(new Disable()), '#'))
                                        ->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineOpenDestroyTypeModal($PersonId, $tblStudentAgreementType->getId()))
                                ));
                        } else {
                            $CategoryList[$tblStudentAgreementCategory->getName()][] = new PullClear($tblStudentAgreementType->getName().new PullRight(
                                    (new Link(new Edit(), '#'))
                                        ->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineOpenEditTypeModal($PersonId, $tblStudentAgreementType->getId()))
                                    .(new Link(new DangerText(new Disable()), '#'))
                                        ->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineOpenDestroyTypeModal($PersonId, $tblStudentAgreementType->getId()))
                                ));
                        }
                    }
                }
                if(count($CategoryList[$tblStudentAgreementCategory->getName()]) < 9
                    || count($CategoryList[$tblStudentAgreementCategory->getName()]) == 0
                ){
                    $CategoryList[$tblStudentAgreementCategory->getName()][] = (new Link(new Plus().'Eintrag hinzufügen', '#'))
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
                (new Link(new Plus().'Kategorie hinzufügen', '#'))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineOpenCreateCategoryModal($PersonId))
            )), 3);
        }


        return (new FrontendStudentAgreement())->getEditStudentAgreementStructure($PersonId)
            .new Well(
                ApiStudentAgreementStructure::receiverModal('Kategorie hinzufügen', 'ModalAgreementStructureCreateCategory')
                .ApiStudentAgreementStructure::receiverModal('Kategorie bearbeiten', 'ModalAgreementStructureEditCategory')
                .ApiStudentAgreementStructure::receiverModal('Kategorie entfernen', 'ModalAgreementStructureDestroyCategory')
                .ApiStudentAgreementStructure::receiverModal('Eintrag hinzufügen', 'ModalAgreementStructureCreateType')
                .ApiStudentAgreementStructure::receiverModal('Eintrag bearbeiten', 'ModalAgreementStructureEditType')
                .ApiStudentAgreementStructure::receiverModal('Eintrag entfernen', 'ModalAgreementStructureDestroyType')
                .new Layout(new LayoutGroup(new LayoutRow($LayoutColumnList)))
                .(new Primary('Schließen', ApiPersonEdit::getEndpoint(), new ChevronLeft()))
                    ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelStudentAgreementContent($PersonId))
            );
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function openCreateCategoryModal($PersonId)
    {

        $form = FrontendStudentAgreement::getCategoryForm();
        // Buttons hinzufügen
        $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
            (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineSaveCreateCategory($PersonId)),
            (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineCloseModal('ModalAgreementStructureCreateCategory'))
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
            $form = FrontendStudentAgreement::getCategoryForm();
            // Fehler
            $form->setError('Meta[Category]', 'Bitte geben Sie etwas ein');
            // Buttons hinzufügen
            $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
                (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineSaveCreateCategory($PersonId)),
                (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineCloseModal('ModalAgreementStructureCreateCategory'))
            )))));
            $isError = true;
        } else {
            if(Student::useService()->getStudentAgreementCategoryByName($Meta['Category'])){
                $form = FrontendStudentAgreement::getCategoryForm();
                // Fehler
                $form->setError('Meta[Category]', 'Name der Kategorie ist bereits in Verwendung');
                // Buttons hinzufügen
                $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
                    (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineSaveCreateCategory($PersonId)),
                    (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineCloseModal('ModalAgreementStructureCreateCategory'))
                )))));
                $isError = true;
            }
        }
        if(!$isError){
            $Name = $Meta['Category'];
            $Description = isset($Meta['Description']) ?? $Meta['Description'];
            Student::useService()->createStudentAgreementCategory($Name, $Description);
            return new Success('Anlegen war Erfolgreich!')
                .ApiStudentAgreementStructure::pipelineEditStudentAgreementStructure($PersonId)
                .ApiStudentAgreementStructure::pipelineCloseModal('ModalAgreementStructureCreateCategory');
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

        if(($tblStudentAgreementCategory = Student::useService()->getStudentAgreementCategoryById($CategoryId))){
            $_POST['Meta']['Category'] = $tblStudentAgreementCategory->getName();
        }

        $form = FrontendStudentAgreement::getCategoryForm();
        // Buttons hinzufügen
        $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
            (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineSaveEditCategory($PersonId, $CategoryId)),
            (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineCloseModal('ModalAgreementStructureEditCategory'))
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
        $tblStudentAgreementCategory = Student::useService()->getStudentAgreementCategoryById($CategoryId);
        if(!isset($Meta['Category']) || $Meta['Category'] == ''){
            $form = FrontendStudentAgreement::getCategoryForm();
            // Fehler
            $form->setError('Meta[Category]', 'Bitte geben Sie etwas ein');
            // Buttons hinzufügen
            $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
                (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineSaveEditCategory($PersonId, $CategoryId)),
                (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineCloseModal('ModalAgreementStructureEditCategory'))
            )))));
            $isError = true;
        } else {
            if($Meta['Category'] != $tblStudentAgreementCategory->getName()
            && Student::useService()->getStudentAgreementCategoryByName($Meta['Category'])){
                $form = FrontendStudentAgreement::getCategoryForm();
                // Fehler
                $form->setError('Meta[Category]', 'Name der Kategorie ist bereits in Verwendung');
                // Buttons hinzufügen
                $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
                    (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineSaveEditCategory($PersonId, $CategoryId)),
                    (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineCloseModal('ModalAgreementStructureEditCategory'))
                )))));
                $isError = true;
            }
        }
        if(!$isError){
            $Name = $Meta['Category'];
            $Description = isset($Meta['Description']) ?? $Meta['Description'];
            Student::useService()->updateStudentAgreementCategory($tblStudentAgreementCategory, $Name, $Description);
            return new Success('Bearbeiten war Erfolgreich!')
                .ApiStudentAgreementStructure::pipelineEditStudentAgreementStructure($PersonId)
                .ApiStudentAgreementStructure::pipelineCloseModal('ModalAgreementStructureEditCategory');
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

        $tblStudentAgreementCategory = Student::useService()->getStudentAgreementCategoryById($CategoryId);
        $CategoryName = $tblStudentAgreementCategory->getName();
        $TypeWithCount = array();
        if(($tblStudentAgreementTypeList = Student::useService()->getStudentAgreementTypeAllByCategory($tblStudentAgreementCategory))){
            foreach($tblStudentAgreementTypeList as $tblStudentAgreementType){
                $AgreementCount = 0;
                $tblStudentAgreementList = Student::useService()->getStudentAgreementAllByType($tblStudentAgreementType);
                if($tblStudentAgreementList){
                    $AgreementCount = count($tblStudentAgreementList);
                }
                $TypeWithCount[] = $tblStudentAgreementType->getName().new ToolTip(new Muted(new Small(' ('.$AgreementCount.')')), 'Verwendungen');
            }
        }

        if(empty($TypeWithCount)){
            $TypeWithCount[] = new Success('Keine Einträge zur Kategorie hinterlegt', null, false, 5, 5);
        }

        $Panel = new Panel('Wollen Sie die Kategorie '.new Bold($CategoryName).' wirklich entfernen?', $TypeWithCount, Panel::PANEL_TYPE_DANGER);
        $ButtonYes = (new Primary('Ja', '#', new SuccessIcon()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineSaveDestroyCategory($PersonId, $CategoryId));
        $ButtonNo = (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineCloseModal('ModalAgreementStructureDestroyCategory'));
        if($tblStudentAgreementTypeList){
            return new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(new Danger(new Bold('Löschen der Kategorie "'.$CategoryName.'" nicht möglich!')
                .new Container('Bitte löschen Sie zuerst alle Struktureinträge in dieser Kategorie.')))
            ))));
        }

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

        $tblStudentAgreementCategory = Student::useService()->getStudentAgreementCategoryById($CategoryId);
        if(Student::useService()->destroyStudentAgreementCategory($tblStudentAgreementCategory)){
            return new Success('Kategorie wurde entfernt')
                .ApiStudentAgreementStructure::pipelineEditStudentAgreementStructure($PersonId)
                .ApiStudentAgreementStructure::pipelineCloseModal('ModalAgreementStructureDestroyCategory');
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

        $form = FrontendStudentAgreement::getTypeForm();
        // Buttons hinzufügen
        $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
            (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineSaveCreateType($PersonId, $CategoryId)),
            (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineCloseModal('ModalAgreementStructureCreateType'))
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
        $tblStudentAgreementCategory = Student::useService()->getStudentAgreementCategoryById($CategoryId);
        if(!isset($Meta['Type']) || $Meta['Type'] == ''){
            $form = FrontendStudentAgreement::getTypeForm();
            // Fehler
            $form->setError('Meta[Type]', 'Bitte geben Sie etwas ein');
            // Buttons hinzufügen
            $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
                (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineSaveCreateType($PersonId, $CategoryId)),
                (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineCloseModal('ModalAgreementStructureCreateType'))
            )))));
            $isError = true;
        } else {
            if(Student::useService()->getStudentAgreementTypeByNameAndCategory($Meta['Type'], $tblStudentAgreementCategory)){
                $form = FrontendStudentAgreement::getTypeForm();
                // Fehler
                $form->setError('Meta[Type]', 'Name des Eintrag\'s ist bereits in Verwendung');
                // Buttons hinzufügen
                $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
                    (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineSaveCreateType($PersonId, $CategoryId)),
                    (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineCloseModal('ModalAgreementStructureCreateType'))
                )))));
                $isError = true;
            }
        }
        if(!$isError){
            $Name = $Meta['Type'];
            $Description = isset($Meta['Description']) ?? $Meta['Description'];
            $isUnlocked = isset($Meta['isUnlocked']);
            Student::useService()->createStudentAgreementType($tblStudentAgreementCategory, $Name, $Description, $isUnlocked);
            return new Success('Anlegen war Erfolgreich!')
                .ApiStudentAgreementStructure::pipelineEditStudentAgreementStructure($PersonId)
                .ApiStudentAgreementStructure::pipelineCloseModal('ModalAgreementStructureCreateType');
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

        if(($tblStudentAgreementType = Student::useService()->getStudentAgreementTypeById($TypeId))){
            $_POST['Meta']['Type'] = $tblStudentAgreementType->getName();
            $_POST['Meta']['isUnlocked'] = $tblStudentAgreementType->getIsUnlocked();
        }

        $form = FrontendStudentAgreement::getTypeForm();
        // Buttons hinzufügen
        $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
            (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineSaveEditType($PersonId, $TypeId)),
            (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineCloseModal('ModalAgreementStructureEditType'))
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
        $tblStudentAgreementType = Student::useService()->getStudentAgreementTypeById($TypeId);
        if(!isset($Meta['Type']) || $Meta['Type'] == ''){
            $form = FrontendStudentAgreement::getTypeForm();
            // Fehler
            $form->setError('Meta[Type]', 'Bitte geben Sie etwas ein');
            // Buttons hinzufügen
            $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
                (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineSaveEditType($PersonId, $TypeId)),
                (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineCloseModal('ModalAgreementStructureEditType'))
            )))));
            $isError = true;
        } else {
            if($Meta['Type'] != $tblStudentAgreementType->getName()
            && Student::useService()->getStudentAgreementTypeByNameAndCategory($Meta['Type'], $tblStudentAgreementType->getTblStudentAgreementCategory())){
                $form = FrontendStudentAgreement::getTypeForm();
                // Fehler
                $form->setError('Meta[Type]', 'Name des Eintrag\'s ist bereits in Verwendung');
                // Buttons hinzufügen
                $form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(array(
                    (new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineSaveEditType($PersonId, $TypeId)),
                    (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineCloseModal('ModalAgreementStructureEditType'))
                )))));
                $isError = true;
            }
        }
        if(!$isError){
            $Name = $Meta['Type'];
            $Description = isset($Meta['Description']) ?? $Meta['Description'];
            $isUnlocked = isset($Meta['isUnlocked']);
            Student::useService()->updateStudentAgreementType($tblStudentAgreementType, $Name, $Description, $isUnlocked);
            return new Success('Bearbeiten war Erfolgreich!')
                .ApiStudentAgreementStructure::pipelineEditStudentAgreementStructure($PersonId)
                .ApiStudentAgreementStructure::pipelineCloseModal('ModalAgreementStructureEditType');
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

        $tblStudentAgreementType = Student::useService()->getStudentAgreementTypeById($TypeId);
        $TypeName = $tblStudentAgreementType->getName();
        $Agreement = '';
        $AgreementCount = 0;
        $tblStudentAgreementList = Student::useService()->getStudentAgreementAllByType($tblStudentAgreementType);
        if($tblStudentAgreementList){
            $AgreementCount = count($tblStudentAgreementList);
            $Agreement = new Warning('Dieser Eintrag wird '.$AgreementCount.' mal verwendet');
        }

        if(!$Agreement){
            $Agreement = new Success('der Eintrag wird nicht verwendet', null, false, 5, 5);
        }

        $Panel = new Panel('Wollen Sie den Eintrag '.new Bold($TypeName).' wirklich entfernen?', $Agreement, Panel::PANEL_TYPE_DANGER);
        $ButtonYes = (new Primary('Ja', '#', new SuccessIcon()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineSaveDestroyType($PersonId, $TypeId));
        $ButtonNo = (new DangerLink('Abbrechen', '#', new Disable()))->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineCloseModal('ModalAgreementStructureDestroyType'));

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

        $tblStudentAgreementType = Student::useService()->getStudentAgreementTypeById($TypeId);
        if(Student::useService()->destroyStudentAgreementType($tblStudentAgreementType)){
            return new Success('Eintrag wurde entfernt')
                .ApiStudentAgreementStructure::pipelineEditStudentAgreementStructure($PersonId)
                .ApiStudentAgreementStructure::pipelineCloseModal('ModalAgreementStructureDestroyType');
        }
        return new Danger('Eintrag konnte nicht entfernt werden');
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineEditStudentAgreementStructure($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'StudentAgreementContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editStudentAgreementStructure',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }
}