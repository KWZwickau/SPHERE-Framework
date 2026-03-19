<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\MinusSign;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Italic;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Stage;

abstract class FrontendGradeType extends FrontendGradeBookSelect
{
    /**
     * @return Stage
     * @noinspection PhpUnused
     */
    public function frontendGradeType(): Stage
    {
        $Stage = new Stage('Zensuren-Typ', 'Übersicht');
        $Stage->setMessage('Hier werden die Zensuren-Typen verwaltet. Bei den Zensuren-Typen wird zwischen den beiden
            Kategorien: Kopfnote (z.B. Betragen, Mitarbeit, Fleiß usw.) und Leistungsüberprüfung
            (z.B. Klassenarbeit, Leistungskontrolle usw.) unterschieden.');

        $addLink = (new Primary('Zensuren-Typ hinzufügen', ApiGradeType::getEndpoint(), new Plus()))
            ->ajaxPipelineOnClick(ApiGradeType::pipelineOpenCreateGradeTypeModal());

        $Stage->setContent(
            $addLink
            . ApiGradeType::receiverModal()
            . ApiGradeType::receiverBlock($this->loadGradeTypeTable(), 'GradeTypeContent')
        );

        return $Stage;
    }

    /**
     * @return string
     */
    public function loadGradeTypeTable(): string
    {
        $dataList = array();
        if (($tblGradeTypeAll = Grade::useService()->getGradeTypeAll(true))) {
            array_walk($tblGradeTypeAll, function (TblGradeType $tblGradeType) use (&$dataList) {
                $extendDescription = '';
                if ($tblGradeType->getIsIgnoredByScoreRule()) {
                    $extendDescription = new Italic(' (Zensuren-Typ wird bei Berechnungsvorschriften nicht mit berechnet)');
                } elseif ($tblGradeType->getIsPartGrade()) {
                    $extendDescription = new Italic(' (Teilnote)');
                }

                $item['DisplayName'] = $tblGradeType->getIsHighlighted() ? new Bold($tblGradeType->getName()) : $tblGradeType->getName();
                $item['DisplayCode'] = $tblGradeType->getIsHighlighted() ? new Bold($tblGradeType->getCode()) : $tblGradeType->getCode();
                $category = $tblGradeType->getIsTypeBehavior() ? 'Kopfnote' : 'Leistungsüberprüfung';
                $item['Category'] = $tblGradeType->getIsHighlighted() ? new Bold($category) : $category;

                $item['Status'] = $tblGradeType->getIsActive()
                    ? new SuccessText(new PlusSign().' aktiv')
                    : new Warning(new MinusSign() . ' inaktiv');
                $item['Description'] = trim($tblGradeType->getDescription() . $extendDescription);
                $item['Option'] =
                    (new Standard('', ApiGradeType::getEndpoint(), new Edit(), [], 'Zensuren-Typ bearbeiten'))
                        ->ajaxPipelineOnClick(ApiGradeType::pipelineOpenEditGradeTypeModal($tblGradeType->getId()))
                    . ($tblGradeType->getIsActive()
                        ? (new Standard('', ApiGradeType::getEndpoint(), new MinusSign(), [], 'Zensuren-Typ deaktivieren'))
                            ->ajaxPipelineOnClick(ApiGradeType::pipelineOpenActiveGradeTypeModal($tblGradeType->getId()))
                        : (new Standard('', ApiGradeType::getEndpoint(), new PlusSign(), [], 'Zensuren-Typ aktivieren')))
                            ->ajaxPipelineOnClick(ApiGradeType::pipelineOpenActiveGradeTypeModal($tblGradeType->getId()))
                    . ($tblGradeType->getIsUsed()
                        ? ''
                        : (new Standard('', ApiGradeType::getEndpoint(), new Remove(), [], 'Zensuren-Typ löschen'))
                            ->ajaxPipelineOnClick(ApiGradeType::pipelineOpenDeleteGradeTypeModal($tblGradeType->getId()))
                    );

                $dataList[] = $item;
            });
        }

        return new TableData($dataList, null, array(
            'Status' => 'Status',
            'Category' => 'Kategorie',
            'DisplayName' => 'Name',
            'DisplayCode' => 'Abk&uuml;rzung',
            'Description' => 'Beschreibung',
            'Option' => ''
        ), array(
            'order' => array(
                array('0', 'asc'),
                array('1', 'asc'),
                array('2', 'asc'),
            ),
            'columnDefs' => array(
                array('orderable' => false, 'targets' => -1),
            ),
        ));
    }

    /**
     * @param null $GradeTypeId
     * @param bool $setPost
     *
     * @return Form
     */
    public function formGradeType($GradeTypeId = null, bool $setPost = false): Form
    {
        $typeList[1] = new SelectBoxItem(1, 'Leistungsüberprüfung');
        $typeList[2] = new SelectBoxItem(2, 'Kopfnote');
        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
        $tblGradeType = Grade::useService()->getGradeTypeById($GradeTypeId);
        if ($setPost && $tblGradeType) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['Type'] = $tblGradeType->getIsTypeBehavior() ? 2 : 1;
            $Global->POST['Data']['Name'] = $tblGradeType->getName();
            $Global->POST['Data']['Code'] = $tblGradeType->getCode();
            $Global->POST['Data']['IsHighlighted'] = $tblGradeType->getIsHighlighted();
            $Global->POST['Data']['Description'] = $tblGradeType->getDescription();
            $Global->POST['Data']['IsPartGrade'] = $tblGradeType->getIsPartGrade();
            $Global->POST['Data']['IsIgnoredByScoreRule'] = $tblGradeType->getIsIgnoredByScoreRule();

            $Global->savePost();
        }

        if ($GradeTypeId) {
            $saveButton = (new Primary('Speichern', ApiGradeType::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiGradeType::pipelineEditGradeTypeSave($GradeTypeId));
        } else {
            $saveButton = (new Primary('Speichern', ApiGradeType::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiGradeType::pipelineCreateGradeTypeSave());
        }

        return (new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new SelectBox('Data[Type]', 'Kategorie', array('Name' => $typeList)))->setRequired(), 3
                ),
                new FormColumn(
                    (new TextField('Data[Code]', 'LK', 'Abk&uuml;rzung'))->setRequired(), 3
                ),
                new FormColumn(
                    (new TextField('Data[Name]', 'Leistungskontrolle', 'Name'))->setRequired(), 6
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    new TextField('Data[Description]', '', 'Beschreibung'), 12
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    new CheckBox('Data[IsHighlighted]', 'Fett markiert', 1), 3
                ),
                new FormColumn(
                    new CheckBox('Data[IsPartGrade]', 'Teilnote (wird zu einer Note zusammengefasst)', 1), 3
                ),
                new FormColumn(
                    new CheckBox('Data[IsIgnoredByScoreRule]', new ToolTip(new Exclamation() . ' Zensuren-Typ wird bei Berechnungsvorschriften nicht mit berechnet', 'Zum Beispiel für Sport Blocknoten'), 1), 5
                )
            )),
            new FormRow(
                new FormColumn(
                    $saveButton
                )
            )
        ))))->disableSubmitAction();
    }
}