<?php
namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\People\Meta\Agreement\ApiStudentAgreementStructure;
use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementCategory;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\TileSmall;
use SPHERE\Common\Frontend\Icon\Repository\Unchecked;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
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
use SPHERE\System\Extension\Extension;

/**
 * Class FrontendStudentAgreement
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendStudentAgreement extends FrontendReadOnly
{
    const TITLE = 'Schülerakte - Datenschutz';

    /**
     * @param null $PersonId
     * @param int  $AllowEdit
     *
     * @return string
     */
    public static function getStudentAgreementContent($PersonId = null, $AllowEdit = 1)
    {

        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
            $AgreementPanelCategory = array();
            if(($tblAgreementCategoryAll = Student::useService()->getStudentAgreementCategoryAll())){
                array_walk($tblAgreementCategoryAll, function (TblStudentAgreementCategory $tblStudentAgreementCategory) use (&$AgreementPanelCategory, $tblStudent) {
                    $tblAgreementTypeAll = Student::useService()->getStudentAgreementTypeAllByCategory($tblStudentAgreementCategory);
                    if ($tblAgreementTypeAll) {
//                        $tblAgreementTypeAll = (new Extension)->getSorter($tblAgreementTypeAll)->sortObjectBy('Name');
                        $List = array();
                        array_walk($tblAgreementTypeAll, function (TblStudentAgreementType $tblStudentAgreementType) use (&$List, $tblStudentAgreementCategory, $tblStudent) {
                            if ($tblStudent) {
                                $isChecked = Student::useService()->getStudentAgreementByTypeAndStudent($tblStudentAgreementType, $tblStudent);
                            } else {
                                $isChecked = false;
                            }
                            $List[] = ($isChecked ? new Check() : new Unchecked()) . ' ' . $tblStudentAgreementType->getName();
                        });
                        $AgreementPanelCategory[] = new LayoutColumn(FrontendReadOnly::getSubContent($tblStudentAgreementCategory->getName(), $List), 3);
                    }
                });
            }

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(
                    $AgreementPanelCategory
                ),
            )));

            $editLink = '';
            if($AllowEdit == 1){
                $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                    ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditStudentAgreementContent($PersonId));
            }
            $StructureEdit = (new Link(new Edit() . ' Struktur bearbeiten', ApiPersonEdit::getEndpoint()))
                ->ajaxPipelineOnClick(ApiStudentAgreementStructure::pipelineEditStudentAgreementStructure($PersonId));
            $DivisionString = FrontendReadOnly::getDivisionString($tblPerson);

            return TemplateReadOnly::getContent(
                self::TITLE,
                $content,
                array($editLink, $StructureEdit),
                'der Person ' . new Bold(new Success($tblPerson->getFullName())).$DivisionString,
                new TileSmall()
            );
        }

        return '';
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function getEditStudentAgreementContent($PersonId = null)
    {

        $tblPerson = false;
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $Global = $this->getGlobal();
            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                if ($tblStudentAgreementAll = Student::useService()->getStudentAgreementAllByStudent($tblStudent)) {
                    foreach ($tblStudentAgreementAll as $tblStudentAgreement) {
                        $Global->POST['Meta']['Agreement']
                        [$tblStudentAgreement->getTblStudentAgreementType()->getTblStudentAgreementCategory()->getId()]
                        [$tblStudentAgreement->getTblStudentAgreementType()->getId()] = 1;
                    }
                }

                $Global->savePost();
            }
        }

        return $this->getEditStudentAgreementTitle($tblPerson ? $tblPerson : null)
            . new Well($this->getEditStudentAgreementForm($tblPerson ? $tblPerson : null));
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function getEditStudentAgreementStructure($PersonId = null)
    {

        return $this->getEditStudentAgreementStructureTitle();
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return string
     */
    private function getEditStudentAgreementTitle(TblPerson $tblPerson = null)
    {
        return new Title(new TileSmall() . ' ' . self::TITLE, self::getEditTitleDescription($tblPerson))
            . self::getDataProtectionMessage();
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return string
     */
    private function getEditStudentAgreementStructureTitle()
    {
        return new Title(new TileSmall() . ' ' . self::TITLE, 'Struktur Datenschutz bearbeiten');
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Form
     */
    private function getEditStudentAgreementForm(TblPerson $tblPerson = null)
    {

        /**
         * Panel: Agreement
         */
        $AgreementPanel = array();
        $CheckboxList = array();
        if(($tblAgreementCategoryAll = Student::useService()->getStudentAgreementCategoryAll())){
            $PanelCount = 1;
            array_walk($tblAgreementCategoryAll,
                function (TblStudentAgreementCategory $tblStudentAgreementCategory) use (&$AgreementPanel, &$CheckboxList, &$PanelCount) {
                $AgreementPanel[$PanelCount] = array();
                $tblAgreementTypeAll = Student::useService()->getStudentAgreementTypeAllByCategory($tblStudentAgreementCategory);
                // Extra Toggle on Category
                $CategoryCheckboxList = array();
                array_walk($tblAgreementTypeAll,
                    function (TblStudentAgreementType $tblStudentAgreementType) use (&$AgreementPanel, &$CategoryCheckboxList,
                        $tblStudentAgreementCategory) {
                        $CategoryCheckboxList[] = 'Meta[Agreement]['.$tblStudentAgreementCategory->getId().']['.$tblStudentAgreementType->getId().']';
                    }
                );
                array_push($AgreementPanel[$PanelCount],new PullClear(new PullLeft(new Bold($tblStudentAgreementCategory->getName()))
                .new PullRight(new ToggleSelective('wählen/abwählen', $CategoryCheckboxList))
                ));
                if ($tblAgreementTypeAll) {
//                    $tblAgreementTypeAll = $this->getSorter($tblAgreementTypeAll)->sortObjectBy('Name');
                    array_walk($tblAgreementTypeAll,
                    function (TblStudentAgreementType $tblStudentAgreementType) use (&$AgreementPanel, &$CheckboxList,
                        $tblStudentAgreementCategory, &$PanelCount) {
                        $CheckboxList[] = 'Meta[Agreement]['.$tblStudentAgreementCategory->getId().']['.$tblStudentAgreementType->getId().']';
                        array_push($AgreementPanel[$PanelCount],
                            new CheckBox('Meta[Agreement]['.$tblStudentAgreementCategory->getId().']['.$tblStudentAgreementType->getId().']',
                                $tblStudentAgreementType->getName(), 1)
                        );
                    });
                }
                $PanelCount++;
            });
        }

//        $CheckboxButton = new ToggleSelective('Alle wählen/abwählen', $CheckboxList);
//        $AgreementPanel = new Panel(new PullClear('Einverständniserklärung zur Datennutzung') // .new PullRight($CheckboxButton)
//            , $AgreementPanel, Panel::PANEL_TYPE_INFO);
        $AgreementLayout = array();
        if(!empty($AgreementPanel)){
            foreach($AgreementPanel as $AgreementPanelOne){
                $AgreementLayout[] = new LayoutColumn(new Panel(new PullClear('Einverständniserklärung zur Datennutzung') // .new PullRight($CheckboxButton)
                    , $AgreementPanelOne, Panel::PANEL_TYPE_INFO), 3);
            }
        }

        $Form = new Form(array(
            new FormGroup(array(
                new FormRow(
                    $AgreementLayout
                ),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiPersonEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveStudentAgreementContent($tblPerson ? $tblPerson->getId() : 0)),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelStudentAgreementContent($tblPerson ? $tblPerson->getId() : 0))
                    ))
                ))
            ))
        ));

        return $Form;
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
            new FormColumn(new CheckBox('Meta[isUnlocked]', 'Typ kann vom Lehrer gesetzt werden', true)),
            new FormColumn(new Success('<div style="height:10px"></div>')),
        )))))->disableSubmitAction();
    }
}