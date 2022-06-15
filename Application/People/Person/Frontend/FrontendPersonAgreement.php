<?php
namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\People\Meta\Agreement\ApiPersonAgreementStructure;
use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Agreement\Agreement;
use SPHERE\Application\People\Meta\Agreement\Service\Entity\TblPersonAgreementCategory;
use SPHERE\Application\People\Meta\Agreement\Service\Entity\TblPersonAgreementType;
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

/**
 * Class FrontendPersonAgreement
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendPersonAgreement extends FrontendReadOnly
{
    const TITLE = 'Datennutzung';

    /**
     * @param null $PersonId
     * @param int  $AllowEdit
     *
     * @return string
     */
    public static function getPersonAgreementContent($PersonId = null, $AllowEdit = 1)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))){
            return '';
        }

        $AuthorizedToCollectGroups[] = 'Mitarbeiter';
        $hasBlockChild = false;
        foreach ($AuthorizedToCollectGroups as $group) {
            if (($tblGroup = Group::useService()->getGroupByName(trim($group)))
                && Group::useService()->existsGroupPerson($tblGroup, $tblPerson)
            ) {
                $hasBlockChild = true;
                break;
            }
        }
        if(!$hasBlockChild){
            return '';
        }

        $AgreementPanelCategory = array();
        if(($tblAgreementCategoryAll = Agreement::useService()->getPersonAgreementCategoryAll())){
            array_walk($tblAgreementCategoryAll, function (TblPersonAgreementCategory $tblPersonAgreementCategory) use (&$AgreementPanelCategory, $tblPerson) {
                $tblAgreementTypeAll = Agreement::useService()->getPersonAgreementTypeAllByCategory($tblPersonAgreementCategory);
                if ($tblAgreementTypeAll) {
//                        $tblAgreementTypeAll = (new Extension)->getSorter($tblAgreementTypeAll)->sortObjectBy('Name');
                    $List = array();
                    array_walk($tblAgreementTypeAll, function (TblPersonAgreementType $tblPersonAgreementType) use (&$List, $tblPersonAgreementCategory, $tblPerson) {
                        if ($tblPerson) {
                            $isChecked = Agreement::useService()->getPersonAgreementByTypeAndPerson($tblPersonAgreementType, $tblPerson);
                        } else {
                            $isChecked = false;
                        }
                        $List[] = ($isChecked ? new Check() : new Unchecked()) . ' ' . $tblPersonAgreementType->getName();
                    });
                    $AgreementPanelCategory[] = new LayoutColumn(FrontendReadOnly::getSubContent($tblPersonAgreementCategory->getName(), $List), 3);
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
                ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditPersonAgreementContent($PersonId));
        }
        $StructureEdit = (new Link(new Edit() . ' Struktur bearbeiten', ApiPersonEdit::getEndpoint()))
            ->ajaxPipelineOnClick(ApiPersonAgreementStructure::pipelineEditPersonAgreementStructure($PersonId));
        $DivisionString = FrontendReadOnly::getDivisionString($tblPerson);

        return TemplateReadOnly::getContent(
            self::TITLE,
            $content,
            array($editLink, $StructureEdit),
            'der Person ' . new Bold(new Success($tblPerson->getFullName())).$DivisionString,
            new TileSmall()
        );

    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function getEditPersonAgreementContent($PersonId = null)
    {

        $tblPerson = false;
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $Global = $this->getGlobal();
            if ($tblPersonAgreementAll = Agreement::useService()->getPersonAgreementAllByPerson($tblPerson)) {
                foreach ($tblPersonAgreementAll as $tblPersonAgreement) {
                    $Global->POST['Meta']['Agreement']
                    [$tblPersonAgreement->getTblPersonAgreementType()->getTblPersonAgreementCategory()->getId()]
                    [$tblPersonAgreement->getTblPersonAgreementType()->getId()] = 1;
                }
            }

            $Global->savePost();
        }

        return $this->getEditPersonAgreementTitle($tblPerson ? $tblPerson : null)
            . new Well($this->getEditPersonAgreementForm($tblPerson ? $tblPerson : null));
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
    private function getEditPersonAgreementTitle(TblPerson $tblPerson = null)
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
    private function getEditPersonAgreementForm(TblPerson $tblPerson = null)
    {

        /**
         * Panel: Agreement
         */
        $AgreementPanel = array();
        $AgreementPanelHead = array();
        if(($tblAgreementCategoryAll = Agreement::useService()->getPersonAgreementCategoryAll())){
            $PanelCount = 1;
            array_walk($tblAgreementCategoryAll, function (TblPersonAgreementCategory $tblCategory) use (&$AgreementPanel, &$AgreementPanelHead, &$PanelCount) {
                // Toggle on Category
                $CategoryCheckboxList = array();
                $AgreementPanel[$PanelCount] = array();
                if (($tblAgreementTypeAll = Agreement::useService()->getPersonAgreementTypeAllByCategory($tblCategory))) {
                    array_walk($tblAgreementTypeAll, function (TblPersonAgreementType $tblType) use (&$AgreementPanel, &$CategoryCheckboxList, $tblCategory) {
                        $CategoryCheckboxList[] = 'Meta[Agreement][' . $tblCategory->getId() . '][' . $tblType->getId() . ']';
                    });
                }
                $AgreementPanelHead[$PanelCount] = new PullClear(new PullLeft(new Bold($tblCategory->getName()))
                .new PullRight(new ToggleSelective('wählen/abwählen', $CategoryCheckboxList)));

                if ($tblAgreementTypeAll) {
//                    $tblAgreementTypeAll = $this->getSorter($tblAgreementTypeAll)->sortObjectBy('Name');
                    array_walk($tblAgreementTypeAll, function (TblPersonAgreementType $tblType) use (&$AgreementPanel, $tblCategory, &$PanelCount) {
                        $AgreementPanel[$PanelCount][] =new CheckBox('Meta[Agreement]['.$tblCategory->getId().']['.$tblType->getId().']', $tblType->getName(), 1);
                    });
                }
                $PanelCount++;
            });
        }
        $AgreementLayout = array();
        if(!empty($AgreementPanel)){
            foreach($AgreementPanel as $Key => $AgreementPanelOne){
                $AgreementLayout[] = new LayoutColumn(new Panel($AgreementPanelHead[$Key]
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
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSavePersonAgreementContent($tblPerson ? $tblPerson->getId() : 0)),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelPersonAgreementContent($tblPerson ? $tblPerson->getId() : 0))
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
            new FormColumn(new TextField('Meta[Type]', '', 'Name des Eintrag\'s')),
            new FormColumn(new Success('<div style="height:5px"></div>')),
        )))))->disableSubmitAction();
    }
}