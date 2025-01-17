<?php

namespace SPHERE\Application\Education\Lesson\Term;

use DateInterval;
use DateTime;
use SPHERE\Application\Api\Education\Term\ApiYear;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\System\BasicData\BasicData;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class FrontendWizard extends Extension implements IFrontendInterface
{
    /**
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendWizardYear($Data = null): Stage
    {
        $stage = new Stage('Schuljahr mit Zeiträumen und Ferien', 'Erstellen');
        $stage->addButton(new Standard('Zurück', '/Education/Lesson/Term', new ChevronLeft()));

        $YearList = array();
        for ($i = -2; $i < 5; $i++) {
            Term::useService()->addYear($YearList, $i, true);
        }

        $hasShortYear = ($tblSchoolTypeList = School::useService()->getConsumerSchoolTypeAll())
            && (isset($tblSchoolTypeList['Gy']) || isset($tblSchoolTypeList['BGy']));

        $stateId = 0;
        $stateList = array();
        if (($tblState = BasicData::useService()->getStateByName('Sachsen'))) {
            $stateList[$tblState->getId()] = $tblState->getName();
            if (Consumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_SACHSEN)) {
                $stateId = $tblState->getId();
            }
        }
        if (($tblState = BasicData::useService()->getStateByName('Berlin'))) {
            $stateList[$tblState->getId()] = $tblState->getName();
            if (Consumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_BERLIN)) {
                $stateId = $tblState->getId();
            }
        }
        if (($tblState = BasicData::useService()->getStateByName('Niedersachsen'))) {
            $stateList[$tblState->getId()] = $tblState->getName();
        }
        if (($tblState = BasicData::useService()->getStateByName('Thüringen'))) {
            $stateList[$tblState->getId()] = $tblState->getName();
        }

        $PreData = array();
        $global = $this->getGlobal();
        if ($stateId) {
            $global->POST['Data']['State'] = $PreData['State'] = $stateId;
        }
        if ($YearList) {
            $global->POST['Data']['YearName'] = $PreData['YearName'] = reset($YearList);
        }
        $global->POST['Data'][1]['Name'] = '1. Halbjahr';
        $global->POST['Data'][2]['Name'] = '2. Halbjahr';
        $global->POST['Data'][3]['Name'] = '1. Halbjahr';
        $global->POST['Data'][4]['Name'] = '2. Halbjahr';
        $global->savePost();

        $tblPeriodAll = Term::useService()->getPeriodAll();
        $acNameAll = array();
        if ($tblPeriodAll) {
            array_walk($tblPeriodAll, function (TblPeriod $tblPeriod) use (&$acNameAll) {

                if (!in_array($tblPeriod->getName(), $acNameAll)) {
                    $acNameAll[] = $tblPeriod->getName();
                }
            });
        }

        $pipeLines = array();
        $formColumns = array();
        for ($j = 1; $j <= ($hasShortYear ? 4 : 2); $j++) {
            $pipeLines[] = ApiYear::pipelineLoadPeriodDatePicker($j, 'From');
            $pipeLines[] = ApiYear::pipelineLoadPeriodDatePicker($j, 'To');

            $formColumns[] = new FormColumn(
                new Panel(
                    ($j % 2 == 1 ? '1' : '2') . '. Zeitraum' . ($j > 2 ? ' für 12. Klasse Gy / 13. Klasse BGy' : ''),
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                (new AutoCompleter('Data[' . $j . '][Name]', 'Name', '', $acNameAll, new Pencil()))
                                    ->setRequired()
                                , 6),
                            new LayoutColumn(
                                new TextField('Data[' . $j . '][Description]', '', 'Beschreibung', new Pencil())
                                , 6)
                        )),
                        new LayoutRow(array(
                            new LayoutColumn(
                                ApiYear::receiverBlock($this->loadPeriodDatePicker($j, 'From', $PreData), 'Period_From_' . $j)
                                , 6),
                            new LayoutColumn(
                                ApiYear::receiverBlock($this->loadPeriodDatePicker($j, 'To', $PreData), 'Period_To_' . $j)
                                , 6)
                        ))
                    ))),
                    Panel::PANEL_TYPE_INFO
                )
                , 6);
        }

        $form = (new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new SelectBox('Data[YearName]', 'Schuljahr', $YearList, new Select()))
                        ->ajaxPipelineOnChange($pipeLines)
                        ->setRequired()
                , 6),
                new FormColumn(
                    (new SelectBox('Data[State]', 'Bundesland', $stateList))
                        ->ajaxPipelineOnChange($pipeLines)
                , 6),
            )),
            new FormRow(array(
                new FormColumn(
                    new TextField('Data[YearDescription]', '', 'Beschreibung des Schuljahres', new Pencil())
                )
            )),
            new FormRow(
                $formColumns
            ),
            new FormRow(
                new FormColumn(
                    new Primary('Speichern', new Save())
                )
            )
        ))));

        $stage->setContent(new Well(
            Term::useService()->createWizardYear($form, $Data)
        ));

        return $stage;
    }

    /**
     * @param $Period
     * @param string $Type
     * @param $Data
     *
     * @return string
     */
    public function loadPeriodDatePicker($Period, string $Type, $Data): string
    {
        if (isset($Data['YearName']) && $Data['YearName']) {
            if (strlen($Data['YearName']) >= 4) {
                $year = substr($Data['YearName'], 0, 4);
                $nextYear = (int) $year + 1;

                $dateChristmas = new DateTime('31.12.' . $year);
                $dateChristmasNextDay = new DateTime('01.01.' . $nextYear);
                $dateWinter = new DateTime('31.01.' . $nextYear);
                $dateWinterNextDay = new DateTime('01.02.' . $nextYear);
                if (($tblState = BasicData::useService()->getStateById($Data['State'] ?? 0))) {
                    if (($tblHolidayChristmas = BasicData::useService()->getHolidayByNameAndYearAndState('Weihnachtsferien', $year, $tblState))
                        && ($fromDateChristmas = $tblHolidayChristmas->getFromDate())
                    ) {
                        $dateChristmas = (new DateTime($fromDateChristmas))->sub(new DateInterval('P1D'));
                        $dateChristmasNextDay = (new DateTime($fromDateChristmas));
                    }
                    if (($tblHolidayWinter = BasicData::useService()->getHolidayByNameAndYearAndState('Winterferien', $nextYear, $tblState))
                        && ($fromDateWinter = $tblHolidayWinter->getFromDate())
                    ) {
                        $dateWinter = (new DateTime($fromDateWinter))->sub(new DateInterval('P1D'));
                        $dateWinterNextDay = (new DateTime($fromDateWinter));
                    }
                }

                $post = '';
                if ($Type == 'From') {
                    switch ($Period) {
                        case 1:
                        case 3: $post = '01.08.' . $year; break;
                        case 2: $post = $dateWinterNextDay->format('d.m.Y'); break;
                        case 4: $post = $dateChristmasNextDay->format('d.m.Y'); break;
                    }
                } else {
                    switch ($Period) {
                        case 1: $post = $dateWinter->format('d.m.Y'); break;
                        case 3: $post = $dateChristmas->format('d.m.Y'); break;
                        case 2:
                        case 4: $post = '31.07.' . $nextYear; break;
                    }
                }

                $global = $this->getGlobal();
                $global->POST['Data'][$Period][$Type] = $post;
                $global->savePost();
            }
        }

        if ($Type == 'From') {
            $label = 'Von';
            $placeHolder = 'Beginn';
        } else {
            $label = 'Bis';
            $placeHolder = 'Ende';
        }

        return (new DatePicker('Data[' . $Period . '][' . $Type . ']', $placeHolder, $label, new Calendar()))
            ->setRequired();
    }
}