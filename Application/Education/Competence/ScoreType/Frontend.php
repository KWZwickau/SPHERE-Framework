<?php

namespace SPHERE\Application\Education\Competence\ScoreType;

use SPHERE\Application\Api\Education\Competence\ApiScoreType;
use SPHERE\Application\Education\Competence\ScoreType\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Competence\SkillGrid\SkillGrid;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Transfer;
use SPHERE\Common\Frontend\Icon\Repository\Unchecked;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    /** @noinspection PhpUnused */
    public function frontendScoreTypes(): Stage
    {
        $stage = new Stage('Bewertungssystem', 'Übersicht');
        $stage->setMessage('Neben dem bereits hinterlegten Bewertungssystem: Prozent können hier eigene Bewertungssysteme für die Bewertung 
            von Kompetenzen hinterlegt werden.');

        $stage->setContent(
            ApiScoreType::receiverModal()
            . new Primary('Bewertungssystem hinzufügen', '/Education/Competence/ScoreType/Edit', new Plus())
            . new Container('&nbsp;')
            . ApiScoreType::receiverBlock($this->loadScoreTypeTable(), 'ScoreTypeTable')
        );

        return $stage;
    }

    /**
     * @return string
     */
    public function loadScoreTypeTable(): string
    {
        $dataList = [];
        if (($tblScoreTypeList = ScoreType::useService()->getScoreTypeAll())) {
            foreach ($tblScoreTypeList as $tblScoreType) {
                $delete = '';
                // nur löschen möglich, wenn Bewertungssystem nicht verwendet wird
                if ($tblScoreType->getId() > 0 && !SkillGrid::useService()->getIsScoreTypeUsedInAnySkillGrid($tblScoreType)) {
                    $delete = (new Standard('', ApiScoreType::getEndpoint(), new Remove(), array(), 'Bewertungssystem löschen'))
                        ->ajaxPipelineOnClick(ApiScoreType::pipelineOpenDeleteScoreTypeModal($tblScoreType->getId()));
                }
                $edit = '';
                if ($tblScoreType->getId() > 0) {
                    $edit = new Standard('', '/Education/Competence/ScoreType/Edit', new Edit(), ['ScoreTypeId' => $tblScoreType->getId()],
                        'Bewertungssystem bearbeiten');
                }

                if ($tblScoreType->getScoreTypeConversions()) {
                    $conversion = new Success(new Check() . ' Umrechnung in Zensuren hinterlegt');
                } else {
                    $conversion = new Warning(new Unchecked() . ' Umrechnung in Zensuren hinterlegt');
                }

                $dataList[] = [
                    'Name' => $tblScoreType->getName(),
                    'Description' => $tblScoreType->getDescription(),
                    'Names' => $tblScoreType->getDisplayNames(),
                    'Conversion' => $conversion,
                    'Option' => $edit
                        . (new Standard('', ApiScoreType::getEndpoint(), new Transfer(), array(),
                            'Umrechnung Bewertungssystem in Zensuren für Notenzeugnisse'))
                                ->ajaxPipelineOnClick(ApiScoreType::pipelineOpenConversionScoreTypeModal($tblScoreType->getId()))
                        . $delete
                ];
            }
        }

        return new TableData(
            $dataList,
            null,
            [
                'Name' => 'Name',
                'Description' => 'Beschreibung',
                'Names' => 'Bewertungen',
                'Conversion' => 'Umrechnung in Zensuren',
                'Option' => ' '
            ],
            [
                'columnDefs' => array(
                    array('orderable' => false, 'width' => '100px', 'targets' => -1),
                    // array('searchable' => false, 'targets' => array(-1, -2)),
                ),
                'order' => array(
                    array('0', 'asc'),
                    array('1', 'asc'),
                ),
                'responsive' => false,
                'destroy' => true
            ]
        );
    }

    /**
     * @param $ScoreTypeId
     *
     * @return Stage
     * @noinspection PhpUnused
     */
    public function frontendEditScoreTypes($ScoreTypeId = null): Stage
    {
        $stage = new Stage('Bewertungssystem', $ScoreTypeId ? 'Bearbeiten' : 'Hinzufügen');
        $stage->setMessage(new Warning('Der Wert (Zahl) wird für die Durchschnittsberechnung von Kompetenzbewertungen und für eine mögliche 
            Umrechnung in Zensuren für Notenzeugnisse benötigt.'
            . new Container('Beispiel für eine Bewertung: Wert: 1, Kurztext: übertrifft die Anforderung, 
                Beschreibung: liegt deutlich über den Regelanforderungen und jahrgangsgemäßen Erwartungen')));

        $stage->setContent(
            ApiScoreType::receiverBlock($this->loadEditScoreTypeContent(true, $ScoreTypeId), 'EditScoreTypeContent')
        );

        return $stage;
    }

    /**
     * @param bool $setPost
     * @param $ScoreTypeId
     * @param $Action
     * @param $ActionId
     * @param $Data
     *
     * @return string
     */
    public function loadEditScoreTypeContent(bool $setPost = false, $ScoreTypeId = null,
        $Action = null, $ActionId = null, $Data = null): string
    {
        if ($Action == 'RemoveScoreTypeItem' && isset($Data['ScoreTypeItems'][$ActionId])) {
            unset($Data['ScoreTypeItems'][$ActionId]);
        }

        return new Well($this->formScoreType($setPost, $ScoreTypeId, $Data));
    }

    /**
     * @param bool $setPost
     * @param $ScoreTypeId
     * @param $Data
     * @param $ErrorList
     * t
     * @return Form
     */
    public function formScoreType(bool $setPost, $ScoreTypeId = null, $Data = null, $ErrorList = null): Form
    {
        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
        $tblScoreType = ScoreType::useService()->getScoreTypeById($ScoreTypeId);
        if ($setPost && $tblScoreType) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['Name'] = $tblScoreType->getName();
            $Global->POST['Data']['Description'] = $tblScoreType->getDescription() ?: '';

            $ranking = 1;
            foreach ($tblScoreType->getScoreTypeItems() as $tblScoreTypeItem) {
                $Global->POST['Data']['ScoreTypeItems'][$ranking]['Value'] = $tblScoreTypeItem->getValue();
                $Data['ScoreTypeItems'][$ranking]['Value'] = $tblScoreTypeItem->getValue();
                $Global->POST['Data']['ScoreTypeItems'][$ranking]['Name'] = $tblScoreTypeItem->getName();
                $Data['ScoreTypeItems'][$ranking]['Name'] = $tblScoreTypeItem->getName();
                $Global->POST['Data']['ScoreTypeItems'][$ranking]['Description'] = $tblScoreTypeItem->getDescription();
                $Data['ScoreTypeItems'][$ranking]['Description'] = $tblScoreTypeItem->getDescription();

                $ranking++;
            }

            $Global->savePost();
        }

        $items = [];
        if (isset($Data['ScoreTypeItems'])) {
            $countItems = count($Data['ScoreTypeItems']);
            $count = 0;
            foreach ($Data['ScoreTypeItems'] as $ranking => $areaArray) {
                $items[] =
                    ApiScoreType::receiverBlock(
                        $this->loadScoreTypeItemContent($ScoreTypeId, $ranking, ++$count == $countItems, $ErrorList),
                        "ScoreTypeItem_$ranking"
                    );
            }
        } else {
            $countItems = 3;
            $count = 0;
            for ($ranking = 1; $ranking < 4; $ranking++) {
                $items[] = ApiScoreType::receiverBlock($this->loadScoreTypeItemContent($ScoreTypeId, $ranking, ++$count == $countItems), "ScoreTypeItem_$ranking");
            }
        }

        $form = (new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel(
                            'Bewertungssystem',
                            new Layout(new LayoutGroup(array(
                                new LayoutRow(array(
                                    new LayoutColumn(
                                        new TextField('Data[Name]', '', 'Name ' . new Danger('*'))
                                    , 6),
                                    new LayoutColumn(
                                        new TextField('Data[Description]', '', 'Beschreibung')
                                    , 6),
                                )),
                            ))),
                            Panel::PANEL_TYPE_INFO
                        )
                    ),
                )),
            )),
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel(
                            'Bewertungen',
                            $items,
                            Panel::PANEL_TYPE_INFO
                        )
                    ),
                )),
            ),
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Container('&nbsp;'),
                        (new Primary('Speichern', ApiScoreType::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiScoreType::pipelineSaveEditScoreType($ScoreTypeId)),
                        new Standard('Abbrechen', '/Education/Competence/ScoreType', new Disable())
                    ))
                )),
            ))
        )))->disableSubmitAction();

        if ($ErrorList) {
            foreach ($ErrorList as $error) {
                $form->setError($error['Name'], $error['Message']);
            }
        }

        return $form;
    }

    /**
     * @param $ScoreTypeId
     * @param $ranking
     * @param bool $hasAddButton
     * @param $ErrorList
     *
     * @return string
     */
    public function loadScoreTypeItemContent($ScoreTypeId, $ranking, bool $hasAddButton = true, $ErrorList = null): string
    {
        $valuePlaceholder = $ranking == 1 ? '1' : '';
        $namePlaceholder = $ranking == 1 ? 'übertrifft die Anforderung' : '';
        $descriptionPlaceholder = $ranking == 1 ? 'liegt deutlich über den Regelanforderungen und jahrgangsgemäßen Erwartungen' : '';

        $valueInput = new TextField("Data[ScoreTypeItems][$ranking][Value]", $valuePlaceholder, 'Wert (Zahl) ' . new Danger('*'));
        if (isset($ErrorList["Data[ScoreTypeItems][$ranking][Value]"])) {
            $valueInput->setError($ErrorList["Data[ScoreTypeItems][$ranking][Value]"]['Message']);
        }
        $nameInput = new TextField("Data[ScoreTypeItems][$ranking][Name]", $namePlaceholder, 'Kurztext ' . new Danger('*'));
        if (isset($ErrorList["Data[ScoreTypeItems][$ranking][Name]"])) {
            $nameInput->setError($ErrorList["Data[ScoreTypeItems][$ranking][Name]"]['Message']);
        }

        $layout = new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    $valueInput
                , 2),
                new LayoutColumn(
                    $nameInput
                , 3),
                new LayoutColumn(
                    new TextField("Data[ScoreTypeItems][$ranking][Description]", $descriptionPlaceholder, 'Beschreibung')
                , 6),
                new LayoutColumn(array(
                    (new Container('&nbsp;'))->setStyle(['height: 22px;']),
                    (new Standard('', ApiScoreType::getEndpoint(), new Minus(), [], 'Bewertung löschen', null))
                        ->ajaxPipelineOnClick(ApiScoreType::pipelineLoadEditScoreTypeContent($ScoreTypeId, 'RemoveScoreTypeItem', $ranking))
                ), 1),
            )),
        )));

        $button = '';
        if ($hasAddButton) {
            $button = ApiScoreType::receiverBlock(
                (new Link(new Bold('Bewertung hinzufügen'), ApiScoreType::getEndpoint(), new Plus()))
                    ->ajaxPipelineOnClick(ApiScoreType::pipelineLoadScoreTypeItemContent($ScoreTypeId, $ranking + 1)),
                'ScoreTypeItem_' . ($ranking + 1)
            );
        }

        return $layout . $button;
    }

    /**
     * @param bool $setPost
     * @param TblScoreType $tblScoreType
     *
     * @return Form
     */
    public function formScoreTypeConversion(bool $setPost, TblScoreType $tblScoreType): Form
    {
        if ($setPost) {
            $global = $this->getGlobal();
            foreach ($tblScoreType->getScoreTypeConversions() as $tblScoreTypeConversion) {
                $global->POST['Data'][$tblScoreTypeConversion->getGrade()] = $tblScoreTypeConversion->getValue();
            }

            $global->savePost();
        }

        $isPercent = $tblScoreType->getId() < 1;
        $rows = [];
        for ($i = 1; $i < 7; $i++) {
            $placeholder = $isPercent
                ? $this->getPercentPlaceholder($i)
                : "$i,5";

            $rows[] = new FormRow(array(
                new FormColumn(
                    new Center(new Bold($i . ' - ' . $this->getVerbalGrade($i)))
                , 6),
                new FormColumn(
                    $i == 6
                        ? new Container($isPercent ? 'Alle weiteren Prozente' : 'Alle weiteren Bewertungsdurchschnitte')
                        : new TextField("Data[$i]", $placeholder, "", $isPercent ? new ChevronRight() : new ChevronLeft())
                , 6),
            ));
        }

        $rows[] = new FormRow(array(
            new FormColumn(array(
                new Container('&nbsp;'),
                (new Primary('Speichern', ApiScoreType::getEndpoint(), new Save()))
                    ->ajaxPipelineOnClick(ApiScoreType::pipelineConversionScoreTypeSave($tblScoreType->getId())),
            ))
        ));

        $title = new Title(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    new Center(new Bold('Zensuren für Notenzeugnis'))
                    , 6),
                new LayoutColumn(
                    new Bold('Bis Kompetenz-Bewertung-Durchschnitt')
                    , 6)
            ))))
        );

        return (new Form(new FormGroup($rows, $title)))->disableSubmitAction();
    }

    private function getVerbalGrade($grade): string
    {
        return match ($grade) {
            1 => 'Sehr gut',
            2 => 'Gut',
            3 => 'Befriedigend',
            4 => 'Ausreichend',
            5 => 'Mangelhaft',
            6 => 'Ungenügend',
            default => $grade,
        };
    }

    private function getPercentPlaceholder($grade): string
    {
        return match ($grade) {
            1 => '92',
            2 => '81',
            3 => '67',
            4 => '50',
            5 => '30',
            default => $grade,
        };
    }
}