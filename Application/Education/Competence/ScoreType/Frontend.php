<?php

namespace SPHERE\Application\Education\Competence\ScoreType;

use SPHERE\Application\Api\Education\Competence\ApiScoreType;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
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
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    /** @noinspection PhpUnused */
    public function frontendScoreTypes(): Stage
    {
        $stage = new Stage('Bewertungssystem', 'Übersicht');

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
                $dataList[] = [
                    'Name' => $tblScoreType->getName(),
                    'Description' => $tblScoreType->getDescription(),
                    'Option' => new Standard('', '/Education/Competence/ScoreType/Edit', new Edit(), ['ScoreTypeId' => $tblScoreType->getId()])
                        . (new Standard('', ApiScoreType::getEndpoint(), new Remove(), array(), 'Bewertungssystem löschen'))
//                            ->ajaxPipelineOnClick(ApiScoreType::pipelineOpenDeleteSkillGridModal($tblScoreType->getId(), $SchoolTypeId, $Filter))
                ];
            }
        }

        return new TableData(
            $dataList,
            null,
            [
                'Name' => 'Name',
                'Description' => 'Beschreibung',
                'Option' => ' '
            ],
            [
                'columnDefs' => array(
                    array('orderable' => false, 'width' => '60px', 'targets' => -1),
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
     */
    public function frontendEditScoreTypes($ScoreTypeId = null): Stage
    {
        $stage = new Stage('Bewertungssystem', $ScoreTypeId ? 'Bearbeiten' : 'Hinzufügen');

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
//        if ($Action == 'RemoveSkill' && isset($Data['Skills'][$ActionId])) {
//            unset($Data['Skills'][$ActionId]);
//        } elseif ($Action == 'MoveSkillUp' && isset($Data['Skills'][$ActionId])) {
//            $split = explode('-', $ActionId);
//            $areaRanking = $split[0];
//            $skillRanking = $split[1];
//            // wenn der Skill davor gelöscht wurde → nicht nur minus 1
//            $up = $skillRanking - 1;
//            while ($up > 0) {
//                if (isset($Data['Skills'][$areaRanking . '-' . $up])) {
//                    $temp = $Data['Skills'][$ActionId];
//                    $Data['Skills'][$ActionId] = $Data['Skills'][$areaRanking . '-' . $up];
//                    $Data['Skills'][$areaRanking . '-' . $up] = $temp;
//
//                    ksort($Data['Skills']);
//
//                    // muss zusätzlich gepostet werden, damit die werte korrekt im Frontend angezeigt werden
//                    $global = $this->getGlobal();
//                    $global->POST['Data']['Skills'][$ActionId] = $Data['Skills'][$ActionId];
//                    $global->POST['Data']['Skills'][$areaRanking . '-' . $up] = $Data['Skills'][$areaRanking . '-' . $up];
//                    $global->savePost();
//
//                    break;
//                }
//                $up--;
//            }
//        }

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
        if ($Data === null) {
            $countItems = 3;
            $count = 0;
            for ($ranking = 1; $ranking < 4; $ranking++) {
                $items[] = ApiScoreType::receiverBlock($this->getScoreTypeItemContent($ScoreTypeId, $ranking, ++$count == $countItems), "ScoreTypeItem_$ranking");
            }
        } else {
            $countItems = count($Data['ScoreTypeItems']);
            $count = 0;
            foreach ($Data['ScoreTypeItems'] as $ranking => $areaArray) {
                $items[] =
                    ApiScoreType::receiverBlock(
                        $this->getScoreTypeItemContent($ScoreTypeId, $ranking, ++$count == $countItems, $ErrorList),
                        "ScoreTypeItem_$ranking"
                    );
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
    public function getScoreTypeItemContent($ScoreTypeId, $ranking, bool $hasAddButton = true, $ErrorList = null): string
    {
        $valueInput = new TextField("Data[ScoreTypeItems][$ranking][Value]", '1', 'Wert (Zahl) ' . new Danger('*'));
        if (isset($ErrorList["Data[ScoreTypeItems][$ranking][Value]"])) {
            $valueInput->setError($ErrorList["Data[ScoreTypeItems][$ranking][Value]"]['Message']);
        }
        $nameInput = new TextField("Data[ScoreTypeItems][$ranking][Name]", 'übertrifft die Anforderung', 'Kurztext ' . new Danger('*'));
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
                    new TextField("Data[ScoreTypeItems][$ranking][Description]", 'liegt deutlich über den Regelanforderungen und jahrgangsgemäßen Erwartungen',
                        'Beschreibung')
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
                    , //->ajaxPipelineOnClick(ApiSkill::pipelineLoadSkillAreaContent($SchoolTypeId, $Filter, $ScoreTypeId,$ranking + 1)),
                'ScoreTypeItem_' . ($ranking + 1)
            );
        }

        return $layout . $button;
    }
}