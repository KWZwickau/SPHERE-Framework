<?php
namespace SPHERE\Application\Education\Lesson\Term;

use SPHERE\Application\Api\Education\Term\YearHoliday;
use SPHERE\Application\Api\Education\Term\YearPeriod;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as GatekeeperConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\System\BasicData\BasicData;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Icon\Repository\Unchecked;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Lesson\Term
 */
class Frontend extends FrontendWizard
{

    public function getWelcome()
    {

        // BIP mit 7 verschiedenen parallelen Schuljahren ausgenommen
        if(GatekeeperConsumer::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'BIP')){
            return '';
        }

        $Now = new \DateTime();
        $tblYearList = array();
        // aktuelles & folgende Schuljahre
        if(($tblYearListTmp = Term::useService()->getYearAllFutureYears(0))){
            $tblYearListTmp = (new Extension())->getSorter($tblYearListTmp)->sortObjectBy(TblYear::ATTR_YEAR);
            foreach($tblYearListTmp as $tblYearTmp){
                $tblYearList[] = $tblYearTmp;
            }
        }

        $MissingTimeSpan = array();
        if(!empty($tblYearList)){
            foreach($tblYearList as $tblYear){
                if(($tblPeriodList = $tblYear->getPeriodList())){
                    $tblPeriodList = (new Extension())->getSorter($tblPeriodList)->sortObjectBy(TblPeriod::ATTR_FROM_DATE, new DateTimeSorter());
                    foreach($tblPeriodList as $tblPeriod){
                        $From = $tblPeriod->getFromDateTime();
                        $To = $tblPeriod->getToDateTime();
                        $isDangerColor = false;
                        if(!isset($LastTo)){
                            // Now darf kein Tag hinzufügen
                            $LastToPlus1Day = $LastTo = $Now;
                            $isDangerColor = true;
                        } else {
                            // Vergleich setzt ToDate um ein Tag nach oben um das Datum besser vergleichen zu können
                            $LastToPlus1Day = $LastTo;
                            $LastToPlus1Day->modify('+1 day');
                        }
                        if($LastToPlus1Day < $From && $From > $Now) {
                            $FromTemp = $From;
                            $FromTemp->modify('-1 day');
                            if($isDangerColor){
                                $DateString = new DangerText('['.$LastToPlus1Day->format('d.m.Y').' - '.$From->format('d.m.Y').']');
                            } else {
                                $DateString = '['.$LastToPlus1Day->format('d.m.Y').' - '.$From->format('d.m.Y').']';
                            }
                            $MissingTimeSpan[] = $DateString; // 'Schuljahr: '.$tblYear->getDisplayName().
                        }
//                        $LastFrom = $From;
                        $LastTo = $To;
                    }
                }
            }
            if(!empty($MissingTimeSpan)) {
                if(count($MissingTimeSpan) > 1) {
                    $TextForCount = 'Für folgende Zeiträume sind keine Schuljahre';
                } else {
                    $TextForCount = 'Für folgenden Zeitraum ist kein Schuljahr';
                }
                return new Warning(
                    new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn($TextForCount.' hinterlegt: '.new Bold(implode(' &nbsp; ',$MissingTimeSpan))
                            .new Container('Bitte geben Sie die Zeiträume so an, das keine Lücken entstehen, um ein lückenloses Arbeiten zu ermöglichen'), 9),
                        new LayoutColumn(new PullRight(new Standard('Schuljahr Dashboard', '/Education/Lesson/Term', new EyeOpen())), 3),
                    )))));
            }
        } else {
            return new Danger(new Standard('Schuljahr Dashboard', '/Education/Lesson/Term', new EyeOpen()).'Es ist kein Schuljahr aktiv. Auswertungen, Kurse, Schülerakten, etc. können unerwartet reagieren');
        }
        return '';
    }

    /**
     * @param string $Route
     * @param bool $IsAllYears
     * @param string|null $YearId
     *
     * @return array
     */
    public function getYearButtonsAndYearFilters(string $Route, bool $IsAllYears = false, ?string $YearId = null): array
    {
        $buttonList = array();
        $filterYearList = array();

        $tblYear = false;
        $tblYearList = Term::useService()->getYearByNow();
        if ($YearId) {
            $tblYear = Term::useService()->getYearById($YearId);
        }

        if ($tblYearList) {
            if($tblYear || $IsAllYears){
                $buttonList[] = new Standard('Aktuelles Schuljahr', $Route);
            } else {
                $buttonList[] = new Standard(new Info(new Bold('Aktuelles Schuljahr')), $Route, new Edit());
            }

            if ($tblYear) {
                $filterYearList[$tblYear->getId()] = $tblYear;
            }

            /** @var TblYear $tblYearItem */
            foreach ($tblYearList as $tblYearItem) {
                if ($tblYear && $tblYear->getId() == $tblYearItem->getId()) {
                    $buttonList[] = new Standard(new Info(new Bold($tblYearItem->getDisplayName())), $Route, new Edit(),
                        array('YearId' => $tblYearItem->getId()));
                } else {
                    $buttonList[] = new Standard($tblYearItem->getDisplayName(), $Route, null,
                        array('YearId' => $tblYearItem->getId()));
                }

                if (!$tblYear && !$IsAllYears) {
                    $filterYearList[$tblYearItem->getId()] = $tblYearItem;
                }
            }

            if ($IsAllYears) {
                $buttonList[] = new Standard(new Info(new Bold('Alle Schuljahre')), $Route, new Edit(),
                    array('IsAllYears' => true));
            } else {
                $buttonList[] = new Standard('Alle Schuljahre', $Route, null, array('IsAllYears' => true));
            }
        }

        return array($buttonList, $filterYearList);
    }

    /**
     * @param null|array $Year
     *
     * @return Stage
     */
    public function frontendCreateYear($Year = null)
    {

        $Stage = new Stage('Schuljahr', 'Übersicht');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Term', new ChevronLeft()));

        $tblYearAll = Term::useService()->getYearAll();
        $TableContent = array();
        if ($tblYearAll) {
            array_walk($tblYearAll, function (TblYear $tblYear) use (&$TableContent) {

                $tblPeriodAll = $tblYear->getPeriodList(false, true);
                $Temp['Year'] = $tblYear->getYear();
                $Temp['Description'] = $tblYear->getDescription();
                $Temp['Option'] =
                    new Standard('', __NAMESPACE__ . '\Edit\Year', new Pencil(),
                        array('Id' => $tblYear->getId())
                    ) .
                    (empty($tblPeriodAll)
                        ? new Standard('', __NAMESPACE__ . '\Destroy\Year', new Remove(),
                            array('Id' => $tblYear->getId())
                        ) : ''
                    );
                array_push($TableContent, $Temp);
            });
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null, array(
                                'Year' => 'Jahr',
                                'Description' => 'Beschreibung',
                                'Option' => '',
                            ))
                        )
                    ), new Title(new ListingTable() . ' Übersicht')
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Term::useService()->createYear(
                                    $this->formYear()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $Year
                                )
                            )
                            , 12)
                    ), new Title(new PlusSign() . ' Hinzufügen')
                ),
            ))
        );

        return $Stage;
    }

    /**
     * @param null|TblYear $tblYear
     *
     * @return Form
     */
    public function formYear(TblYear $tblYear = null)
    {

        $YearList = array();
        for ($i = -2; $i < 5; $i++) {
            Term::useService()->addYear($YearList, $i);
        }

//        // bereits existierende Schuljahr stehen nicht zur Auswahl
//        if (($tblYearAll = Term::useService()->getYearAll())) {
//            foreach ($tblYearAll as $item) {
//                if (!$tblYear && isset($YearList[$item->getYear()])) {
//                    unset($YearList[$item->getYear()]);
//                }
//            }
//        }
//        // Fügt ein leeres Element hinzu (sonst Fehlermeldung)
//        if(count($YearList) <= 1){
//            $YearList[] = '';
//        }

        $Global = $this->getGlobal();
        if (!isset($Global->POST['Year']) && $tblYear) {
            $Global->POST['Year']['Year'] = $tblYear->getYear();
            $Global->POST['Year']['Description'] = $tblYear->getDescription();
        } elseif(!isset($Global->POST['Year'])){
            $Global->POST['Year']['Year'] = next($YearList);
        }
        $Global->savePost();

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Schuljahr', array(
                                new SelectBox('Year[Year]', 'Jahr', $YearList, new Select())
                            )
                            , Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        new Panel('Sonstiges', array(
                                new TextField('Year[Description]', '', 'Beschreibung', new Pencil())
                            )
                            , Panel::PANEL_TYPE_INFO
                        )
                        , 6)
                )),
            ))
        );
    }

    /**
     * @param null|array $Period
     *
     * @return Stage
     */
    public function frontendCreatePeriod($Period = null)
    {

        $Stage = new Stage('Zeitraum', 'Übersicht');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Term', new ChevronLeft()));

        $tblPeriodAll = Term::useService()->getPeriodAll();
        $TableContent = array();
        if ($tblPeriodAll) {
            array_walk($tblPeriodAll, function (TblPeriod $tblPeriod) use (&$TableContent) {

                $Temp['Name'] = $tblPeriod->getName();
                $Temp['Description'] = $tblPeriod->getDescription();
                $Temp['PeriodFrom'] = $tblPeriod->getFromDate();
                $Temp['PeriodTo'] = $tblPeriod->getToDate();
                $Temp['IsLevel12'] = $tblPeriod->isLevel12() ? new Check() : new Unchecked();
                $Temp['Option'] =
                    new Standard('', __NAMESPACE__ . '\Edit\Period', new Pencil(),
                        array('Id' => $tblPeriod->getId()))
                    . ((Term::useService()->getPeriodExistWithYear($tblPeriod) === false) ?
                        new Standard('', __NAMESPACE__ . '\Destroy\Period', new Remove(),
                            array('Id' => $tblPeriod->getId()))
                        : '');
                array_push($TableContent, $Temp);
            });
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array(
                                    'Name' => 'Name',
                                    'Description' => 'Beschreibung',
                                    'IsLevel12' => 'Für 12. Klasse Gy / 13. Klasse BGy',
                                    'PeriodFrom' => 'Zeitraum von',
                                    'PeriodTo' => 'Zeitraum Bis',
                                    'Option' => '',
                                ),
                                array(
                                    'order' => array(
                                        array('3', 'desc'),
                                        array('4', 'desc'),
                                        array('0', 'asc'),
                                    ),
                                    'columnDefs' => array(
                                        array('type' => 'de_date', 'targets' => array(3, 4)),
                                    ),
                                )
                            )
                        )
                    ), new Title(new ListingTable() . ' Übersicht')
                ),
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(new Warning('Bitte beachten Sie, dass die Zeiträume der Halbjahre (auch von Schuljahr zu Schuljahr) lückenlos sein müssen.')),
                        new LayoutColumn(
                            new Well(
                                Term::useService()->createPeriod(
                                    $this->formPeriod()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $Period
                                )
                            )
                        )
                    )), new Title(new PlusSign() . ' Hinzufügen')
                ),
            ))
        );

        return $Stage;
    }

    /**
     * @param null|TblPeriod $tblPeriod
     *
     * @return Form
     */
    public function formPeriod(TblPeriod $tblPeriod = null)
    {

        $tblPeriodAll = Term::useService()->getPeriodAll();
        $acNameAll = array();
        if ($tblPeriodAll) {
            array_walk($tblPeriodAll, function (TblPeriod $tblPeriod) use (&$acNameAll) {

                if (!in_array($tblPeriod->getName(), $acNameAll)) {
                    array_push($acNameAll, $tblPeriod->getName());
                }
            });
        }

        $Global = $this->getGlobal();
        if (!isset($Global->POST['Period']) && $tblPeriod) {
            $Global->POST['Period']['Name'] = $tblPeriod->getName();
            $Global->POST['Period']['Description'] = $tblPeriod->getDescription();
            $Global->POST['Period']['From'] = $tblPeriod->getFromDate();
            $Global->POST['Period']['To'] = $tblPeriod->getToDate();
            $Global->POST['Period']['IsLevel12'] = $tblPeriod->isLevel12();
            $Global->savePost();
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Zeitraum',
                            array(
                                new AutoCompleter('Period[Name]', 'Name', 'z.B: 1.Halbjahr',
                                    $acNameAll, new Pencil()),
                                new TextField('Period[Description]', 'z.B: für Gymnasium', 'Beschreibung',
                                    new Pencil()),
                                new CheckBox('Period[IsLevel12]', 'Ist ein Halbjahr für die 12. Klasse Gy / 13. Klasse BGy', 1)
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        new Panel('Datum',
                            array(
                                new DatePicker('Period[From]', 'Beginn', 'Von', new Calendar()),
                                new DatePicker('Period[To]', 'Ende', 'Bis', new Calendar()),
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                )),
            ))
        );
    }

    /**
     * @param      $Id
     *
     * @return Stage
     */
    public function frontendChoosePeriod($Id = null)
    {
        $tblYear = $Id === null ? false : Term::useService()->getYearById($Id);
        $Stage = new Stage('Zeiträume', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Term', new ChevronLeft()));

        if ($tblYear) {
            $Stage->setContent(
                new Panel('Schuljahr', $tblYear->getName() .
                    ($tblYear->getDescription() !== '' ? '&nbsp;&nbsp;'
                        . new Muted(new Small($tblYear->getDescription())) : ''),
                    Panel::PANEL_TYPE_INFO)
                . YearPeriod::receiverUsed(YearPeriod::tablePeriod($Id))
            );
        } else {
            $Stage->setContent(new Warning('Jahr nicht gefunden'));
        }

        return $Stage;
    }

    /**
     * @param TblYear $tblYear
     *
     * @return bool|Layout
     */
    public function layoutYear(
        TblYear $tblYear
    ) {

        if ($tblYear) {
            $Panel = new Panel('<b>' . (($tblYear->getDescription()) ? ($tblYear->getDescription()) : 'Schuljahr') . '&nbsp'
                . $tblYear->getDisplayName() . '</b>', '', Panel::PANEL_TYPE_INFO);
            return new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn($Panel, 6))));
        }
        return false;
    }

    /**
     * @param null $Id
     * @param null $Year
     *
     * @return Stage|string
     */
    public function frontendEditYear($Id = null, $Year = null)
    {

        $Stage = new Stage('Schuljahr', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Term/Create/Year', new ChevronLeft()));
        $tblYear = $Id === null ? false : Term::useService()->getYearById($Id);
        if (!$tblYear) {
            $Stage->setContent(new Warning('Jahr nicht gefunden!'));
            return $Stage . new Redirect('/Education/Lesson/Term/Create/Year', Redirect::TIMEOUT_ERROR);
        }
        $Form = $this->formYear($tblYear)
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            array(
                                new Panel('Jahr',
                                    $tblYear->getDisplayName() . ' ' . new Small(new Muted($tblYear->getDescription())),
                                    Panel::PANEL_TYPE_INFO) .
                                new Headline(new Edit() . ' Bearbeiten'),
                                new Well(Term::useService()->changeYear($Form, $tblYear, $Year)),
                            ), 12)
                    )
                )
            )
        );
//            $Stage->setContent( Term::useService()->changeYear($this->formYear($tblYear)
//            ->appendFormButton(new Primary('Änderungen speichern'))
//            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
//            ,$tblYear, $Year)
//            );
        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage|string
     */
    public function frontendDestroyYear($Id = null)
    {

        $Stage = new Stage('Jahr', 'Entfernen');
        $tblYear = $Id === null ? false : Term::useService()->getYearById($Id);
        if (!$tblYear) {
            $Stage->setContent(new Warning('Jahr nicht gefunden!'));
            return $Stage . new Redirect('/Education/Lesson/Term/Create/Year', Redirect::TIMEOUT_ERROR);
        }
        $Stage->setContent(Term::useService()->destroyYear($tblYear));

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Period
     *
     * @return Stage|string
     */
    public function frontendEditPeriod($Id = null, $Period = null)
    {

        $Stage = new Stage('Zeitraum', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Term/Create/Period', new ChevronLeft()));
        $tblPeriod = $Id === null ? false : Term::useService()->getPeriodById($Id);

        if (!$tblPeriod) {
            $Stage->setContent(new Warning('Zeitraum nicht gefunden!'));
            return $Stage . new Redirect('/Education/Lesson/Term/Create/Period', Redirect::TIMEOUT_ERROR);
        }
        $PeriodName = $tblPeriod->getName();
        $PeriodDescription = $tblPeriod->getDescription();
        $PeriodFrom = $tblPeriod->getFromDate();
        $PeriodTo = $tblPeriod->getToDate();
        $Panel = new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Panel('Zeitraum', array(
                            $PeriodName . ' ' . new Muted(new Small($PeriodDescription)),
                            'Zeitraum ' . $PeriodFrom . ' - ' . $PeriodTo
                        ), Panel::PANEL_TYPE_INFO)
                    )
                )
            )
        );

        $Form = $this->formPeriod($tblPeriod)
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent($Panel .
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(Term::useService()->changePeriod($Form, $tblPeriod, $Period))
                        )
                    ), new Title(new Edit() . ' Bearbeiten')
                )
            )
        );
        return $Stage;
    }

    /**
     * @param int $Id
     *
     * @return Stage|string
     */
    public function frontendDestroyPeriod($Id = null)
    {

        $Stage = new Stage('Zeitraum', 'Entfernen');
        $tblPeriod = $Id === null ? false : Term::useService()->getPeriodById($Id);
        if (!$tblPeriod) {
            return $Stage . new Warning('Zeitraum nicht gefunden!')
            . new Redirect('/Education/Lesson/Term/Create/Period', Redirect::TIMEOUT_ERROR);
        }
        $Stage->setContent(Term::useService()->destroyPeriod($tblPeriod));
        return $Stage;
    }

    /**
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendHoliday($Data = null)
    {

        $Stage = new Stage('Unterrichtsfreie Zeiträume', 'Übersicht');

        $Stage->addButton(new Standard(
            'Zurück', '/Education/Lesson/Term', new ChevronLeft()
        ));

        $tableData = array();
        $tblHolidayAll = Term::useService()->getHolidayAll();
        if ($tblHolidayAll) {
            foreach ($tblHolidayAll as $tblHoliday) {

                $tableData[] = array(
                    'FromDate' => $tblHoliday->getFromDate(),
                    'ToDate' => $tblHoliday->getToDate(),
                    'Name' => $tblHoliday->getName(),
                    'Type' => $tblHoliday->getTblHolidayType()->getName(),
                    'Option' => (new Standard(
                            '', '/Education/Lesson/Term/Holiday/Edit', new Edit(),
                            array('Id' => $tblHoliday->getId()), 'Bearbeiten'
                        ))
                        . (new Standard(
                            '', '/Education/Lesson/Term/Holiday/Destroy', new Remove(),
                            array('Id' => $tblHoliday->getId()), 'Löschen'
                        ))
                );
            }
        }

        $Form = $this->formHoliday()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new TableData($tableData, null, array(
                                    'FromDate' => 'Datum von',
                                    'ToDate' => 'Datum bis',
                                    'Name' => 'Name',
                                    'Type' => 'Typ',
                                    'Option' => ''
                                ),
                                    array(
                                        'order' => array(
                                            array(0, 'desc'),
                                            array(1, 'desc')
                                        ),
                                        'columnDefs' => array(
                                            array('type' => 'de_date', 'targets' => array(0,1)),
                                        )
                                    )
                                )
                            ))
                        ))
                    ), new Title(new ListingTable() . ' Übersicht')),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Well(Term::useService()->createHoliday($Form, $Data))
                            )
                        ))
                    ), new Title(new PlusSign() . ' Hinzufügen'))
                )
            )
        );

        return $Stage;
    }


    /**
     * @return Form
     */
    private function formHoliday()
    {

        $tblHolidayTypeAll = Term::useService()->getHolidayTypeAll();
        if (!$tblHolidayTypeAll) {
            $tblHolidayTypeAll = array();
        }

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new SelectBox('Data[Type]', 'Typ', array('Name' => $tblHolidayTypeAll)), 4
                ),
                new FormColumn(
                    new DatePicker('Data[FromDate]', '', 'Datum von', new Calendar()), 4
                ),
                new FormColumn(
                    new DatePicker('Data[ToDate]', '', 'Datum bis', new Calendar()), 4
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    new TextField('Data[Name]', '', 'Name'), 12
                ),
            )),
        )));
    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendEditHoliday($Id = null, $Data = null)
    {

        $Stage = new Stage('Unterrichtsfreie Zeiträume', 'Bearbeiten');
        $tblHoliday = Term::useService()->getHolidayById($Id);
        if ($tblHoliday) {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Lesson/Term/Holiday', new ChevronLeft()
            ));

            if ($Data === null) {
                $Global = $this->getGlobal();
                $Global->POST['Data']['FromDate'] = $tblHoliday->getFromDate();
                $Global->POST['Data']['ToDate'] = $tblHoliday->getToDate();
                $Global->POST['Data']['Type'] = $tblHoliday->getTblHolidayType()->getId();
                $Global->POST['Data']['Name'] = $tblHoliday->getName();
                $Global->savePost();
            }

            $Form = $this->formHoliday()
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->setContent(
                new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Unterrichtsfreie Zeiträume',
                                        $tblHoliday->getName(),
                                        Panel::PANEL_TYPE_INFO
                                    )
                                ))
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Well(Term::useService()->updateHoliday($Form, $tblHoliday, $Data))
                                )
                            ))
                        ))
                    )
                )
            );

            return $Stage;
        } else {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/ClassRegister', new ChevronLeft()
            ));

            return $Stage . new Danger('Unterrichtsfreie Zeiträume nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $YearId
     * @param null $CompanyId
     *
     * @return Stage|string
     */
    public function frontendSelectHoliday(
        $YearId = null,
        $CompanyId = null
    ) {

        $Stage = new Stage('Schuljahr', 'Unterrichtsfreie Zeiträume zuweisen');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Term', new ChevronLeft()));

        if (($tblYear = Term::useService()->getYearById($YearId))) {
            $Stage->setContent(new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel(
                                'Schuljahr',
                                $tblYear->getName() . ' ' . new Small(new Muted($tblYear->getDescription())),
                                Panel::PANEL_TYPE_INFO
                            )
                            , 6),
                        new LayoutColumn(
                            new Panel(
                                'Schule',
                                ($tblCompany = Company::useService()->getCompanyById($CompanyId))
                                    ? $tblCompany->getDisplayName()
                                    : 'Alle Schulen',
                                Panel::PANEL_TYPE_INFO
                            )
                            , 6),
                    ))
                )),
            )) . YearHoliday::receiverUsed(YearHoliday::tableHoliday($YearId, $CompanyId)));

        } else {
            return $Stage
            . new Danger('Schuljahr nicht gefunden.', new Ban())
            . new Redirect('/Education/Lesson/Term', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }

    /**
     * @param int $Id
     * @param bool $Confirm
     *
     * @return Stage
     */
    public function frontendDestroyHoliday($Id = null, $Confirm = false)
    {

        $Stage = new Stage('Unterrichtsfreie Zeiträume', 'Löschen');

        if (($tblHoliday = Term::useService()->getHolidayById($Id))) {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Lesson/Term/Holiday', new ChevronLeft()
            ));

            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(
                            new Question() . ' Diese Unterrichtsfreien Tage wirklich löschen?',
                            array(
                                $tblHoliday->getFromDate()
                                . ($tblHoliday->getToDate() ? ' -  ' . $tblHoliday->getToDate() : ''),
                                $tblHoliday->getName(),
                                $tblHoliday->getTblHolidayType()->getName()
                            ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Education/Lesson/Term/Holiday/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            . new Standard(
                                'Nein', '/Education/Lesson/Term/Holiday', new Disable()
                            )
                        ),
                    )))))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            (Term::useService()->destroyHoliday($tblHoliday)
                                ? new Success(
                                    new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Unterrichtsfreien Tage wurde gelöscht')
                                . new Redirect('/Education/Lesson/Term/Holiday', Redirect::TIMEOUT_SUCCESS)
                                : new Danger(new Ban() . ' Die Unterrichtsfreien Tage konnte nicht gelöscht werden')
                                . new Redirect('/Education/Lesson/Term/Holiday', Redirect::TIMEOUT_ERROR)
                            )
                        ))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger(new Ban() . ' Die Unterrichtsfreien Tage konnte nicht gefunden werden'),
                        new Redirect('/Education/Lesson/Term/Holiday', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param null $YearId
     * @param null $CompanyId
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendImportHoliday($YearId = null, $CompanyId = null, $Data = null)
    {
        $Stage = new Stage('Schuljahr', 'Unterrichtsfreie Zeiträume importieren');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Term', new ChevronLeft()));

        if (($tblYear = Term::useService()->getYearById($YearId))) {
            $tblCompany = Company::useService()->getCompanyById($CompanyId);
            $list = array();
            if (($tblState = BasicData::useService()->getStateByName('Sachsen'))) {
                $list[$tblState->getId()] = $tblState->getName();
                if (Consumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_SACHSEN)) {
                    $global = $this->getGlobal();
                    $global->POST['Data'] = $tblState->getId();
                    $global->savePost();
                }
            }
            if (($tblState = BasicData::useService()->getStateByName('Berlin'))) {
                $list[$tblState->getId()] = $tblState->getName();
                if (Consumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_BERLIN)) {
                    $global = $this->getGlobal();
                    $global->POST['Data'] = $tblState->getId();
                    $global->savePost();
                }
            }
            if (($tblState = BasicData::useService()->getStateByName('Niedersachsen'))) {
                $list[$tblState->getId()] = $tblState->getName();
            }
            if (($tblState = BasicData::useService()->getStateByName('Thüringen'))) {
                $list[$tblState->getId()] = $tblState->getName();
            }

            $form = new Form(new FormGroup(new FormRow(new FormColumn(
                new SelectBox('Data', 'Bundesland', $list
            )))), new Primary('Importieren', new Save()));

            $Stage->setContent(new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel(
                                'Schuljahr',
                                $tblYear->getName() . ' ' . new Small(new Muted($tblYear->getDescription())),
                                Panel::PANEL_TYPE_INFO
                            )
                            , 6),
                        new LayoutColumn(
                            new Panel(
                                'Schule',
                                $tblCompany ? $tblCompany->getDisplayName() : 'Alle Schulen',
                                Panel::PANEL_TYPE_INFO
                            )
                            , 6),
                    ))
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Well(
                                Term::useService()->importHolidayFromSystem($form, $tblYear, $tblCompany ? $tblCompany : null, $Data)
                            )
                        ))
                    ))
                ))
            )));

        } else {
            return $Stage
                . new Danger('Schuljahr nicht gefunden.', new Ban())
                . new Redirect('/Education/Lesson/Term', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }
}
