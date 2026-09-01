<?php
namespace SPHERE\Application\Education\Integration;

use SPHERE\Application\Api\People\Meta\Integration\ApiIntegration;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Commodity;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Debugger;

class Frontend extends Extension
{

    const SUPPORT = 1; // Förderbereich
    const SPECIAL = 2; // Entwicklungsbesonderheit
    const HANDYCAP = 3; // Nachteilsausgleich

    public function frontendSelectPerson($FilterSelect = '')
    {
        $Stage = new Stage('Inklusion', 'Übersicht Schüler');

        $_POST['FilterSelect'] = $FilterSelect;

        $ReceiverFilter = ApiIntegration::receiverFilter((new Well(new Center($this->getFilterChange())))->setPadding('5px')->setMarginBottom('5px'));

        $ReceiverContent = new Warning('Bitte wählen Sie einen Filter aus');
        if($FilterSelect){
            if($FilterSelect == 1){
                $ReceiverContent = ApiIntegration::pipelineLoadSupport();
            } elseif($FilterSelect == 2){
                $ReceiverContent = ApiIntegration::pipelineLoadSpecial();
            } elseif ($FilterSelect == 3){
                $ReceiverContent = ApiIntegration::pipelineLoadHandyCap();
            }
        }

        $ReceiverTable = ApiIntegration::receiverTable($ReceiverContent);

        $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn($ReceiverFilter, 6),
            new LayoutColumn($ReceiverTable),
//            new LayoutColumn(new TableData($tableContent, null, array('Name' => 'Name', 'HandyCapList' => 'Zusammenfassung', 'Option' => '')))
        )))));

        return $Stage;
    }

    /**
     * @return Form
     */
    public function getFilterChange()
    {

        return new Form(new FormGroup(new FormRow(
            array(
                new FormColumn((new RadioBox('FilterSelect', 'Förderverlauf', self::SUPPORT))->ajaxPipelineOnChange(ApiIntegration::pipelineLoadSupport()), 4),
                new FormColumn((new RadioBox('FilterSelect', 'Entwicklungsbesonderheit', self::SPECIAL))->ajaxPipelineOnChange(ApiIntegration::pipelineLoadSpecial()), 4),
                new FormColumn((new RadioBox('FilterSelect', 'Nachteilsausgleich', self::HANDYCAP))->ajaxPipelineOnChange(ApiIntegration::pipelineLoadHandyCap()), 4),
            )
        )));
    }

    /**
     * @return string
     */
    public function getTableSupport()
    {

        $PersonList = Integration::useService()->getSupportPerson();

        $tableContent = Integration::useService()->getSupportTableByPersonList($PersonList);

        return new Title('Förderverlauf').
            new TableData($tableContent, null,
                array(
                    'Name' => 'Name',
                    'Course' => 'Kurs',
                    'SchoolType' => 'Schulart',
                    'SupportList' => 'Zusammenfassung',
                    'Option' => '')
                , array(
                    'columnDefs' => array(
                        array('type' => 'natural', 'targets' => 1),
                        array('orderable' => false, 'targets' => -1),
                        array('width' => '30px', 'targets' => -1),
                        array("searchable" => false, "targets" => -1),
                    ),
                    'order' => array(array(1, 'asc'), array(2, 'desc')),
//                    'pageLength' => -1,
//                    'paging'     => false,
//                    'info'       => false,
//                    'searching'  => false,
//                    'responsive' => false
                )
            );
    }

    /**
     * @return string
     */
    public function getTableSpecial()
    {

        $PersonList = Integration::useService()->getSpecialPerson();

        $tableContent = Integration::useService()->getSpecialTableByPersonList($PersonList);

//        return new Title(new PullClear('Entwicklungsbesonderheit'.new PullRight('<div style="margin-top: -13px">'.new Standard('Test', '', new Download()).'</div>')))
        return new Title('Entwicklungsbesonderheit')
            .new TableData($tableContent, null,
            array(
                'Name' => 'Name',
                'Course' => 'Kurs',
                'SchoolType' => 'Schulart',
                'SpecialList' => 'Zusammenfassung',
                'Option' => '')
            , array(
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 1),
//                    array('type' => 'de_date', 'targets' => -2),
                    array('orderable' => false, 'targets' => array(-1)),
                    array('width' => '30px', 'targets' => -1),
                    array("searchable" => false, "targets" => -1),
                ),
                'order' => array(array(1, 'asc'), array(2, 'desc')),
//                    'pageLength' => -1,
//                    'paging'     => false,
//                    'info'       => false,
//                    'searching'  => false,
//                    'responsive' => false
            )
        );
    }

    /**
     * @return string
     */
    public function getTableHandyCap()
    {
        $PersonList = Integration::useService()->getHandyCapPerson();
        $tableContent = Integration::useService()->getHandyCapTableByPersonList($PersonList);

        return new Title('Nachteilsausgleich').
            new TableData($tableContent, null,
            array(
                'Name' => 'Name',
                'Course' => 'Kurs',
                'SchoolType' => 'Schulart',
                'HandyCapList' => 'Zusammenfassung',
                'Option' => '')
            , array(
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 1),
                    array('orderable' => false, 'targets' => -1),
                    array('width' => '30px', 'targets' => -1),
                    array("searchable" => false, "targets" => -1),
                ),
                'order' => array(array(1, 'asc'), array(2, 'desc')),
//                    'pageLength' => -1,
//                    'paging'     => false,
//                    'info'       => false,
//                    'searching'  => false,
//                    'responsive' => false
            )
        );
    }

    /**
     * @return Form
     */
    public function getFilterNachteilsausgleich()
    {

        //ToDO Wonach macht es sinn zu filtern?
        // - nur Schüler schränke ich initial bereits ein
        // - übersicht mit zusätzlicher Schulart lässt schon alles in der DataTable filtern
        // - erstmal weg lassen
        return new Form(new FormGroup(new FormRow(array(
            new FormColumn('', 3),
            new FormColumn('', 3),
            new FormColumn('', 3),
            new FormColumn('', 3),
        ))));
    }

    /**
     * @param $PersonId
     * @param $Open
     *
     * @return Stage
     */
    public function frontendIntegration($PersonId = null, $Open = 1)
    {
        $Stage = new Stage('Inklusion', 'Schüler bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Integration', new ChevronLeft(), array('FilterSelect' => $Open)));

        if (($tblPerson = Person::useService()->getPersonById($PersonId))
        ) {
            $Content = new Title($tblPerson->getFullName());
            $Content .= (new Well(Student::useFrontend()->frontendIntegration($tblPerson, $Open)));
        } else {
            $Content = (new Warning('Person wurde nicht gefunden.'));
        }
        $Stage->setContent($Content);

        return $Stage;
    }
}