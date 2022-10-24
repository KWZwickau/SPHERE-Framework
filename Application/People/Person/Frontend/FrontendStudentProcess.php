<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 19.12.2018
 * Time: 12:11
 */

namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\Education\DivisionCourse\ApiDivisionCourseStudent;
use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Pen;
use SPHERE\Common\Frontend\Icon\Repository\History;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Strikethrough;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter;

/**
 * Class FrontendStudentProcess
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendStudentProcess extends FrontendReadOnly
{
    const TITLE = 'Schülerakte - Schulverlauf';

    /**
     * @param null $PersonId
     * @param int  $AllowEdit
     *
     * @return string
     */
    public static function getStudentProcessContent($PersonId = null, $AllowEdit = 1): string
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            // Verlauf mit Edit + neu für neues Schuljahr auswahl begrenzen auf neue Schuljahre + noch kein Eintrag TblStudentEducation
            $studentEducationList = array();
            if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListByPerson($tblPerson))) {
                $levelList = array();
                // Sortierung aufsteigend, notwendig wegen Schuljahrwiederholung
                $tblStudentEducationList = (new Extension())->getSorter($tblStudentEducationList)->sortObjectBy('YearNameForSorter', null, Sorter::ORDER_ASC);
                /** @var TblStudentEducation $tblStudentEducation */
                foreach ($tblStudentEducationList as $tblStudentEducation) {
                    $isInActive = $tblStudentEducation->isInActive();
                    $year = ($tblYear = $tblStudentEducation->getServiceTblYear()) ? $tblYear->getDisplayName() : new WarningText('Kein Schuljahr hinterlegt');
                    $company = ($tblCompany = $tblStudentEducation->getServiceTblCompany())
                        ? $tblCompany->getDisplayName() : new WarningText('Keine Schule hinterlegt');
                    if (($levelValue = intval($tblStudentEducation->getLevel()))) {
                        $level = $levelValue;
                        if (!$isInActive && isset($levelList[$levelValue])) {
                            $level .= ' ' . new ToolTip(new Info(), 'Schuljahrwiederholung');
                        }
                    } else {
                        $level = new WarningText('Keine Klassenstufe hinterlegt');
                    }
                    $schoolType = ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                        ? $tblSchoolType->getName() : new WarningText('Keine Schulart hinterlegt');

                    $warningCourse = '';
                    if ($tblSchoolType && $tblSchoolType->getShortName() == 'OS' && $levelValue > 6) {
                        $warningCourse = new WarningText('Keine Bildungsgang hinterlegt');
                    }
                    $course = ($tblCourse = $tblStudentEducation->getServiceTblCourse()) ? $tblCourse->getName() : $warningCourse;
                    $division = ($tblDivision = $tblStudentEducation->getTblDivision()) ? $tblDivision->getName() : '';
                    $divisionTeachers = $tblDivision ? $tblDivision->getDivisionTeacherNameListString() : '';
                    $coreGroup = ($tblCoreGroup = $tblStudentEducation->getTblCoreGroup()) ? $tblCoreGroup->getName() : '';
                    $tudors =  $tblCoreGroup ? $tblCoreGroup->getDivisionTeacherNameListString() : '';

                    $item['Year'] = $isInActive ? new Strikethrough($year) : $year;
                    $item['SchoolType'] = $isInActive ? new Strikethrough($schoolType) : $schoolType;
                    $item['Company'] = $isInActive ? new Strikethrough($company) : $company;
                    $item['Level'] = $isInActive ? new Strikethrough($level) : $level;
                    $item['Course'] = $isInActive ? new Strikethrough($course) : $course;
                    $item['Division'] = $isInActive ? new Strikethrough($division) : $division;
                    $item['DivisionTeachers'] = $isInActive ? new Strikethrough($divisionTeachers) : $divisionTeachers;
                    $item['CoreGroup'] = $isInActive ? new Strikethrough($coreGroup) : $coreGroup;
                    $item['Tudors'] = $isInActive ? new Strikethrough($tudors) : $tudors;
                    if ($AllowEdit) {
                        $item['Option'] = (new Link('Bearbeiten', ApiPersonEdit::getEndpoint(), new Pen()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditStudentProcessContent($tblPerson->getId(), $tblStudentEducation->getId()));
                    }
                    $studentEducationList[] = $item;

                    if (!$isInActive && $levelValue && !isset($levelList[$levelValue])) {
                        $levelList[$levelValue] = $levelValue;
                    }
                }

                // notwendig wegen Schuljahrwiederholung
                $studentEducationList = array_reverse($studentEducationList);
            }

            $divisionCourseFrontend = DivisionCourse::useFrontend();
            $backgroundColor = '#E0F0FF';
            $headerColumnList[] = $divisionCourseFrontend->getTableHeaderColumn('Schul&shy;jahr', $backgroundColor);
            $headerColumnList[] = $divisionCourseFrontend->getTableHeaderColumn('Schul&shy;art', $backgroundColor);
            $headerColumnList[] = $divisionCourseFrontend->getTableHeaderColumn('Schule', $backgroundColor);
            $headerColumnList[] = $divisionCourseFrontend->getTableHeaderColumn('Klassen&shy;stufe', $backgroundColor);
            $headerColumnList[] = $divisionCourseFrontend->getTableHeaderColumn('Bildungs&shy;gang', $backgroundColor);
            $headerColumnList[] = $divisionCourseFrontend->getTableHeaderColumn('Klasse', $backgroundColor);
            $headerColumnList[] = $divisionCourseFrontend->getTableHeaderColumn('Klasse&shy;lehrer', $backgroundColor);
            $headerColumnList[] = $divisionCourseFrontend->getTableHeaderColumn('Stamm&shy;gruppe', $backgroundColor);
            $headerColumnList[] = $divisionCourseFrontend->getTableHeaderColumn('Tudor', $backgroundColor);
            if ($AllowEdit) {
                $headerColumnList[] = $divisionCourseFrontend->getTableHeaderColumn('&nbsp; ', $backgroundColor, '95px');
            }

            $newLink = '';
            if($AllowEdit == 1){
                $newLink = (new Link(new Plus() . ' Hinzufügen', ApiDivisionCourseStudent::getEndpoint()))
                    ->ajaxPipelineOnClick(ApiDivisionCourseStudent::pipelineOpenCreateStudentEducationModal($PersonId));
            }

            $DivisionString = FrontendReadOnly::getDivisionString($tblPerson);

            return TemplateReadOnly::getContent(
                self::TITLE,
                $divisionCourseFrontend->getTableCustom($headerColumnList, $studentEducationList),
                array($newLink),
                'der Person ' . new Bold(new Success($tblPerson->getFullName())) . $DivisionString,
                new History()
            );
        }

        return '';
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return string
     */
    public static function getEditStudentProcessTitle(TblPerson $tblPerson = null): string
    {
        return new Title(new History() . ' ' . self::TITLE, self::getEditTitleDescription($tblPerson)) . self::getDataProtectionMessage();
    }
}