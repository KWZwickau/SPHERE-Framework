<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Frontend;

use SPHERE\Application\Api\Education\DivisionCourse\ApiSubjectTable;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Window\Stage;

class FrontendSubjectTable extends FrontendStudent
{
    /**
     * @param $SchoolTypeId
     *
     * @return Stage
     */
    public function frontendSubjectTable($SchoolTypeId = null): Stage
    {
        $stage = new Stage('Stundentafel', 'Übersicht');
        if (($tblSchoolTypeList = School::useService()->getConsumerSchoolTypeAll())) {
            foreach ($tblSchoolTypeList as $tblSchoolType) {
                if ($tblSchoolType->getId() == $SchoolTypeId) {
                    $stage->addButton(new Standard(new Info(new Bold($tblSchoolType->getName())), '/Education/Lesson/SubjectTable', new Edit(), array('SchoolTypeId' => $tblSchoolType->getId())));
                } else {
                    $stage->addButton(new Standard($tblSchoolType->getName(), '/Education/Lesson/SubjectTable', null, array('SchoolTypeId' => $tblSchoolType->getId())));
                }
            }
        }

        $stage->setContent(
            ApiSubjectTable::receiverBlock($this->loadSubjectTableContent($SchoolTypeId), 'SubjectTableContent')
        );

        return $stage;
    }

    /**
     * @param $SchoolTypeId
     *
     * @return string
     */
    public function loadSubjectTableContent($SchoolTypeId): string
    {
        if ($SchoolTypeId === null) {
            return new Warning('Bitte wählen Sie zunächst eine Schulart aus.');
        }

        if (($tblSchoolType = Type::useService()->getTypeById($SchoolTypeId))) {
            if (($tblSubjectTableList = DivisionCourse::useService()->getSubjectTableListBy($tblSchoolType))) {
                $dataList = array();
                $levelList = array();
                foreach ($tblSubjectTableList as $tblSubjectTable) {
                    $subjectId = $tblSubjectTable->getSubjectId();
                    $levelList[$tblSubjectTable->getLevel()] = $tblSubjectTable->getLevel();
                    $dataList[$tblSubjectTable->getTypeName()][$tblSubjectTable->getRanking()][$subjectId]['Name'] = $tblSubjectTable->getSubjectName();
                    $dataList[$tblSubjectTable->getTypeName()][$tblSubjectTable->getRanking()][$subjectId]['Levels'][$tblSubjectTable->getLevel()] = $tblSubjectTable->getHoursPerWeek();
                }

                if ($levelList) {
                    $countLevel = count($levelList);
                    $widthLevel = $countLevel < 5 ? 2 : 1;
                    $widthSubject = 12 - $countLevel * $widthLevel;

                    $titleColumns[] = new LayoutColumn(new Bold('Klassenstufe'), $widthSubject);
                    foreach ($levelList as $item) {
                        $titleColumns[] = new LayoutColumn(new Bold($item), $widthLevel);
                    }

                    $content = new Title(new Layout(new LayoutGroup(new LayoutRow($titleColumns))));
                    $content .= $this->setContentByTypeName('Pflichtbereich', $dataList, $levelList, $widthSubject, $widthLevel);
                    // todo verknüpfung anzeigen
                    $content .= $this->setContentByTypeName('Wahlpflichtbereich', $dataList, $levelList, $widthSubject, $widthLevel);
                    $content .= $this->setContentByTypeName('Wahlbereich', $dataList, $levelList, $widthSubject, $widthLevel);

                    return $content;
                }
            }
        } else {
            return new Danger('Schulart nicht gefunden', new Exclamation());
        }

        return '';
    }

    /**
     * @param $typeName
     * @param $dataList
     * @param $levelList
     * @param $widthSubject
     * @param $widthLevel
     *
     * @return string
     */
    private function setContentByTypeName($typeName, $dataList, $levelList, $widthSubject, $widthLevel): string
    {
        $content = '';
        if (isset($dataList[$typeName])) {
            $content .= new Title(new Bold($typeName));
            ksort($dataList[$typeName]);
            foreach ($dataList[$typeName] as $rankingList) {
                foreach ($rankingList as $list) {
                    $columns = array();
                    $columns[] = new LayoutColumn($list['Name'], $widthSubject);
                    foreach ($levelList as $level) {
                        $columns[] = new LayoutColumn(isset($list['Levels'][$level]) ? $list['Levels'][$level] : '-', $widthLevel);
                    }

                    $content .= new Layout(new LayoutGroup(new LayoutRow($columns)));
                }
            }
        }

        return $content;
    }
}