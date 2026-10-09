<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineGradebook;

use SPHERE\Application\Api\ParentStudentAccess\ApiOnlineGradebook;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRule;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\MyAccount\MyAccount;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @param null $YearId
     *
     * @return Stage|string
     */
    public function frontendOnlineGradebook($YearId = null)
    {
        $Stage = new Stage('Notenübersicht', 'Eltern/Schüler');
        $Stage->setMessage(
            new Container('Anzeige der Zensuren für die Schüler und Eltern.')
            .new Container('Der angemeldete Schüler sieht nur seine eigenen Zensuren.')
            .new Container('Der angemeldete Sorgeberechtigte sieht nur die Zensuren seiner Kinder.')
        );

        $rowList = array();

        list($tblDisplayYearList, $BlockedList, $data) = OnlineGradebook::useService()->getOnlineGradeBookYearAndBlockedAndDataList();

        if (!empty($tblDisplayYearList)) {
            $tblDisplayYearList = $this->getSorter($tblDisplayYearList)->sortObjectBy('DisplayName');
            $lastYear = end($tblDisplayYearList);
            /** @var TblYear $year */
            foreach ($tblDisplayYearList as $year) {
                $Stage->addButton(
                    new Standard(
                        ($YearId === null && $year->getId() == $lastYear->getId()) ? new Info(new Bold($year->getDisplayName())) : $year->getDisplayName(),
                        '/ParentStudentAccess/OnlineGradebook',
                        null,
                        array(
                            'YearId' => $year->getId()
                        )
                    )
                );
            }

            if ($YearId === null) {
                $YearId = $lastYear->getId();
            }
        }

        if (($tblYear = Term::useService()->getYearById($YearId))) {
            if (!empty($data)) {
                if (isset($data[$tblYear->getId()])) {
                    foreach ($data[$tblYear->getId()] as $personId => $tblStudentEducation) {
                        if ($tblPerson = Person::useService()->getPersonById($personId)) {
                            $courses = DivisionCourse::useService()->getCurrentMainCoursesByStudentEducation($tblStudentEducation);
                            $button = new External(
                                'Notenübersicht herunterladen',
                                'SPHERE\Application\Api\Document\Standard\GradebookOverview\Create',
                                new Download(),
                                array('PersonId' => $personId, 'YearId' => $tblYear->getId(), 'View' => 'Parent'),
                                'Notenübersicht herunterladen'
                            );
                            $rowList[] = new LayoutRow(new LayoutColumn(new Title(
                                new PullClear($tblPerson->getLastFirstName() . ' ' . new Muted(new Small($courses)) . new PullRight($button)),
                            )));

                            $rowList[] = new LayoutRow(new LayoutColumn(
                                Grade::useService()->getStudentOverviewDataByPerson($tblPerson, $tblYear, $tblStudentEducation, true, false)
                            ));
                        }
                    }
                }
            }
        }

        // Schüler, die den Zugriff für den angemeldeten Sorgeberechtigten gesperrt haben
        $BlockedContent = '';
        /** @var TblPerson $tblPersonStudent */
        foreach ($BlockedList as $tblPersonStudent) {
            $BlockedContent .= new Title($tblPersonStudent->getLastFirstName())
                .MyAccount::useService()->getCustodyBlockedWarning($tblPersonStudent);
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        ($tblYear ?
                            new Panel('Schuljahr', $tblYear->getDisplayName(), Panel::PANEL_TYPE_INFO)
                            : '')
                        . ApiOnlineGradebook::receiverModal()
                    )
                ))),
                ($tblYear ? new LayoutGroup($rowList) : null),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            $BlockedContent
                        )
                    )
                )
            ))
        );

        return $Stage;
    }

    /**
     * @param TblScoreRule $tblScoreRule
     *
     * @return string
     */
    public function getScoreRuleModalContent(TblScoreRule $tblScoreRule): string
    {
        $structure = array();
        if ($tblScoreRule->getDescriptionForExtern() != '') {
            $structure[] = str_replace("\n", '<br/>', $tblScoreRule->getDescriptionForExtern()) . '<br/>';
        } else {
            $structure = Grade::useService()->getScoreRuleStructure($tblScoreRule, $structure);
        }

        return new Panel(
            'Berechnungsvorschrift: ' . $tblScoreRule->getName(),
            implode('<br/>', $structure),
            Panel::PANEL_TYPE_INFO
        );
    }
}
