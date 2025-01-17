<?php
namespace SPHERE\Application\Reporting\Custom\BadDueben\Person;

use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\Reporting\Standard\Person\Person as PersonStandard;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Custom\BadDueben\Person
 */
class Frontend  extends Extension implements IFrontendInterface
{

    /**
     * @return array
     */
    public function getDivisionListByLevel($level = null)
    {

        $DivisionList = array();
        if(($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT))
            && ($tblPersonStudentList = $tblGroup->getPersonList())
            && ($tblYearList = Term::useService()->getYearByNow())
        ){
            foreach($tblYearList as $tblYear){
                foreach($tblPersonStudentList as $tblPersonStudent){
                    if(($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPersonStudent, $tblYear))
                        && ($tblDivisionCourse = $tblStudentEducation->getTblDivision())){
                        if($level !== null && $tblStudentEducation->getLevel() == $level){
                            $this->setDivisionDataList($tblStudentEducation, $tblYear, $tblDivisionCourse, $DivisionList);
                        } elseif($level === null) {
                            $this->setDivisionDataList($tblStudentEducation, $tblYear, $tblDivisionCourse, $DivisionList);
                        }
                    }
                }
            }
        }
        return $DivisionList;
    }

    private function setDivisionDataList(TblStudentEducation $tblStudentEducation, TblYear $tblYear, TblDivisionCourse $tblDivisionCourse, &$DivisionList)
    {

        $DivisionList[$tblStudentEducation->getLevel()]['Year'] = $tblYear->getName();
        $DivisionList[$tblStudentEducation->getLevel()]['DivisionName'][$tblDivisionCourse->getId()] = $tblDivisionCourse->getDisplayName();
        if(($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())){
            $DivisionList[$tblStudentEducation->getLevel()]['DivisionType'][$tblSchoolType->getId()] = $tblSchoolType->getName();
        }
        if(($tblPersonList = $tblDivisionCourse->getStudents())){
            $DivisionList[$tblStudentEducation->getLevel()]['Count'][$tblDivisionCourse->getId()] = count($tblPersonList);
            foreach($tblPersonList as $tblPerson){
                $DivisionList[$tblStudentEducation->getLevel()]['Person'][$tblPerson->getId()] = $tblPerson;
            }
        }
    }

    /**
     * @param null $level
     *
     * @return Stage
     */
    public function frontendClassList($level = null)
    {

        $Stage = new Stage('Auswertung', 'Klassenlisten');
        $Route = '/Reporting/Custom/BadDueben/Person/ClassList';
        // Sammeln der Klassenlisten
        $DivisionList = $this->getDivisionListByLevel($level);
        if($level === null){
            $TableContent = array();
            if (!empty($DivisionList)) {
                foreach ($DivisionList as $level => $Division) {
                    $item['Year'] = $Division['Year'];
                    $item['Level'] = $level;
                    $item['Division'] = '';
                    if(isset($Division['DivisionName'])){
                        $item['Division'] = implode(', ', $Division['DivisionName']);
                    }
                    $item['Type'] = '';
                    $item['TypeList'] = array();
                    if(isset($Division['DivisionType'])){
                        $item['Type'] = implode(', ', $Division['DivisionType']);
                        $item['TypeList'] = $Division['DivisionType'];
                    }
                    $item['Count'] = '';
                    if(isset($Division['Count'])){
                        $item['Count'] = array_sum($Division['Count']);
                    }
                    $item['Option'] = new Standard('', $Route, new EyeOpen(),
                        array('level' => $level), 'Anzeigen');
                    array_push($TableContent, $item);
                }
            }
            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new TableData($TableContent, null,
                        array(
                            'Year'     => 'Jahr',
                            'Level'    => 'Stufe',
                            'Division' => 'Klasse(n)',
                            'Type'     => 'Schulart',
                            'Count'    => 'Schüler',
                            'Option'   => '',
                        ), array(
                            'columnDefs' => array(
                                array('type' => 'natural', 'targets' => array(1,3)),
                                array("orderable" => false, "targets"   => -1),
                            ),
                            'order' => array(
                                array(0, 'desc'),
                                array(1, 'asc'),
                                array(2, 'asc'),
                            ),
                        )
                    )
                    , 12)), new Title(new Listing().' Übersicht')))
            );
        } else {
            $Stage->addButton(new Standard('Zurück', $Route, new ChevronLeft()));
            $tblPersonList = array();
            if(isset($DivisionList[$level]['Person'])){
                $tblPersonList = $DivisionList[$level]['Person'];
            }
            if(($TableContent = Person::useService()->createClassList($tblPersonList))) {
                $Stage->addButton(
                    new Primary('Herunterladen', '/Api/Reporting/Custom/BadDueben/Common/ClassList/Download', new Download(), array('level' => $level))
                );
                $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
            }
            $SchoolTypeList = array();
            if(isset($DivisionList[$level]['DivisionType'])){
                $SchoolTypeList = $DivisionList[$level]['DivisionType'];
            }
            $Stage->setContent(
                new Layout(array(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(new Panel('Jahr', $DivisionList[$level]['Year'], Panel::PANEL_TYPE_SUCCESS), 4),
                    new LayoutColumn(new Panel('Stufe', $level, Panel::PANEL_TYPE_SUCCESS), 4),
                    (!empty($SchoolTypeList)
                        ? new LayoutColumn(new Panel((count($SchoolTypeList) == 1 ? 'Schulart' : 'Schularten'), $SchoolTypeList, Panel::PANEL_TYPE_SUCCESS), 4)
                        : ''),
                ))),
                    new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new TableData($TableContent, null,
                            array(
                                'Division'              => 'Klasse(n)',
                                'Type'                  => 'Schulart',
                                'Mentor'                => 'Gruppe',
                                'Gender'                => 'Geschlecht',
                                'LastName'              => 'Nachname',
                                'FirstName'             => 'Vorname',
                                'StreetName'            => 'Straße',
                                'StreetNumber'          => 'Nr.',
                                'Code'                  => 'PLZ',
                                'City'                  => 'Wohnort',
                                'District'              => 'Ortsteil',
                                'PhoneNumbersPrivate'   => new ToolTip('Tel. privat '.new Info(), 'Schüler Festnetz'),
                                'PhoneNumbersBusiness'  => new ToolTip('S1 Tel. dienstlich '.new Info(), 'Festnetz'),
                                'PhoneNumbersGuardian1' => new ToolTip('S1 Tel. '.new Info(), 'Mobil'),
                                'PhoneNumbersGuardian2' => new ToolTip('S2 Tel. '.new Info(), 'Mobil'),
                                'MailAddress'           => 'E-Mail',
                                'Birthday'              => 'Geb.-Datum',
                                'Birthplace'            => 'Geb.-Ort',
                            ),
                            array(
                                "pageLength" => -1,
                                "responsive" => false,
                                'order'      => array(
                                    array(2, 'asc'),
                                    array(4, 'asc'),
                                    array(5, 'asc'),
                                ),
                                "columnDefs" => array(
                                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => array(4,5)),
                                    array('type' => 'natural', 'targets' => 7),
                                ),
                            )
                        )
                    ))),
                    PersonStandard::useFrontend()->getGenderLayoutGroup($tblPersonList)
                ))
            );
        }
        return $Stage;
    }
}
