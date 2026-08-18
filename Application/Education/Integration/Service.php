<?php
namespace SPHERE\Application\Education\Integration;

use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Common\Frontend\Icon\Repository\Commodity;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Bold;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Integration
 */
class Service
{

    /**
     * @return array
     */
    public function getSupportPerson()
    {
        $PersonList = array();
        // Support auf die letzten 5 Jahre eingrenzen damit Person zur Liste gefügt wird, angezeigt wird dann aber alles
        $Date = new \DateTime('now');
        $Date = $Date->sub(new \DateInterval("P5Y"));
        if(($tblSupportList = Student::useService()->getSupportListByDate($Date))){
            $tblGroupStudent = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT);
            foreach($tblSupportList as $tblSupport){
                if(($tblPerson = $tblSupport->getServiceTblPerson())){
                    // nur Schüler berücksichtigen
                    if(Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroupStudent)){
                        $PersonList[$tblPerson->getId()] = $tblPerson;
                    }
                }
            }
        }
        return $PersonList;
    }

    public function getSupportTableByPersonList($PersonList)
    {

        $tableContent = array();
        if(!empty($PersonList)){
            foreach($PersonList as $tblPerson){
                $item = array();
                $item['Name'] = $tblPerson->getLastFirstName();
                $item['Course'] = '';
                $item['SchoolType'] = '';
                if(($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))){
                    if((($tblDivision = $tblStudentEducation->getTblDivision()))){
                        $item['Course'] = $tblDivision->getDisplayName();
                    }
                    if((($tblCoreGroup = $tblStudentEducation->getTblCoreGroup()))){
                        if($item['Course']){
                            $item['Course'] .= new Container($tblCoreGroup->getDisplayName());
                        } else {
                            $item['Course'] = $tblCoreGroup->getDisplayName();
                        }
                    }
                    if(($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())){
                        $item['SchoolType'] = $tblSchoolType->getName();
                    }
                }
                $item['SupportList'] = '';
                $item['Option'] = (new Standard(
                    '', '/Education/Integration/Selected', new Commodity(),
                    array(
                        'PersonId'   => $tblPerson->getId(),
                        'Open'   => 1
                    ),
                    'Inklusion des Schülers verwalten'
                ));
                if(($tblSupportList = Student::useService()->getSupportByPerson($tblPerson))){
                    // ToDO List als einzelne Variablen evtl. als Array speichern für eventuellen Excel
                    $item['SupportList'].= new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn(new Bold('Datum'), 2),
                        new LayoutColumn(new Bold('Vorgang'), 2),
                        new LayoutColumn(new Bold('Primär'), 3),
                        new LayoutColumn(new Bold('weitere'), 2),
                        new LayoutColumn(new Bold('Bemerkung'), 3),
                    ))));
                    $SortDate = false;
                    foreach($tblSupportList as $tblSupport){

                        $SupportDate = $tblSupport->getDate(true);
                        if(!$SortDate && $SupportDate){
                            $SortDate = $SupportDate;
                        } elseif(($SupportDate) && $SortDate < $SupportDate) {
                            $SortDate = $SupportDate;
                        }
                        $SupportPrimary = '';
                        $tblSubjectType = $tblSupport->getTblSupportType();
                        if(($tblSupportFocusPrimary = Student::useService()->getSupportPrimaryFocusBySupport($tblSupport))){
                            $SupportPrimary = $tblSupportFocusPrimary->getTblSupportFocusType()->getName();
                            if($tblSupport->getHasAutism()){
                                $SupportPrimary.= ' inkl. Autismus';
                            }
                        }
                        $SupportSecondary = '';
                        $SupportSecondaryList = array();
                        if(($tblSupportFocusSecondaryList = Student::useService()->getSupportSecondaryFocusBySupport($tblSupport))){
                            foreach($tblSupportFocusSecondaryList as $tblSupportFocusSecondary){
                                $SupportSecondaryList[] = $tblSupportFocusSecondary->getTblSupportFocusType()->getName();
                            }
                            $SupportSecondary = implode(', ', $SupportSecondaryList);
                        }

                        if(($Remark = mb_substr($tblSupport->getRemark(false), 0, 30)) && strlen($Remark) >= 30){
                            $Remark.= '...';
                        }

                        $item['SupportList'].= new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn($tblSupport->getDate(), 2),
                            new LayoutColumn($tblSubjectType->getName(), 2),
                            new LayoutColumn($SupportPrimary, 3),
                            new LayoutColumn($SupportSecondary, 2),
                            new LayoutColumn($Remark, 3),
                        ))));
                    }
                    $item['SupportList'] = ($SortDate ? '<span hidden>'.$SortDate->format('Ymd').'</span>': '').$item['SupportList'];

                    $tableContent[] = $item;
                }
            }
        }
        return $tableContent;
    }

    /**
     * @return array
     */
    public function getSpecialPerson()
    {
        $PersonList = array();;
        // Special auf die letzten 5 Jahre eingrenzen damit Person zur Liste gefügt wird, angezeigt wird dann aber alles
        $Date = new \DateTime('now');
        $Date = $Date->sub(new \DateInterval("P5Y"));
        if(($tblSpecialAll = Student::useService()->getSpecialListByDate($Date))){
            $tblGroupStudent = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT);
            foreach($tblSpecialAll as $tblSpecial){
                if(($tblPerson = $tblSpecial->getServiceTblPerson())){
                    // nur Schüler berücksichtigen
                    if(Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroupStudent)){
                        $PersonList[$tblPerson->getId()] = $tblPerson;
                    }
                }
            }
        }
        return $PersonList;
    }

    /**
     * @param $PersonList
     *
     * @return array
     */
    public function getSpecialTableByPersonList($PersonList)
    {

        $tableContent = array();
        if(!empty($PersonList)){
            foreach($PersonList as $tblPerson){
                $item = array();
                $item['Name'] = $tblPerson->getLastFirstName();
                $item['Course'] = '';
                $item['SchoolType'] = '';
                if(($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))){
                    if((($tblDivision = $tblStudentEducation->getTblDivision()))){
                        $item['Course'] = $tblDivision->getDisplayName();
                    }
                    if((($tblCoreGroup = $tblStudentEducation->getTblCoreGroup()))){
                        if($item['Course']){
                            $item['Course'] .= new Container($tblCoreGroup->getDisplayName());
                        } else {
                            $item['Course'] = $tblCoreGroup->getDisplayName();
                        }
                    }
                    if(($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())){
                        $item['SchoolType'] = $tblSchoolType->getName();
                    }
                }
                $item['SpecialList'] = '';
                $item['Option'] = (new Standard(
                    '', '/Education/Integration/Selected', new Commodity(),
                    array(
                        'PersonId'   => $tblPerson->getId(),
                        'Open'   => 2
                    ),
                    'Inklusion des Schülers verwalten'
                ));
                if(($tblSpecialList = Student::useService()->getSpecialByPerson($tblPerson))){
                    // ToDO List als einzelne Variablen evtl. als Array speichern für eventuellen Excel
                    $item['SpecialList'].= new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn(new Bold('Datum'), 2),
                        new LayoutColumn(new Bold('Entw. Besonderheit'), 3),
                        new LayoutColumn(new Bold('Bemerkung'), 7),
                    ))));
                    $SortDate = false;
                    foreach($tblSpecialList as $tblSpecial){
                        $DisorderTypeList = array();
                        if(($tblSpecialDisorderList = Student::useService()->getSpecialDisorderAllBySpecial($tblSpecial))){
                            foreach($tblSpecialDisorderList as $tblSpecialDisorder){
                                $DisorderTypeList[] = $tblSpecialDisorder->getTblSpecialDisorderType()->getName();
                            }
                        }
                        $SpecialDate = $tblSpecial->getDate(true);
                        if(!$SortDate && $SpecialDate){
                            $SortDate = $SpecialDate;
                        } elseif(($SpecialDate) && $SortDate < $SpecialDate) {
                            $SortDate = $SpecialDate;
                        }
                        if(($Remark = mb_substr($tblSpecial->getRemark(false), 0, 100)) && strlen($Remark) >= 100){
                            $Remark.= '...';
                        }
                        $item['SpecialList'].= new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn($tblSpecial->getDate(), 2),
                            new LayoutColumn(implode(', ', $DisorderTypeList), 3),
                            new LayoutColumn($Remark, 7),
                        ))));
                    }

                    $item['SpecialList'] = ($SortDate ? '<span hidden>'.$SortDate->format('Ymd').'</span>': '').$item['SpecialList'];

                    // nur mit Special aufnehmen
                    $tableContent[] = $item;
                }
            }
        }
        return $tableContent;
    }

    /**
     * @return array
     */
    public function getHandyCapPerson()
    {
        $PersonList = array();;
        // HandyCap auf die letzten 5 Jahre eingrenzen damit Person zur Liste gefügt wird, angezeigt wird dann aber alles
        $Date = new \DateTime('now');
        // 5 Jahre kann später sicher auf 2 reduziert werden da dies eigentlich jedes jahr gepflegt werden muss -> performance boost
        $Date = $Date->sub(new \DateInterval("P5Y"));
        if(($tblHandyCapList = Student::useService()->getHandyCapListByDate($Date))){
            $tblGroupStudent = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT);
            foreach($tblHandyCapList as $tblHandyCap){
                if(($tblPerson = $tblHandyCap->getServiceTblPerson())){
                    // nur Schüler berücksichtigen
                    if(Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroupStudent)){
                        $PersonList[$tblPerson->getId()] = $tblPerson;
                    }
                }
            }
        }
        return $PersonList;
    }

    /**
     * @param $PersonList
     *
     * @return array
     */
    public function getHandyCapTableByPersonList($PersonList)
    {

        $tableContent = array();

        if(!empty($PersonList)){
            foreach($PersonList as $tblPerson){
                $item = array();
                $item['Name'] = $tblPerson->getLastFirstName();
                $item['Course'] = '';
                $item['SchoolType'] = '';
                if(($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))){
                    if((($tblDivision = $tblStudentEducation->getTblDivision()))){
                        $item['Course'] = $tblDivision->getDisplayName();
                    }
                    if((($tblCoreGroup = $tblStudentEducation->getTblCoreGroup()))){
                        if($item['Course']){
                            $item['Course'] .= new Container($tblCoreGroup->getDisplayName());
                        } else {
                            $item['Course'] = $tblCoreGroup->getDisplayName();
                        }
                    }
                    if(($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())){
                        $item['SchoolType'] = $tblSchoolType->getName();
                    }
                }
                $item['HandyCapList'] = '';
                $item['Option'] = (new Standard(
                    '', '/Education/Integration/Selected', new Commodity(),
                    array(
                        'PersonId'   => $tblPerson->getId(),
                        'Open'   => 3
                    ),
                    'Inklusion des Schülers verwalten'
                ));
                if(($tblHandyCapList = Student::useService()->getHandyCapByPerson($tblPerson))){
                    // ToDO List als einzelne Variablen evtl. als Array speichern für eventuellen Excel
                    $item = $this->layoutHandyCap($item, $tblHandyCapList);
                    // nur mit HandyCap aufnehmen
                    $tableContent[] = $item;
                }
            }
        }
        return $tableContent;
    }

    private function layoutHandyCap($item, $tblHandyCapList)
    {

        $item['HandyCapList'].= new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(new Bold('Datum'), 2),
//                        new LayoutColumn(new Bold('Recht.&nbsp;Grundlage'), 2),
//                        new LayoutColumn(new Bold('Lernziel'), 2),
            new LayoutColumn(new Bold('Unterricht'), 4),
            new LayoutColumn(new Bold('Leistungsbewertungen'), 4),
            new LayoutColumn(new Bold('Zeugnisvorbereitung'), 2),
//                        new LayoutColumn('', 2),
        ))));
        $SortDate = false;
        foreach($tblHandyCapList as $tblHandyCap){
            $HandyCapDate = $tblHandyCap->getDate(true);
            if(!$SortDate && $HandyCapDate){
                $SortDate = $HandyCapDate;
            } elseif(($HandyCapDate) && $SortDate < $HandyCapDate) {
                $SortDate = $HandyCapDate;
            }

            if(($RemarkLesson = mb_substr($tblHandyCap->getRemarkLesson(false), 0, 50)) && strlen($RemarkLesson) >= 50){
                $RemarkLesson.= '...';
            }
            if(($RemarkRating = mb_substr($tblHandyCap->getRemarkRating(false), 0, 50)) && strlen($RemarkRating) >= 50){
                $RemarkRating.= '...';
            }
            if(($RemarkCertificate = mb_substr($tblHandyCap->getRemarkCertificate(false), 0, 19)) && strlen($RemarkCertificate) >= 19){
                $RemarkCertificate.= '...';
            }

            $item['HandyCapList'].= new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn($tblHandyCap->getDate(), 2),
//                            new LayoutColumn($tblHandyCap->getLegalBasis(), 2),
//                            new LayoutColumn($tblHandyCap->getLearnTarget(), 2),
                new LayoutColumn($RemarkLesson, 4),
                new LayoutColumn($RemarkRating, 4),
                new LayoutColumn($RemarkCertificate, 2),
//                            new LayoutColumn(($tblHandyCap->isCanceled()?'Aufgehoben': ''), 2),
            ))));
        }
        $item['HandyCapList'] = ($SortDate ? '<span hidden>'.$SortDate->format('Ymd').'</span>': '').$item['HandyCapList'];

        return $item;
    }
}