<?php

namespace SPHERE\Application\People\Dashboard;

use DateInterval;
use DateTime;
use SPHERE\Application\Api\People\Search\ApiPersonSearch;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Group as GroupIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

class Frontend extends Extension implements IFrontendInterface
{
    public function frontendDashboard()
    {
        $tblGroupLockedList = array();
        $tblGroupCustomList = array();
        if (($tblGroupAll = Group::useService()->getGroupAllSorted())) {
            foreach ($tblGroupAll as $tblGroup) {
                // alte Personengruppen - Stammgruppen überspringen
                if ($tblGroup->isCoreGroup()) {
                    continue;
                }

                $content = new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn($tblGroup->getName() . new Muted(new Small('<br/>' . $tblGroup->getDescription(true))), 6),
                    new LayoutColumn(new Muted(new Small(Group::useService()->countMemberByGroup($tblGroup) . '&nbsp;Mitglieder')), 5),
                    new LayoutColumn(new PullRight(
                        new Standard('', '/People', new GroupIcon(), array('PseudoId' => 'G' . $tblGroup->getId()))
                    ), 1)
                ))));

                if ($tblGroup->isLocked()) {
                    $tblGroupLockedList[] = $content;
                    if ($tblGroup->getMetaTable() == 'STUDENT') {
                        $yearListForStudentCount = array();
                        if (($tblYearNowList = Term::useService()->getYearByNow())) {
                            foreach ($tblYearNowList as $tblYearNow) {
                                $yearListForStudentCount[$tblYearNow->getId()] = $tblYearNow;
                            }
                        }
                        $date = (new DateTime('now'))->add(new DateInterval('P2M'));
                        if (($tblYearFutureList = Term::useService()->getYearAllByDate($date))) {
                            foreach ($tblYearFutureList as $tblYearFuture) {
                                $yearListForStudentCount[$tblYearFuture->getId()] = $tblYearFuture;
                            }
                        }

                        $rows[] = new LayoutRow(new LayoutColumn('Schüler / Schuljahr'));
                        foreach ($yearListForStudentCount as $tblYearTemp) {
                            $rows[] = new LayoutRow(new LayoutColumn(
                                new Layout(new LayoutGroup(new LayoutRow(array(
                                        new LayoutColumn(new Bold($tblYearTemp->getDisplayName()), 6),
                                        new LayoutColumn(new Muted(new Small(DivisionCourse::useService()->getCountStudentsByYear($tblYearTemp). '&nbsp;Mitglieder')), 5),
                                        new LayoutColumn(new PullRight(
                                            (new Standard('', ApiPersonSearch::getEndpoint(), new EyeOpen()))
                                                ->ajaxPipelineOnClick(ApiPersonSearch::pipelineOpenYearStudentCountModal($tblYearTemp->getId()))
                                        ), 1)
                                    )
                                )))
                            ));
                        }

                        $content = new Layout(new LayoutGroup($rows));
                        $tblGroupLockedList[] = $content;
                    }
                } else {
                    $tblGroupCustomList[] = $content;
                }
            }
        }

        /*
         * Kurse aus der Bildung
         */
        $dataCourseList = array();
        if (($tblYearNowList = Term::useService()->getYearByNow())) {
            foreach ($tblYearNowList as $tblYear) {
                if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByIsShownInPersonData($tblYear))) {
                    $tblDivisionCourseList = (new Extension())->getSorter($tblDivisionCourseList)->sortObjectBy('DisplayName', new StringNaturalOrderSorter());
                    /** @var TblDivisionCourse $tblDivisionCourse */
                    foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                        if ($tblDivisionCourse->getType()->getIsCourseSystem()) {
                            $countStudentSubjectPeriod1 = DivisionCourse::useService()->getCountStudentsBySubjectDivisionCourseAndPeriod($tblDivisionCourse, 1);
                            $countStudentSubjectPeriod2 = DivisionCourse::useService()->getCountStudentsBySubjectDivisionCourseAndPeriod($tblDivisionCourse, 2);
                            $countContent = new Muted(new Small(
                                '1. HJ: ' . $countStudentSubjectPeriod1 . ' Mitglieder'
                                    . '<br/>'
                                    . ' 2. HJ: ' . $countStudentSubjectPeriod2 . ' Mitglieder'
                            ));
                        } else {
                            $countContent = new Muted(new Small($tblDivisionCourse->getCountStudents() . '&nbsp;Mitglieder'));
                        }

                        $dataCourseList[] = new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn($tblDivisionCourse->getName() . new Muted(new Small('<br/>' . $tblDivisionCourse->getDescription())), 6),
                            new LayoutColumn($countContent, 5),
                            new LayoutColumn(new PullRight(
                                new Standard('', '/People', new GroupIcon(), array('PseudoId' => 'C' . $tblDivisionCourse->getId()))
                            ), 1)
                        ))));
                    }
                }
            }
        }

        $stage = new Stage('Dashboard', 'Personen');

        $stage->setContent(
            ApiPersonSearch::receiverModal()
            . new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    new Panel('Personen in festen Gruppen', $tblGroupLockedList), 4
                ),
                !empty($tblGroupCustomList) ?
                    new LayoutColumn(
                        new Panel('Personen in individuellen Gruppen', $tblGroupCustomList), 4) : null,
                new LayoutColumn(
                    new Panel('Schüler in Kursen', $dataCourseList), 4
                )
            ))))
        );

        return $stage;
    }
}