<?php
namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\People\Meta\Agreement\ApiPersonAgreementStructure;
use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Agreement\Agreement;
use SPHERE\Application\People\Meta\Agreement\Service\Entity\TblPersonAgreementCategory;
use SPHERE\Application\People\Meta\Agreement\Service\Entity\TblPersonAgreementType;
use SPHERE\Application\People\Meta\Masern\Masern;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMasernInfo;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Hospital;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\TileSmall;
use SPHERE\Common\Frontend\Icon\Repository\Unchecked;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title as TitleLayout;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\ToggleSelective;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Link\Repository\Link;

/**
 * Class FrontendPersonMasern
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendPersonMasern extends FrontendReadOnly
{
    const TITLE = 'Masern - Impfung';

    /**
     * @param null $PersonId
     * @param int  $AllowEdit
     *
     * @return string
     */
    public static function getPersonMasernContent($PersonId = null, $AllowEdit = 1)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))){
            return '';
        }

        $AuthorizedToCollectGroups[] = TblGroup::META_TABLE_STAFF;
        $hasBlock = false;
        foreach ($AuthorizedToCollectGroups as $group) {
            if (($tblGroup = Group::useService()->getGroupByMetaTable($group))
                && Group::useService()->existsGroupPerson($tblGroup, $tblPerson)
            ) {
                $hasBlock = true;
                break;
            }
        }
        if(!$hasBlock){
            return '';
        }

        $MasernDate = '';
        $DocumentType = '';
        $CreatorType = '';
        if(($tblPersonMasern = Masern::useService()->getPersonMasernByPerson($tblPerson))){
            $MasernDate = $tblPersonMasern->getMasernDate();
            if(($tblDocumentType = $tblPersonMasern->getMasernDocumentType())){
                $DocumentType = $tblDocumentType->getTextLong();
            }
            if(($TblCreatorType = $tblPersonMasern->getMasernCreatorType())){
                $CreatorType = $TblCreatorType->getTextLong();
            }
        }

        $ColumnTemp[] = FrontendReadOnly::getLayoutColumnLabel('Datum (vorgelegt am)', 2);
        $ColumnTemp[] = FrontendReadOnly::getLayoutColumnValue($MasernDate, 2);
        $ColumnTemp[] = FrontendReadOnly::getLayoutColumnLabel('Art der Bescheinigung', 4);
        $ColumnTemp[] = FrontendReadOnly::getLayoutColumnLabel('Bescheinigung durch', 4);
        $ColumnTemp[] = FrontendReadOnly::getLayoutColumnValue('&nbsp;', 4);
        $ColumnTemp[] = FrontendReadOnly::getLayoutColumnValue($DocumentType, 4);
        $ColumnTemp[] = FrontendReadOnly::getLayoutColumnValue($CreatorType, 4);

        $Column = new LayoutColumn(
            new Layout(new LayoutGroup(new LayoutRow(
                $ColumnTemp
            )))
        );

        $content = new Layout(new LayoutGroup(array(new LayoutRow(
            $Column
        ))));

        $editLink = '';
        if($AllowEdit == 1){
            $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditPersonMasernContent($PersonId));
        }
        $DivisionString = FrontendReadOnly::getDivisionString($tblPerson);

        return TemplateReadOnly::getContent(
            self::TITLE,
            $content,
            array($editLink),
            'der Person ' . new Bold(new Success($tblPerson->getFullName())).$DivisionString,
            new Hospital()
        );

    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function getEditPersonMasernContent($PersonId = null)
    {

        $tblPerson = false;
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $Global = $this->getGlobal();
            if ($tblPersonMasern = Masern::useService()->getPersonMasernByPerson($tblPerson)) {
                $Global->POST['Meta']['Masern']['Date'] = $tblPersonMasern->getMasernDate();
                if(($tblDocumentType = $tblPersonMasern->getMasernDocumentType())){
                    $Global->POST['Meta']['Masern']['DocumentType'] = $tblDocumentType->getId();
                }
                if(($tblCreatorType = $tblPersonMasern->getMasernCreatorType())){
                    $Global->POST['Meta']['Masern']['CreatorType'] = $tblCreatorType->getId();
                }
            }

            $Global->savePost();
        }

        return $this->getEditPersonMasernTitle($tblPerson ? $tblPerson : null)
            . new Well($this->getEditPersonMasernForm($tblPerson ? $tblPerson : null));
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function getEditPersonAgreementStructure($PersonId = null)
    {

        return $this->getEditPersonAgreementStructureTitle();
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return string
     */
    private function getEditPersonMasernTitle(TblPerson $tblPerson = null)
    {
        return new Title(new TileSmall() . ' ' . self::TITLE, self::getEditTitleDescription($tblPerson))
            . self::getDataProtectionMessage();
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return string
     */
    private function getEditPersonAgreementStructureTitle()
    {
        return new Title(new TileSmall() . ' ' . self::TITLE, 'Struktur Datennutzung bearbeiten');
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Form
     */
    private function getEditPersonMasernForm(TblPerson $tblPerson = null)
    {
        $NodeMasern = 'Masern-Impfpflicht';

        $PanelContentArray = array();
        $FieldDate = new DatePicker('Meta[Masern][Date]', null, 'Datum (vorgelegt am)');

        // Document
        $tblStudentMasernInfoDocumentList = Student::useService()->getStudentMasernInfoByType(TblStudentMasernInfo::TYPE_DOCUMENT);
        $FieldDocumentType = new SelectBox('Meta[Masern][DocumentType]', 'Art der Bescheinigung', array('TextLong' => $tblStudentMasernInfoDocumentList));

        // Creator
        $tblStudentMasernInfoProofList = Student::useService()->getStudentMasernInfoByType(TblStudentMasernInfo::TYPE_CREATOR);
        $FieldCreatorType = new SelectBox('Meta[Masern][CreatorType]', 'Bescheinigung, dass der Nachweis bereits vorgelegt wurde, durch', array('TextLong' => $tblStudentMasernInfoProofList));

        $form = new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('', $FieldDate, Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('', $FieldDocumentType, Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('', $FieldCreatorType, Panel::PANEL_TYPE_INFO)
                        , 4))),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiPersonEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSavePersonMasernContent($tblPerson ? $tblPerson->getId() : 0)),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelPersonMasernContent($tblPerson ? $tblPerson->getId() : 0))
                    ))
                ))
            ))));
        $form->disableSubmitAction();
        return $form;
    }

    /**
     * @return Form
     */
    public static function getCategoryForm()
    {

        return (new Form(new FormGroup(new FormRow(array(
            new FormColumn(new TextField('Meta[Category]', '', 'Name der Kategorie')),
            new FormColumn(new Success('<div style="height:5px"></div>')),
        )))))->disableSubmitAction();
    }

    /**
     * @return Form
     */
    public static function getTypeForm()
    {

        return (new Form(new FormGroup(new FormRow(array(
            new FormColumn(new TextField('Meta[Type]', '', 'Name des Typ\'s')),
            new FormColumn(new Success('<div style="height:5px"></div>')),
        )))))->disableSubmitAction();
    }
}