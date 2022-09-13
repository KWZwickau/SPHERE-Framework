<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 19.12.2018
 * Time: 12:11
 */

namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\Education\DivisionCourse\ApiDivisionCourseStudent;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Common\Frontend\Icon\Repository\Pen;
use SPHERE\Common\Frontend\Icon\Repository\History;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Strikethrough;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Link\Repository\Link;
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
            // todo verlauf mit Edit + neu für neues Schuljahr auswahl begrenzen auf neue Schuljahre + noch kein Eintrag TblStudentEducation
            // tabelle wie bei Kursen
            $studentEducationList = array();
            if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListByPerson($tblPerson))) {
                $tblStudentEducationList = (new Extension())->getSorter($tblStudentEducationList)
                    ->sortObjectBy('YearName', null, Sorter::ORDER_DESC);
                foreach ($tblStudentEducationList as $tblStudentEducation) {
                    // todo gleiches Level Schuljahr wiederholung
                    $isInActive = $tblStudentEducation->isInActive();
                    $year = ($tblYear = $tblStudentEducation->getServiceTblYear()) ? $tblYear->getDisplayName() : new WarningText('Kein Schuljahr hinterlegt');
                    $company = ($tblCompany = $tblStudentEducation->getServiceTblCompany())
                        ? $tblCompany->getDisplayName() : new WarningText('Keine Schule hinterlegt');
                    $level = $tblStudentEducation->getLevel() ?: new WarningText('Keine Klassenstufe hinterlegt');
                    $schoolType = ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                        ? $tblSchoolType->getName() : new WarningText('Keine Schulart hinterlegt');

                    $warningCourse = '';
                    if ($tblSchoolType && $tblSchoolType->getShortName() == 'OS' && intval($level) > 6
                    ) {
                        $warningCourse = new WarningText('Keine Bildungsgang hinterlegt');
                    }
                    $course = ($tblCourse = $tblStudentEducation->getServiceTblCourse()) ? $tblCourse->getName() : $warningCourse;
                    $division = ($tblDivision = $tblStudentEducation->getTblDivision()) ? $tblDivision->getName() : '';
                    $coreGroup = ($tblCoreGroup = $tblStudentEducation->getTblCoreGroup()) ? $tblCoreGroup->getName() : '';

                    $item['Year'] = $isInActive ? new Strikethrough($year) : $year;
                    $item['SchoolType'] = $isInActive ? new Strikethrough($schoolType) : $schoolType;
                    $item['Company'] = $isInActive ? new Strikethrough($company) : $company;
                    $item['Level'] = $isInActive ? new Strikethrough($level) : $level;
                    $item['Course'] = $isInActive ? new Strikethrough($course) : $course;
                    $item['Division'] = $isInActive ? new Strikethrough($division) : $division;
                    $item['CoreGroup'] = $isInActive ? new Strikethrough($coreGroup) : $coreGroup;
                    if ($AllowEdit) {
                        $item['Option'] = (new Link('Bearbeiten', ApiDivisionCourseStudent::getEndpoint(), new Pen()))
                            ->ajaxPipelineOnClick(ApiDivisionCourseStudent::pipelineOpenEditStudentEducationModal($tblPerson->getId(), null, $tblStudentEducation->getId()));
                    }
                    $studentEducationList[] = $item;
                }
            }

            $divisionCourseFrontend = DivisionCourse::useFrontend();
            $backgroundColor = '#E0F0FF';
            $headerColumnList[] = $divisionCourseFrontend->getTableHeaderColumn('Schul&shy;jahr', $backgroundColor);
            $headerColumnList[] = $divisionCourseFrontend->getTableHeaderColumn('Schul&shy;art', $backgroundColor);
            $headerColumnList[] = $divisionCourseFrontend->getTableHeaderColumn('Schule', $backgroundColor);
            $headerColumnList[] = $divisionCourseFrontend->getTableHeaderColumn('Klassen&shy;stufe', $backgroundColor);
            $headerColumnList[] = $divisionCourseFrontend->getTableHeaderColumn('Bildungs&shy;gang', $backgroundColor);
            $headerColumnList[] = $divisionCourseFrontend->getTableHeaderColumn('Klasse', $backgroundColor);
            $headerColumnList[] = $divisionCourseFrontend->getTableHeaderColumn('Stamm&shy;gruppe', $backgroundColor);
            if ($AllowEdit) {
                $headerColumnList[] = $divisionCourseFrontend->getTableHeaderColumn('&nbsp; ', $backgroundColor, '95px');
            }

            $newLink = '';
            if($AllowEdit == 1){
                // todo neu nur bei verfügbaren zukünftigen Schuljahren
//                $newLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
//                    ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditStudentProcessContent($PersonId));
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
}