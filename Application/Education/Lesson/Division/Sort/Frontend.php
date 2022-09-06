<?php

namespace SPHERE\Application\Education\Lesson\Division\Sort;

use SPHERE\Application\Api\Education\ClassRegister\ApiSortDivision;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\ResizeVertical;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Sorter\StringGermanOrderSorter;

class Frontend
{
    /**
     * @param null $DivisionId
     *
     * @return string
     */
    public function frontendSortDivision($DivisionId = null)
    {
        $Stage = new Stage('Klasse', 'Schüler sortieren');
        if ($tblDivision = Division::useService()->getDivisionById($DivisionId)) {
            $studentTable = array();
            if ($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision)) {
                foreach ($tblStudentList as $tblPerson) {
                    $tblAddress = $tblPerson->fetchMainAddress();
                    $birthday = '';
                    $Gender = '';
                    if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                        if ($tblCommon->getTblCommonBirthDates()) {
                            $birthday = $tblCommon->getTblCommonBirthDates()->getBirthday();
                            if ($tblGender = $tblCommon->getTblCommonBirthDates()->getTblCommonGender()) {
                                $Gender = $tblGender->getShortName();
                            }
                        }
                    }

                    $studentTable[] = array(
                        'Number'        => (count($studentTable) + 1),
                        'Name'          =>
                            new PullClear(
                                new PullLeft(new ResizeVertical().' '.$tblPerson->getLastFirstName())
                            ),
                        'Gender'        => $Gender,
                        'Address'       => $tblAddress ? $tblAddress->getGuiString() : '',
                        'Birthday'      => $birthday,
                    );
                }
            }

            $buttonList[] = new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft());
            $buttonList[] = (new Standard(
                'Sortierung alphabetisch', ApiSortDivision::getEndpoint(), new ResizeVertical()))
                ->ajaxPipelineOnClick(ApiSortDivision::pipelineOpenSortModal($tblDivision->getId(), 'Sortierung alphabetisch'));
            $buttonList[] = (new Standard(
                'Sortierung Geschlecht (alphabetisch)', ApiSortDivision::getEndpoint(), new ResizeVertical()))
                ->ajaxPipelineOnClick(ApiSortDivision::pipelineOpenSortModal($tblDivision->getId(), 'Sortierung Geschlecht (alphabetisch)'));

            $YearString = new Muted('-NA-');
            if (( $tblYear = $tblDivision->getServiceTblYear() )) {
                $YearString = $tblYear->getName();
            }

            $Stage->setContent(
                ApiSortDivision::receiverModal()
                . new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $this->getDivisionString($tblDivision),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Schuljahr',
                                    $YearString,
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            ($inActivePanel = \SPHERE\Application\Reporting\Standard\Person\Person::useFrontend()->getInActiveStudentPanel($tblDivision))
                                ? new LayoutColumn($inActivePanel)
                                : null,
                            new LayoutColumn($buttonList),
                            new LayoutColumn(array(
                                new TableData($studentTable, null, array(
                                    'Number'        => '#',
                                    'Name'          => 'Name',
                                    'Gender'        => 'Ge&shy;schlecht',
                                    'Birthday'      => 'Geburts&shy;datum',
                                    'Address'       => 'Adresse'
                                ),
                                    array(
                                        'rowReorderColumn' => 1,
                                        'ExtensionRowReorder' => array(
                                            'Enabled' => true,
                                            'Url'     => '/Api/Education/ClassRegister/Reorder',
                                            'Data'    => array('DivisionId' => $tblDivision->getId()
                                            )
                                        ),
                                        'paging' => false,
                                        'columnDefs' => array(
                                            array('type'  => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                            array('width' => '40%', 'targets' => 4),
                                            array('orderable' => false, 'width' => '60px', 'targets' => -1),
                                        ),
                                        'responsive' => false
                                    )
                                )
                            ))
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger('Klasse nicht gefunden.', new Ban());
        }
    }


    /**
     * @param null $DivisionId
     *
     * @return string
     */
    public function frontendSortDivisionAlphabetically($DivisionId = null)
    {

        $Stage = new Stage('Klassenbuch', 'Schüler sortieren');

        if (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
            if (Division::useService()->sortDivisionStudentByProperty($tblDivision, 'LastFirstName', new StringGermanOrderSorter())) {
                return $Stage . new Success(
                        'Die Schüler der Klasse wurden erfolgreich sortiert.',
                        new \SPHERE\Common\Frontend\Icon\Repository\Success()
                    )
                    . new Redirect('/Education/Lesson/Division/Sort', Redirect::TIMEOUT_SUCCESS,
                        array(
                            'DivisionId' => $tblDivision->getId()
                        )
                    );
            } else {
                return $Stage . new Danger(
                        'Die Schüler der Klasse konnten nicht sortiert werden.',
                        new Exclamation()
                    )
                    . new Redirect('/Education/Lesson/Division/Sort', Redirect::TIMEOUT_ERROR,
                        array(
                            'DivisionId' => $tblDivision->getId()
                        )
                    );
            }
        } else {

            return $Stage
                .new Danger('Klassen nicht vorhanden.', new Ban())
                .new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param null $DivisionId
     *
     * @return string
     */
    public function frontendSortDivisionGender($DivisionId = null)
    {

        $Stage = new Stage('Klassenbuch', 'Schüler sortieren');

        if (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
            if (Division::useService()->sortDivisionStudentWithGenderByProperty($tblDivision, 'LastFirstName',
                new StringGermanOrderSorter())
            ) {
                return $Stage.new Success(
                        'Die Schüler der Klasse wurden erfolgreich sortiert.',
                        new \SPHERE\Common\Frontend\Icon\Repository\Success()
                    )
                    .new Redirect('/Education/Lesson/Division/Sort', Redirect::TIMEOUT_SUCCESS,
                        array(
                            'DivisionId' => $tblDivision->getId()
                        )
                    );
            } else {
                return $Stage.new Danger(
                        'Die Schüler der Klasse konnten nicht sortiert werden.',
                        new Exclamation()
                    )
                    .new Redirect('/Education/Lesson/Division/Sort', Redirect::TIMEOUT_ERROR,
                        array(
                            'DivisionId' => $tblDivision->getId()
                        )
                    );
            }
        } else {

            return $Stage
                .new Danger('Klassen nicht vorhanden.', new Ban())
                .new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return string
     */
    private function getDivisionString(TblDivision $tblDivision)
    {

        //Standard
        $DivisionString = $tblDivision->getDisplayName();

        if(($tblDivisionTeacherList = Division::useService()->getDivisionTeacherAllByDivision($tblDivision))){
            $TeacherArray = array();
            foreach($tblDivisionTeacherList as $tblDivisionTeacher){
                if($tblPerson = $tblDivisionTeacher->getServiceTblPerson()){
                    $TeacherArray[] = $tblPerson->getFullName()
                        . (($description = $tblDivisionTeacher->getDescription())
                            ? ' ' . new Muted($description): '');
                }
            }
            if(!empty($TeacherArray)){
                $DivisionString .= ' (Klassenlehrer: '.implode(', ', $TeacherArray).')';
            }
        }
        return $DivisionString;
    }
}