<?php
namespace SPHERE\Application\Reporting\Custom\Kreuzgymnasium\Person;

use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\Reporting\Standard\Person\Person as PersonStandard;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
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
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info as InfoText;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Custom\Kreuzgymnasium\Person
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param int|null $DivisionCourseId
     * @param null     $All
     *
     * @return Stage
     */
    public function frontendSignList(?int $DivisionCourseId = null, $All = null): Stage
    {

        $Stage = new Stage('Unterschriften Liste', '');
        $Route = '/Reporting/Custom/Kreuzgymnasium/Person/SignList';
        if($DivisionCourseId === null) {
            if($All) {
                $Stage->addButton(new Standard('aktuelles Schuljahr', $Route));
                $Stage->addButton(new Standard(new InfoText(new Bold('Alle Schuljahre')), $Route, null, array('All' => 1)));
            } else {
                $Stage->addButton(new Standard(new InfoText(new Bold('aktuelles Schuljahr')), $Route));
                $Stage->addButton(new Standard('Alle Schuljahre', $Route, null, array('All' => 1)));
            }
            $Stage->setContent(PersonStandard::useFrontend()->getChooseDivisionCourse($Route, $All));
            return $Stage;
        }
        $Stage->addButton(new Standard('Zurück', $Route, new ChevronLeft()));
        if(!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return $Stage->setContent(new Warning('Klasse nicht verfügbar.'));
        }
        if(!($tblPersonList = $tblDivisionCourse->getStudents())) {
            return $Stage->setContent(new Warning('Keine Schüler hinterlegt.'));
        }
        $TableContent = Person::useService()->createSignList($tblDivisionCourse);
        if(!empty($TableContent)) {
            $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Kreuzgymnasium/Common/SignList/Download', new Download(),
                    array('DivisionCourseId' => $tblDivisionCourse->getId()))
            );$Stage->addButton(new Primary('Herunterladen Querformat', '/Api/Reporting/Custom/Kreuzgymnasium/Common/SignList/Download', new Download(),
                    array('DivisionCourseId' => $tblDivisionCourse->getId(), 'isLandscape' => true))
            );
            $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }
        $Stage->setContent(
            new Layout(array(
                PersonStandard::useFrontend()->getDivisionHeadOverview($tblDivisionCourse),
                new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new TableData($TableContent, null,
                        array(
                            'Count'        => '#',
                            'LastName'  => 'Name',
                            'FirstName' => 'Vorname',
                        ),
                        array(
                            'columnDefs' => array(
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                            ),
                            "pageLength" => -1,
                            "responsive" => false
                        )
                    )
                , 6))),
                PersonStandard::useFrontend()->getGenderLayoutGroup($tblPersonList)
            ))
        );
        return $Stage;
    }

    public function frontendStudentCount()
    {

        $Stage = new Stage('Schülerzahlen', 'aktuelle Statistik '.(new \DateTime())->format('d.m.Y'));
        if(!$tblYearList = Term::useService()->getYearByNow()){
            return $Stage->setContent(new Warning('Kein aktuelles Jahr vorhanden'));
        }
        $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Kreuzgymnasium/Common/StudentCount/Download', new Download()));
        list($DivisionCourseSek1List, $DivisionCourseSek2List, $DivisionCourseDAZList, $DivisionCourseSiAList) =
            Person::useService()->getStudentCountDivisionList($tblYearList);

        // Zählung
        $tblGroup = Group::useService()->getGroupByName('Kruzianer');
        $TableSek1Content = Person::useService()->createStudentCount($DivisionCourseSek1List, 'Sek1', $tblGroup);
        $TableSek2Content = Person::useService()->createStudentCount($DivisionCourseSek2List, 'Sek2', $tblGroup);
        $TableDAZContent = Person::useService()->createStudentCount($DivisionCourseDAZList, 'DAZ', $tblGroup);
        $TableSiAContent = Person::useService()->createStudentCount($DivisionCourseSiAList, 'SiA', $tblGroup);

        $ContentAll = array();
        $ContentAll = array_merge($ContentAll, $TableSek1Content);
        $ContentAll = array_merge($ContentAll, $TableSek2Content);
        $ContentAll = array_merge($ContentAll, $TableDAZContent);

        $PanelHead = new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn('', 2),
            new LayoutColumn('Jungen:', 2),
            new LayoutColumn('Jungen Kruzianer:', 2),
            new LayoutColumn('Jungen Gesamt:', 2),
            new LayoutColumn('Mädchen:', 2),
            new LayoutColumn('Gesamt:', 2)
        ))));

        $LayoutCountAll = $this->getCountLayout($ContentAll, 'Alle Schüler');
        $LayoutCountSek1 = $this->getCountLayout($TableSek1Content, 'Sekundarstufe I');
        $LayoutCountSek2 = $this->getCountLayout($TableSek2Content, 'Sekundarstufe II');
        $LayoutCountDAZ = $this->getCountLayout($TableDAZContent, 'Deutsch als Zweitsprache');
        $LayoutCountSiA = $this->getCountLayout($TableSiAContent, 'Schüler im Ausland (nicht Summiert)');

        $TableHeadList = array(
            'Name' => 'Klassen',
            'Teacher' => 'Klassenleiter|n',
            'MaleNoKruzianerCount' => 'Jungen',
            'MaleKruzianerCount' => 'Jungen Kruzianer',
            'MaleCount' => 'Jungen Gesamt',
            'FemaleCount' => 'Mädchen Gesamt',
            'Count' => 'Gesamt',
        );
        $TableHeadListSek2 = $TableHeadList;
        $TableHeadListSek2['Count'] = 'Gesamt&nbsp;';
        $TableHeadListDAZ = $TableHeadList;
        $TableHeadListDAZ['Count'] = 'Gesamt&nbsp;&nbsp;';
        $TableHeadListSiA = $TableHeadList;
        $TableHeadListSiA['Count'] = 'Gesamt&nbsp;&nbsp;&nbsp;';

        $TableInteractive = array(
            "paging" => false, // Deaktivieren Blättern
            "iDisplayLength" => -1,    // Alle Einträge zeigen
            "searching" => false, // Deaktivieren Suchen
            "info" => false,  // Deaktivieren Such-Info
            "sort" => false,
            "responsive" => false,
            'columnDefs' => array(
//            array('width' => '10%', 'targets' => 0),
            array('width' => '10%', 'targets' => array(0,2,3,4,5,6)),
        ),
    );

        $Stage->setContent(
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(new Panel($PanelHead, array(
                        $LayoutCountSek1, $LayoutCountSek2, $LayoutCountDAZ, new Bold($LayoutCountAll), $LayoutCountSiA
                    ), Panel::PANEL_TYPE_INFO)),
                    new LayoutColumn(new Title('Sekundarstufe I '.new Small(new Muted('5-10')))),
                    new LayoutColumn(new TableData($TableSek1Content, null, $TableHeadList, $TableInteractive)),
                    new LayoutColumn(new Title('Sekundarstufe II '.new Small(new Muted('11-12')))),
                    new LayoutColumn(new TableData($TableSek2Content, null, $TableHeadListSek2, $TableInteractive)),
                    new LayoutColumn(new Title('Deutsch als Zweitsprache '.new Small(new Muted('DAZ')))),
                    new LayoutColumn(new TableData($TableDAZContent, null, $TableHeadListDAZ, $TableInteractive)),
                    new LayoutColumn(new Title('Schüler im Ausland '.new Small(new Muted('SiA')))),
                    new LayoutColumn(new TableData($TableSiAContent, null, $TableHeadListSiA, $TableInteractive)),
                ))
            )))
        );

        return $Stage;
    }

    private function getCountLayout($Content, $Typ)
    {
        $MaleNoKruzianerCount = $MaleKruzianerCount = $MaleCount = $FemaleCount = $Count = 0;
        if(!empty($Content)){
            foreach($Content as $Row){
                $MaleNoKruzianerCount += $Row['MaleNoKruzianerCount'];
                $MaleKruzianerCount += $Row['MaleKruzianerCount'];
                $MaleCount += $Row['MaleCount'];
                $FemaleCount += $Row['FemaleCount'];
                $Count += $Row['Count'];
            }
        }

        $Warnung = '';
        if($MaleCount + $FemaleCount != $Count){
            $Unmatch = $Count - ($MaleCount + $FemaleCount);
            $Warnung = ' '.new WarningText(new ToolTip(new Exclamation(), $Unmatch.' Schüler '.($Unmatch > 1? 'haben':'hat').' kein männliches oder weibliches Geschlecht'));
        }
        return new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn($Typ, 2),
                new LayoutColumn($MaleNoKruzianerCount, 2),
                new LayoutColumn($MaleKruzianerCount, 2),
                new LayoutColumn($MaleCount, 2),
                new LayoutColumn($FemaleCount, 2),
                new LayoutColumn($Count.$Warnung, 2)
            )))
        );
    }
}