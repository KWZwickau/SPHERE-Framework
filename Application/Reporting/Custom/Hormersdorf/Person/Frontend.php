<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 25.01.2016
 * Time: 15:46
 */

namespace SPHERE\Application\Reporting\Custom\Hormersdorf\Person;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
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
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Custom\Hormersdorf\Person
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendPerson()
    {

        $View = new Stage();
        $View->setTitle('Auswertung');
        $View->setDescription('Bitte wählen Sie eine Liste zur Auswertung');

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendStaffList()
    {

        $View = new Stage();
        $View->setTitle('Auswertung');
        $View->setDescription('Liste der Mitarbeiter (Geburtstage)');

        $staffList = Person::useService()->createStaffList();
        if ($staffList) {
            $View->addButton(
                new Primary('Herunterladen',
                    '/Api/Reporting/Custom/Hormersdorf/Person/StaffList/Download', new Download())
            );

            $View->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }

        $View->setContent(
            new TableData($staffList, null,
                array(
                    'Name' => 'Name',
                    'Birthday'  => 'Geburtstag',
                ),
                null
            )
        );

        return $View;
    }

    /**
     * @param $DivisionId
     *
     * @return Stage
     */
    public function frontendClassList($DivisionId = null)
    {

        $View = new Stage('Auswertung', 'Klassenliste');
        if (null !== $DivisionId) {
            $View->addButton(new Standard('Zurück', '/Reporting/Custom/Hormersdorf/Person/ClassList', new ChevronLeft()));
        }
        $tblDivisionAll = Division::useService()->getDivisionAll();
        $tblDivision = new TblDivision();
        $studentList = array();

        if ($DivisionId !== null) {

            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Select']['Division'] = $DivisionId;
                $Global->savePost();
            }

            $tblDivision = Division::useService()->getDivisionById($DivisionId);
            if ($tblDivision) {
                $studentList = Person::useService()->createClassList($tblDivision);
                if ($studentList) {
                    $View->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Custom/Hormersdorf/Person/ClassList/Download',
                            new Download(),
                            array('DivisionId' => $tblDivision->getId()))
                    );

                    $View->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
                }
            }
        }

        $tableData = ($tableData = new TableData($studentList, null,
            array(
                'DisplayName' => 'Name',
                'Birthday' => 'Geb.-Datum',
                'Address' => 'Adresse',
                'PhoneNumbers' => 'Telefonnummer',
            ),
            null
        ));

        $TableContent = array();
        if ($tblDivisionAll) {
            array_walk($tblDivisionAll, function (TblDivision $tblDivision) use (&$TableContent) {

                $Item['Year'] = '';
                $Item['Division'] = $tblDivision->getDisplayName();
                $Item['Type'] = $tblDivision->getTypeName();
                if ($tblDivision->getServiceTblYear()) {
                    $Item['Year'] = $tblDivision->getServiceTblYear()->getDisplayName();
                }
                $Item['Option'] = new Standard('', '/Reporting/Custom/Hormersdorf/Person/ClassList', new EyeOpen(),
                    array('DivisionId' => $tblDivision->getId()));
                $Item['Count'] = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
                array_push($TableContent, $Item);
            });
        }

        $View->setContent(
            ( $DivisionId === null ?
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($TableContent, null,
                                    array(
                                        'Year'     => 'Jahr',
                                        'Division' => 'Klasse',
                                        'Type'     => 'Schulart',
                                        'Count'    => 'Schüler',
                                        'Option'   => '',))
                                , 12)
                        ), new Title(new Listing().' Übersicht')
                    )
                ) : '' )
            .( $DivisionId !== null ?
                (new Layout(new LayoutGroup(new LayoutRow(array(
                    ( $tblDivision->getServiceTblYear() ?
                        new LayoutColumn(
                            new Panel('Jahr', $tblDivision->getServiceTblYear()->getDisplayName(),
                                Panel::PANEL_TYPE_SUCCESS), 4
                        ) : '' ),
                    new LayoutColumn(
                        new Panel('Klasse', $tblDivision->getDisplayName(),
                            Panel::PANEL_TYPE_SUCCESS), 4
                    ),
                    ( $tblDivision->getTypeName() ?
                        new LayoutColumn(
                            new Panel('Schulart', $tblDivision->getTypeName(),
                                Panel::PANEL_TYPE_SUCCESS), 4
                        ) : '' ),
                )))))
                . $tableData
                : '')
        );

        return $View;
    }

}