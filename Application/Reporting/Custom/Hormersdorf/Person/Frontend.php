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
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
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
     * @param $Select
     *
     * @return Stage
     */
    public function frontendClassList($DivisionId = null, $Select = null)
    {

        $View = new Stage();
        $View->setTitle('Auswertung');
        $View->setDescription('Klassenliste');

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

        $View->setContent(
            new Well(
                Person::useService()->getClass(
                    new Form(new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(
                                new SelectBox('Select[Division]', 'Klasse', array(
                                    '{{ serviceTblYear.Name }} - {{ tblLevel.serviceTblType.Name }} - {{ tblLevel.Name }}{{ Name }}' => $tblDivisionAll
                                )), 12
                            )
                        )),
                    )), new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Auswählen', new Select()))
                    , $Select, '/Reporting/Custom/Hormersdorf/Person/ClassList')
            )
            .
            ($DivisionId !== null ?
                (new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Klasse:', $tblDivision->getDisplayName(),
                            Panel::PANEL_TYPE_INFO), 12
                    ),
                )))))
                . $tableData
                : '')
        );

        return $View;
    }

}