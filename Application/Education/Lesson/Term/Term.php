<?php
namespace SPHERE\Application\Education\Lesson\Term;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\System\BasicData\BasicData;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Clock;
use SPHERE\Common\Frontend\Icon\Repository\Holiday;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Time;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

/**
 * Class Term
 *
 * @package SPHERE\Application\Education\Lesson\Term
 */
class Term extends Extension implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Schuljahr'),
                new Link\Icon(new Calendar()))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__ . '::frontendDashboard'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Create/Year', __NAMESPACE__ . '\Frontend::frontendCreateYear'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Create/WizardYear', __NAMESPACE__ . '\Frontend::frontendWizardYear'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Edit/Year', __NAMESPACE__ . '\Frontend::frontendEditYear'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Destroy/Year', __NAMESPACE__ . '\Frontend::frontendDestroyYear'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Create/Period', __NAMESPACE__ . '\Frontend::frontendCreatePeriod'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Edit/Period', __NAMESPACE__ . '\Frontend::frontendEditPeriod'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Destroy/Period', __NAMESPACE__ . '\Frontend::frontendDestroyPeriod'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Link/Period', __NAMESPACE__ . '\Frontend::frontendLinkPeriod'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Choose/Period', __NAMESPACE__ . '\Frontend::frontendChoosePeriod'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Holiday', __NAMESPACE__ . '\Frontend::frontendHoliday'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Holiday/Edit', __NAMESPACE__ . '\Frontend::frontendEditHoliday'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Holiday/Destroy', __NAMESPACE__ . '\Frontend::frontendDestroyHoliday'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Holiday/Select', __NAMESPACE__ . '\Frontend::frontendSelectHoliday'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Holiday/Import', __NAMESPACE__ . '\Frontend::frontendImportHoliday'
        ));
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {
        return new Frontend();
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Schuljahr', 'Dashboard');

        $Stage->addButton(new Standard('Schuljahr mit Zeiträumen', __NAMESPACE__ . '\Create\WizardYear', new Plus(), null,
            'Schuljahr mit Zeiträumen und Ferien erstellen'));
        $Stage->addButton(new Standard('Schuljahr', __NAMESPACE__ . '\Create\Year', new Calendar(), null,
            'Erstellen/Bearbeiten'));
        $Stage->addButton(new Standard('Zeitraum', __NAMESPACE__ . '\Create\Period', new Time(), null,
            'Erstellen/Bearbeiten'));
        $Stage->addButton(new Standard('Unterrichtsfreie Zeiträume', __NAMESPACE__ . '\Holiday', new Holiday(), null,
            'Erstellen/Bearbeiten'));

        $tblYearAll = Term::useService()->getYearAll();
        $Year = array();
        if ($tblYearAll) {
            array_walk($tblYearAll, function (TblYear $tblYear) use (&$Year) {
                $tblPeriodAll = $tblYear->getPeriodList(false, true);
                if ($tblPeriodAll) {
                    array_walk($tblPeriodAll, function (TblPeriod &$tblPeriod) use ($tblYear) {

                        $tblPeriod = $tblPeriod->getName()
                            . ($tblPeriod->getDescription() ? ' ' . new Muted(new Small($tblPeriod->getDescription())) : '')
                            . ($tblPeriod->isLevel12() ? new Muted(' 12. Klasse Gy / 13. Klasse BGy') : '')
                            . '<br/>' . $tblPeriod->getFromDate() . ' - ' . $tblPeriod->getToDate();
                    });
                } else {
                    $tblPeriodAll = array();
                }

                list($startDate, $endDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
                if ($startDate && $endDate
                    && BasicData::useService()->getHolidayAllBy($startDate, $endDate)
                ) {
                    $hasHolidayImportButton = true;
                } else {
                    $hasHolidayImportButton = false;
                }

                $holidayList[] = $this->getHolidaysPanelForSchool($tblYear, $hasHolidayImportButton, null);
                if (($tblSchoolAll = School::useService()->getSchoolAll())) {
                    foreach ($tblSchoolAll as $tblSchool) {
                        if (($tblCompany = $tblSchool->getServiceTblCompany())) {
                            $holidayList[] = $this->getHolidaysPanelForSchool($tblYear, $hasHolidayImportButton, $tblCompany);
                        }
                    }
                }

                array_push($Year, array(
                    'Schuljahr' => $tblYear->getDisplayName(),
                    'Zeiträume' => new Panel(
                        (empty($tblPeriodAll) ?
                            'Keine Zeiträume hinterlegt'
                            : count($tblPeriodAll) . ' Zeiträume'),
                        $tblPeriodAll,
                        (empty($tblPeriodAll) ? Panel::PANEL_TYPE_WARNING : Panel::PANEL_TYPE_DEFAULT)
                        , new Standard('', __NAMESPACE__ . '\Choose\Period', new Clock(),
                        array('Id' => $tblYear->getId()), 'Zeitraum zuweisen'
                    )),
                    'Holiday' => implode('', $holidayList)
                ));
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Headline(new Calendar() . ' Im System vorhandene Schuljahre'),
                            new TableData(
                                $Year, null, array(
                                    'Schuljahr' => 'Schuljahr',
                                    'Zeiträume' => 'Zeiträume',
                                    'Holiday' => 'Unterrichtsfreie Zeiträume',
                                ), array(
                                    'order'      => array(
                                        array('0', 'desc')
                                    ),
                                    'columnDefs' => array(
                                        array('orderable' => false, 'targets' => array(1,2)),
                                    ),
                                )
                            )
                        ))
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param TblYear $tblYear
     * @param bool $hasHolidayImportButton
     * @param TblCompany|null $tblCompany
     *
     * @return Panel
     */
    private function getHolidaysPanelForSchool(TblYear $tblYear, $hasHolidayImportButton, TblCompany $tblCompany = null)
    {
        $holidayList = array();
        $tblYearHolidayAllByYear = Term::useService()->getYearHolidayAllByYear($tblYear, $tblCompany);
        if ($tblYearHolidayAllByYear) {
            $tblHolidayList = array();
            foreach ($tblYearHolidayAllByYear as $tblYearHoliday) {
                if (($item = $tblYearHoliday->getTblHoliday())) {
                    $tblHolidayList[$item->getId()] = $item;
                }
            }
            // sort
            $tblHolidayList = $this->getSorter($tblHolidayList)->sortObjectBy('FromDate', new DateTimeSorter());
            foreach ($tblHolidayList as $tblHoliday) {
                $holidayList[] = $tblHoliday->getName() . ' '
                    . new Muted(new Small($tblHoliday->getFromDate()
                        . ($tblHoliday->getToDate() ? ' - ' . $tblHoliday->getToDate() : ' ')));
            }
        }


        $title = $tblCompany ? $tblCompany->getDisplayName() : 'Alle Schulen';
//        $title .= ' ' . (empty($holidayList)
//            ? 'Keine Unterrichtsfreie Zeiträume hinterlegt'
//            : count($holidayList) . ' Unterrichtsfreie Zeiträume');

        return new Panel(
            $title,
            $holidayList,
            $tblCompany ? Panel::PANEL_TYPE_DEFAULT : Panel::PANEL_TYPE_INFO,
//            Panel::PANEL_TYPE_DEFAULT,
            (new Standard(
                '',
                __NAMESPACE__ . '\Holiday\Select',
                new Holiday(),
                array(
                    'YearId' => $tblYear->getId(),
                    'CompanyId' => $tblCompany ? $tblCompany->getId() : null
                ),
                'Unterrichtsfreie Zeiträume zuweisen'
            ))
            . ($hasHolidayImportButton
                ? (new Standard(
                    'Ferien und Feiertage importieren',
                    __NAMESPACE__ . '\Holiday\Import',
                    null,
                    array(
                        'YearId' => $tblYear->getId(),
                        'CompanyId' => $tblCompany ? $tblCompany->getId() : null
                    )
                ))
                : ''
            )
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {
        return new Service(
            new Identifier('Education', 'Lesson', 'Term', null, Consumer::useService()->getConsumerBySession()),
            __DIR__ . '/Service/Entity', __NAMESPACE__ . '\Service\Entity'
        );
    }
}
