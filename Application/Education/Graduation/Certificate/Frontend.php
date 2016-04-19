<?php
namespace SPHERE\Application\Education\Graduation\Certificate;

use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Element;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Blackboard;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Backward;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    public function frontendSelectDivision($SinceYears = 1)
    {

        $Stage = new Stage('Klasse', 'wählen');
        $Stage->addButton(new Backward());

        $tblYearAll = Term::useService()->getYearAllSinceYears($SinceYears);

        $DivisionList = array();
        if ($tblYearAll) {
            array_walk($tblYearAll, function (TblYear $tblYear) use (&$DivisionList) {

                $tblPeriodAll = $tblYear->getTblPeriodAll();
                $PeriodList = array();
                array_walk($tblPeriodAll, function (TblPeriod $tblPeriod) use (&$PeriodList) {

                    $PeriodList[] = $tblPeriod->getDisplayName();
                });
                $tblDivisionAll = Division::useService()->getDivisionByYear($tblYear);
                if ($tblDivisionAll) {
                    array_walk($tblDivisionAll,
                        function (TblDivision $tblDivision) use (&$DivisionList, $tblYear, $PeriodList) {

                            $Division = array(
                                'Year'     => $tblYear->getName(),
                                'Period'   => (new Listing($PeriodList))->__toString(),
                                'Division' => $tblDivision->getDisplayName(),
                                'Option'   => new Standard(
                                    'Weiter', '/Education/Graduation/Certificate/Select/Student', new ChevronRight(),
                                    array(
                                        'Division' => $tblDivision->getId()
                                    ), $tblDivision->getDisplayName().' auswählen')
                            );
                            array_push($DivisionList, $Division);
                        });
                }
            });
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(new LayoutColumn(
                        new TableData($DivisionList, null, array(
                            'Year'     => 'Schuljahr',
                            'Period'   => 'Zeitraum',
                            'Division' => 'Klasse',
                            'Option'   => ' '
                        ), array('pageLength' => -1))
                    ))
                    , new Title('Verfügbare Klassen')),
            ))
        );

        return $Stage;
    }

    public function frontendSelectStudent($Division = null)
    {

        $Stage = new Stage('Schüler', 'wählen');
        $Stage->addButton(new Backward());
        $Header = '';

        if ($Division) {
            $tblDivision = Division::useService()->getDivisionById($Division);

            if ($tblDivision) {
                $Header = new Panel(new Blackboard().' '.$tblDivision->getDisplayName(), array(
                    $tblDivision->getServiceTblYear()->getName()
                ), Panel::PANEL_TYPE_SUCCESS);

                $tblPersonAll = Division::useService()->getStudentAllByDivision($tblDivision);
                $PersonList = array();
                if ($tblPersonAll) {
                    array_walk($tblPersonAll, function (TblPerson $tblPerson) use (&$PersonList, $tblDivision) {

                        $PersonList[] = array(
                            'Student' => $tblPerson->getLastFirstName(),
                            'Option'  => new Standard(
                                'Weiter', '/Education/Graduation/Certificate/Select/Certificate',
                                new ChevronRight(),
                                array(
                                    'Division' => $tblDivision->getId(),
                                    'Person'   => $tblPerson->getId()
                                ), $tblPerson->getLastFirstName().' auswählen')
                        );
                    });

                    $Content = new TableData($PersonList, null, array(
                        'Student' => 'Schüler',
                        'Option'  => ' '
                    ), array('pageLength' => -1));
                } else {
                    $Content = new Danger('Dieser Klasse wurden noch keine Schüler zugewiesen')
                        .new Standard('Zurück', '/Education/Graduation/Certificate', new ChevronLeft());
                }
            } else {
                $Content = new Danger('Gewählte Schulklasse ist nicht verfügbar')
                    .new Standard('Zurück', '/Education/Graduation/Certificate', new ChevronLeft());
            }
        } else {
            $Content = new Danger('Keine Schulklasse gewählt')
                .new Standard('Zurück', '/Education/Graduation/Certificate', new ChevronLeft());
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(new LayoutColumn($Header))
                    , new Title('Gewählte Klassenstufe')),
                new LayoutGroup(
                    new LayoutRow(new LayoutColumn($Content))
                    , new Title('Verfügbare Schüler')),
            ))
        );

        return $Stage;
    }

    /**
     * @param null|int $Division
     * @param null|int $Person
     *
     * @return Stage
     */
    public function frontendSelectCertificate($Division = null, $Person = null)
    {

        $Stage = new Stage('Zeugnisvorlage', 'wählen');
        $Stage->addButton(new Backward());
        $Header = '';
        $consumerAcronym = Consumer::useService()->getConsumerBySession()->getAcronym();

        if ($Division && $Person) {
            $tblDivision = Division::useService()->getDivisionById($Division);
            if ($tblDivision) {
                $tblPerson = Person::useService()->getPersonById($Person);
                if ($tblPerson) {
                    $Header = array(
                        new Panel(new Blackboard().' '.$tblDivision->getDisplayName(), array(
                            $tblDivision->getServiceTblYear()->getName()
                        ), Panel::PANEL_TYPE_SUCCESS),
                        new Panel(new Education().' '.$tblPerson->getFullName(), array(
                            ( $tblPerson->fetchMainAddress() ? $tblPerson->fetchMainAddress()->getGuiString() : '' )
                        ), Panel::PANEL_TYPE_SUCCESS),
                    );

                    // TODO: Find Templates in Database (DMS)
                    $TemplateTable[] = array(
                        'Template' => 'Grundschule Halbjahresinformation',
                        'Option'   => new Standard(
                            'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                            'Division'    => $tblDivision->getId(),
                            'Person'      => $tblPerson->getId(),
                            'Certificate' => 'GSHJInfo'
                        ), 'Auswählen')
                    );
                    $TemplateTable[] = array(
                        'Template' => 'Grundschule Jahreszeugnis',
                        'Option'   => new Standard(
                            'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                            'Division'    => $tblDivision->getId(),
                            'Person'      => $tblPerson->getId(),
                            'Certificate' => 'GSJ'
                        ), 'Auswählen')
                    );
                    $TemplateTable[] = array(
                        'Template' => 'Grundschule Halbjahresinformation der ersten Klasse',
                        'Option'   => new Standard(
                            'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                            'Division'    => $tblDivision->getId(),
                            'Person'      => $tblPerson->getId(),
                            'Certificate' => 'GsHjOneInfo'
                        ), 'Auswählen')
                    );
                    $TemplateTable[] = array(
                        'Template' => 'Grundschule Jahreszeugnis der ersten Klasse',
                        'Option'   => new Standard(
                            'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                            'Division'    => $tblDivision->getId(),
                            'Person'      => $tblPerson->getId(),
                            'Certificate' => 'GsJOne'
                        ), 'Auswählen')
                    );
                    $TemplateTable[] = array(
                        'Template' => 'Mittelschule Abgangszeugnis Hauptschule',
                        'Option'   => new Standard(
                            'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                            'Division'    => $tblDivision->getId(),
                            'Person'      => $tblPerson->getId(),
                            'Certificate' => 'MsAbgHs'
                        ), 'Auswählen')
                    );
                    $TemplateTable[] = array(
                        'Template' => 'Mittelschule Abgangszeugnis Realschule',
                        'Option'   => new Standard(
                            'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                            'Division'    => $tblDivision->getId(),
                            'Person'      => $tblPerson->getId(),
                            'Certificate' => 'MsAbgRs'
                        ), 'Auswählen')
                    );
                    $TemplateTable[] = array(
                        'Template' => 'Mittelschule Abschlusszeugnis Hauptschule',
                        'Option'   => new Standard(
                            'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                            'Division'    => $tblDivision->getId(),
                            'Person'      => $tblPerson->getId(),
                            'Certificate' => 'MsAbsHs'
                        ), 'Auswählen')
                    );
                    $TemplateTable[] = array(
                        'Template' => 'Mittelschule qualifiziertes Abschlusszeugnis Hauptschule',
                        'Option'   => new Standard(
                            'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                            'Division'    => $tblDivision->getId(),
                            'Person'      => $tblPerson->getId(),
                            'Certificate' => 'MsAbsHsQ'
                        ), 'Auswählen')
                    );
                    $TemplateTable[] = array(
                        'Template' => 'Mittelschule Abschlusszeugnis Realschule',
                        'Option'   => new Standard(
                            'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                            'Division'    => $tblDivision->getId(),
                            'Person'      => $tblPerson->getId(),
                            'Certificate' => 'MsAbsRs'
                        ), 'Auswählen')
                    );
                    $TemplateTable[] = array(
                        'Template' => 'Mittelschule Halbjahreszeugnis Klasse 5-6',
                        'Option'   => new Standard(
                            'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                            'Division'    => $tblDivision->getId(),
                            'Person'      => $tblPerson->getId(),
                            'Certificate' => 'MsHj'
                        ), 'Auswählen')
                    );
                    $TemplateTable[] = array(
                        'Template' => 'Mittelschule Halbjahreszeugnis Hauptschule',
                        'Option'   => new Standard(
                            'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                            'Division'    => $tblDivision->getId(),
                            'Person'      => $tblPerson->getId(),
                            'Certificate' => 'MsHjHs'
                        ), 'Auswählen')
                    );
                    $TemplateTable[] = array(
                        'Template' => 'Mittelschule Halbjahreszeugnis Realschule',
                        'Option'   => new Standard(
                            'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                            'Division'    => $tblDivision->getId(),
                            'Person'      => $tblPerson->getId(),
                            'Certificate' => 'MsHjRs'
                        ), 'Auswählen')
                    );
                    $TemplateTable[] = array(
                        'Template' => 'Mittelschule Halbjahresinformation Klasse 5-6',
                        'Option'   => new Standard(
                            'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                            'Division'    => $tblDivision->getId(),
                            'Person'      => $tblPerson->getId(),
                            'Certificate' => 'MsHjInfo'
                        ), 'Auswählen')
                    );
                    $TemplateTable[] = array(
                        'Template' => 'Mittelschule Halbjahresinformation Hauptschule',
                        'Option'   => new Standard(
                            'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                            'Division'    => $tblDivision->getId(),
                            'Person'      => $tblPerson->getId(),
                            'Certificate' => 'MsHjInfoHs'
                        ), 'Auswählen')
                    );
                    $TemplateTable[] = array(
                        'Template' => 'Mittelschule Halbjahresinformation Realschule',
                        'Option'   => new Standard(
                            'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                            'Division'    => $tblDivision->getId(),
                            'Person'      => $tblPerson->getId(),
                            'Certificate' => 'MsHjInfoRs'
                        ), 'Auswählen')
                    );
                    $TemplateTable[] = array(
                        'Template' => 'Mittelschule Jahreszeugnis Klasse 5-6',
                        'Option'   => new Standard(
                            'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                            'Division'    => $tblDivision->getId(),
                            'Person'      => $tblPerson->getId(),
                            'Certificate' => 'MsJ'
                        ), 'Auswählen')
                    );
                    $TemplateTable[] = array(
                        'Template' => 'Mittelschule Jahreszeugnis Hauptschule',
                        'Option'   => new Standard(
                            'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                            'Division'    => $tblDivision->getId(),
                            'Person'      => $tblPerson->getId(),
                            'Certificate' => 'MsJHs'
                        ), 'Auswählen')
                    );
                    $TemplateTable[] = array(
                        'Template' => 'Mittelschule Jahreszeugnis Realschule',
                        'Option'   => new Standard(
                            'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                            'Division'    => $tblDivision->getId(),
                            'Person'      => $tblPerson->getId(),
                            'Certificate' => 'MsJRs'
                        ), 'Auswählen')
                    );
                    $TemplateTable[] = array(
                        'Template' => 'Gymnasium Abgangszeugnis Hauptschulabschluss Klasse 9',
                        'Option'   => new Standard(
                            'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                            'Division'    => $tblDivision->getId(),
                            'Person'      => $tblPerson->getId(),
                            'Certificate' => 'GymAbgHs'
                        ), 'Auswählen')
                    );
                    $TemplateTable[] = array(
                        'Template' => 'Gymnasium Abgangszeugnis Realschulabschluss Klasse 10',
                        'Option'   => new Standard(
                            'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                            'Division'    => $tblDivision->getId(),
                            'Person'      => $tblPerson->getId(),
                            'Certificate' => 'GymAbgRs'
                        ), 'Auswählen')
                    );
                    $TemplateTable[] = array(
                        'Template' => 'Gymnasium Halbjahreszeugnis',
                        'Option'   => new Standard(
                            'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                            'Division'    => $tblDivision->getId(),
                            'Person'      => $tblPerson->getId(),
                            'Certificate' => 'GymHj'
                        ), 'Auswählen')
                    );
                    $TemplateTable[] = array(
                        'Template' => 'Gymnasium Halbjahresinformation',
                        'Option'   => new Standard(
                            'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                            'Division'    => $tblDivision->getId(),
                            'Person'      => $tblPerson->getId(),
                            'Certificate' => 'GymHjInfo'
                        ), 'Auswählen')
                    );
                    $TemplateTable[] = array(
                        'Template' => 'Gymnasium Jahreszeugnis',
                        'Option'   => new Standard(
                            'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                            'Division'    => $tblDivision->getId(),
                            'Person'      => $tblPerson->getId(),
                            'Certificate' => 'GymJ'
                        ), 'Auswählen')
                    );
                    if ($consumerAcronym === 'EVSC' || $consumerAcronym === 'DEMO') {
                        $TemplateTable[] = array(
                            'Template' => 'Coswig Halbjahresinformation Primarstufe',
                            'Option'   => new Standard(
                                'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                                'Division'    => $tblDivision->getId(),
                                'Person'      => $tblPerson->getId(),
                                'Certificate' => 'CosHjPri'
                            ), 'Auswählen')
                        );
                        $TemplateTable[] = array(
                            'Template' => 'Coswig Halbjahresinformation Sekundarstufe',
                            'Option'   => new Standard(
                                'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                                'Division'    => $tblDivision->getId(),
                                'Person'      => $tblPerson->getId(),
                                'Certificate' => 'CosHjSek'
                            ), 'Auswählen')
                        );
                        $TemplateTable[] = array(
                            'Template' => 'Coswig Jahreszeugnis Primarstufe',
                            'Option'   => new Standard(
                                'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                                'Division'    => $tblDivision->getId(),
                                'Person'      => $tblPerson->getId(),
                                'Certificate' => 'CosJPri'
                            ), 'Auswählen')
                        );
                        $TemplateTable[] = array(
                            'Template' => 'Coswig Jahreszeugnis Sekundarstufe',
                            'Option'   => new Standard(
                                'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                                'Division'    => $tblDivision->getId(),
                                'Person'      => $tblPerson->getId(),
                                'Certificate' => 'CosJSek'
                            ), 'Auswählen')
                        );
                    }
                    if ($consumerAcronym === 'FEGH' || $consumerAcronym === 'DEMO') {
                        $TemplateTable[] = array(
                            'Template' => 'Hormersdorf Halbjahresinformation',
                            'Option'   => new Standard(
                                'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                                'Division'    => $tblDivision->getId(),
                                'Person'      => $tblPerson->getId(),
                                'Certificate' => 'HorHj'
                            ), 'Auswählen')
                        );
                        $TemplateTable[] = array(
                            'Template' => 'Hormersdorf Halbjahresinformation 1. Klasse',
                            'Option'   => new Standard(
                                'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                                'Division'    => $tblDivision->getId(),
                                'Person'      => $tblPerson->getId(),
                                'Certificate' => 'HorHjOne'
                            ), 'Auswählen')
                        );
                        $TemplateTable[] = array(
                            'Template' => 'Hormersdorf Jahreszeugnis',
                            'Option'   => new Standard(
                                'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                                'Division'    => $tblDivision->getId(),
                                'Person'      => $tblPerson->getId(),
                                'Certificate' => 'HorJ'
                            ), 'Auswählen')
                        );
                        $TemplateTable[] = array(
                            'Template' => 'Hormersdorf Jahreszeugnis 1. Klasse',
                            'Option'   => new Standard(
                                'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                                'Division'    => $tblDivision->getId(),
                                'Person'      => $tblPerson->getId(),
                                'Certificate' => 'HorJOne'
                            ), 'Auswählen')
                        );
                    }
                    if ($consumerAcronym === 'ESZC' || $consumerAcronym === 'DEMO') {
                        $TemplateTable[] = array(
                            'Template' => 'Chemnitz Bildungsempfehlung Mittelschule',
                            'Option'   => new Standard(
                                'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                                'Division'    => $tblDivision->getId(),
                                'Person'      => $tblPerson->getId(),
                                'Certificate' => 'CheBeMi'
                            ), 'Auswählen')
                        );
                        $TemplateTable[] = array(
                            'Template' => 'Chemnitz Bildungsempfehlung Gymnasium',
                            'Option'   => new Standard(
                                'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                                'Division'    => $tblDivision->getId(),
                                'Person'      => $tblPerson->getId(),
                                'Certificate' => 'CheBeGym'
                            ), 'Auswählen')
                        );
                        $TemplateTable[] = array(
                            'Template' => 'Chemnitz Halbjahresinformation Klasse 5-6',
                            'Option'   => new Standard(
                                'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                                'Division'    => $tblDivision->getId(),
                                'Person'      => $tblPerson->getId(),
                                'Certificate' => 'CheHjInfo'
                            ), 'Auswählen')
                        );
                        $TemplateTable[] = array(
                            'Template' => 'Chemnitz Halbjahresinformation Hauptschule',
                            'Option'   => new Standard(
                                'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                                'Division'    => $tblDivision->getId(),
                                'Person'      => $tblPerson->getId(),
                                'Certificate' => 'CheHjInfoHs'
                            ), 'Auswählen')
                        );
                        $TemplateTable[] = array(
                            'Template' => 'Chemnitz Halbjahresinformation Realschule',
                            'Option'   => new Standard(
                                'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                                'Division'    => $tblDivision->getId(),
                                'Person'      => $tblPerson->getId(),
                                'Certificate' => 'CheHjInfoRs'
                            ), 'Auswählen')
                        );
                        $TemplateTable[] = array(
                            'Template' => 'Chemnitz Halbjahreszeugnis Klasse 5-6',
                            'Option'   => new Standard(
                                'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                                'Division'    => $tblDivision->getId(),
                                'Person'      => $tblPerson->getId(),
                                'Certificate' => 'CheHj'
                            ), 'Auswählen')
                        );
                        $TemplateTable[] = array(
                            'Template' => 'Chemnitz Halbjahreszeugnis Hauptschule',
                            'Option'   => new Standard(
                                'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                                'Division'    => $tblDivision->getId(),
                                'Person'      => $tblPerson->getId(),
                                'Certificate' => 'CheHjHs'
                            ), 'Auswählen')
                        );
                        $TemplateTable[] = array(
                            'Template' => 'Chemnitz Halbjahreszeugnis Realschule',
                            'Option'   => new Standard(
                                'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                                'Division'    => $tblDivision->getId(),
                                'Person'      => $tblPerson->getId(),
                                'Certificate' => 'CheHjRs'
                            ), 'Auswählen')
                        );
                        $TemplateTable[] = array(
                            'Template' => 'Chemnitz Jahreszeugnis',
                            'Option'   => new Standard(
                                'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                                'Division'    => $tblDivision->getId(),
                                'Person'      => $tblPerson->getId(),
                                'Certificate' => 'CheJ'
                            ), 'Auswählen')
                        );
                        $TemplateTable[] = array(
                            'Template' => 'Chemnitz Halbjahresinfromation Gymnasium',
                            'Option'   => new Standard(
                                'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                                'Division'    => $tblDivision->getId(),
                                'Person'      => $tblPerson->getId(),
                                'Certificate' => 'CheHjGymInfo'
                            ), 'Auswählen')
                        );
                        $TemplateTable[] = array(
                            'Template' => 'Chemnitz Halbjahreszeugnis Gymnasium',
                            'Option'   => new Standard(
                                'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                                'Division'    => $tblDivision->getId(),
                                'Person'      => $tblPerson->getId(),
                                'Certificate' => 'CheHjGym'
                            ), 'Auswählen')
                        );
                        $TemplateTable[] = array(
                            'Template' => 'Chemnitz Jahreszeugnis Gymnasium',
                            'Option'   => new Standard(
                                'Weiter', '/Education/Graduation/Certificate/Select/Content', new ChevronRight(), array(
                                'Division'    => $tblDivision->getId(),
                                'Person'      => $tblPerson->getId(),
                                'Certificate' => 'CheJGym'
                            ), 'Auswählen')
                        );
                    }

                    $Content = new TableData($TemplateTable);

                } else {
                    // TODO: Error
                    $Content = new Danger('Keine Schulklasse gewählt')
                        .new Standard('Zurück', '/Education/Graduation/Certificate', new ChevronLeft());
                }
            } else {
                // TODO: Error
                $Content = new Danger('Keine Schulklasse gewählt')
                    .new Standard('Zurück', '/Education/Graduation/Certificate', new ChevronLeft());
            }
        } else {
            // TODO: Error
            $Content = new Danger('Keine Schulklasse gewählt')
                .new Standard('Zurück', '/Education/Graduation/Certificate', new ChevronLeft());
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(new LayoutColumn($Header[0]))
                    , new Title('Gewählte Klassenstufe')),
                new LayoutGroup(
                    new LayoutRow(new LayoutColumn($Header[1]))
                    , new Title('Gewählter Schüler')),
                new LayoutGroup(new LayoutRow(
                    new LayoutColumn($Content)
                ), new Title('Verfügbare Vorlagen')),
            ))
        );

        return $Stage;
    }

    public function frontendSelectContent(
        $Division = null,
        $Person = null,
        $Certificate = null,
        $Content = array(),
        $SaveAs = null
    ) {

        $Stage = new Stage('Zeugnisdaten', 'überprüfen');
        $Stage->addButton(new Backward(true));

        if (!$SaveAs) {
            $Global = $this->getGlobal();
            $Global->POST['SaveAs'] = 0;
            $Global->savePost();
        }

        $Form = '';
        $Header = '';

        if ($Division && $Person) {
            $tblDivision = Division::useService()->getDivisionById($Division);
            if ($tblDivision) {
                $tblPerson = Person::useService()->getPersonById($Person);
                if ($tblPerson) {
                    $Header = implode(array(
                        new Panel(new Education().' '.$tblPerson->getFullName(), array(
                            ( $tblPerson->fetchMainAddress() ? $tblPerson->fetchMainAddress()->getGuiString() : '' )
                        ), Panel::PANEL_TYPE_SUCCESS),
                        new Panel(new Blackboard().' '.$tblDivision->getDisplayName(), array(
                            $tblDivision->getServiceTblYear()->getName()
                        ), Panel::PANEL_TYPE_SUCCESS),
                    ));

                    $CertificateClass = '\SPHERE\Application\Api\Education\Graduation\Certificate\Repository\\'.$Certificate;
                    if (class_exists($CertificateClass)) {

                        /** @var \SPHERE\Application\Api\Education\Graduation\Certificate\Certificate $Template */
                        $Template = new $CertificateClass($tblPerson, $tblDivision);

                        $GradeList = $Template->getGrade();
                        $HeaderBehavior = array();
                        if (isset( $GradeList['Data']['BEHAVIOR'] )) {
                            foreach ($GradeList['Data']['BEHAVIOR'] as $Acronym => $Value) {
                                $tblGradeType = Gradebook::useService()->getGradeTypeByCode($Acronym);
                                array_push($HeaderBehavior, $tblGradeType->getName().': '.$Value);
                            }
                        }
                        sort($HeaderBehavior);
                        $Header .= new Panel(new Education().' Kopfnoten (Durchschnitt)',
                            $HeaderBehavior
                            , Panel::PANEL_TYPE_SUCCESS);

                        $FormField = array(
                            'Content.Person.Common.BirthDates.Birthday' => 'DatePicker',

                            'Content.Input.Adding'    => 'TextField',     //ToDO <- deleting after rebuild
                            'Content.Input.Remark'    => 'TextArea',
                            'Content.Input.Rating'    => 'TextArea',
                            'Content.Input.Survey'    => 'TextArea',
                            'Content.Input.Team'      => 'TextArea',
                            'Content.Input.Deepening' => 'TextField',
                            'Content.Input.Choose'    => 'TextField',
                            'Content.Input.Date'      => 'DatePicker',
                            'Content.Input.Transfer'    => 'TextField',
                            'Content.Input.LevelTwo'    => 'TextField',
                            'Content.Input.LevelThree'  => 'TextField',
                            'Content.Input.Missing'     => 'TextField',
                            'Content.Input.Bad.Missing' => 'TextField',
                        );
                        $FormLabel = array(
                            'Content.Person.Data.Name.Salutation' => 'Anrede',
                            'Content.Person.Data.Name.First'      => 'Vorname',
                            'Content.Person.Data.Name.Last'       => 'Nachname',
                            'Content.Person.Common.BirthDates.Birthday' => 'Geburtsdatum',

                            'Content.Company.Data.Name' => 'Name der Schule',

                            'Content.Division.Data.Level.Name' => 'Klassenstufe',
                            'Content.Division.Data.Name'       => 'Klassengruppe',

                            'Content.Input.Adding'    => 'nahm am Unterricht der Schulart Mittelschule mit dem Ziel des [...] teil.',
                            //ToDO <- deleting after rebuild
                            'Content.Input.Remark'    => 'Bemerkungen',
                            'Content.Input.Rating'    => 'Einschätzung',
                            'Content.Input.Survey'    => 'Gutachten',
                            'Content.Input.Team'      => 'Arbeitsgemeinschaften',
                            'Content.Input.Deepening' => 'Vertiefungsrichtung',
                            'Content.Input.Choose'    => 'Wahlpflichtbereich',
                            'Content.Input.Date'                  => 'Datum',
                            'Content.Input.Transfer'              => 'Versetzungsvermerk',
                            'Content.Input.LevelTwo'              => '2. Fremdsprache ab Klassenstufe',
                            'Content.Input.LevelThree'            => '3. Fremdsprache ab Klassenstufe',
                            'Content.Input.Missing'               => 'Fehltage entschuldigt',
                            'Content.Input.Bad.Missing'           => 'Fehltage unentschuldigt',
                        );

                        // Create Form, Additional Information from Template
                        $PlaceholderList = $Template->getCertificate()->getPlaceholder();
                        $FormPanelList = array();
                        if ($PlaceholderList) {
                            array_walk($PlaceholderList,
                                function ($Placeholder) use ($Template, $FormField, $FormLabel, &$FormPanelList) {

                                    $PlaceholderList = explode('.', $Placeholder);
                                    $Identifier = array_slice($PlaceholderList, 1);

                                    $FieldName = $PlaceholderList[0].'['.implode('][', $Identifier).']';

                                    $Type = array_shift($Identifier);
                                    if (method_exists($Template, 'get'.$Type)) {
                                        $Payload = $Template->{'get'.$Type}();

                                        foreach ($Identifier as $Key) {
                                            if (isset( $Payload[$Key] )) {
                                                $Payload = $Payload[$Key];
                                            } else {
//                                                $Payload = '';
                                                break;
                                            }
                                        }
                                        if (isset( $FormLabel[$Placeholder] )) {
//                                            $Label = $FormLabel[$Placeholder];
                                        } else {
//                                            $Label = $Placeholder;
                                        }
                                        if (isset( $FormField[$Placeholder] )) {
//                                            $Field = '\SPHERE\Common\Frontend\Form\Repository\Field\\'.$FormField[$Placeholder];
//                                            $Placeholder = (new $Field($FieldName, $Label, $Label));
                                        } else {
//                                            $Placeholder = (new TextField($FieldName, $Label, $Label));
                                        }
                                        /** @var AbstractField $Placeholder */
//                                        $Placeholder = $Placeholder->setDefaultValue($Payload,true);
//                                        $FormPanelList[$Type][] = $Placeholder;
                                    } else {
                                        if (isset( $FormLabel[$Placeholder] )) {
                                            $Label = $FormLabel[$Placeholder];
                                        } else {
                                            $Label = $Placeholder;
                                        }
                                        if (isset( $FormField[$Placeholder] )) {
                                            $Field = '\SPHERE\Common\Frontend\Form\Repository\Field\\'.$FormField[$Placeholder];
                                            $Placeholder = (new $Field($FieldName, $Label, $Label));
                                        } else {
                                            $Placeholder = (new TextField($FieldName, $Label, $Label));
                                        }
//                                        /** @var AbstractField $Placeholder */
//                                        $Placeholder = $Placeholder;

                                        $FormPanelList['Additional'][] = $Placeholder;
                                    }
                                });
                        }

                        foreach ($FormPanelList as $Type => $Payload) {
                            switch ($Type) {
                                case 'Person':
                                    $Title = 'Schülerinformationen';
                                    break;
                                case 'Company':
                                    $Title = 'Schulinformationen';
                                    break;
                                case 'Division':
                                    $Title = 'Klassen-Informationen';
                                    break;
                                case 'Grade':
                                    $Title = 'Noten-Informationen';
                                    break;
                                case 'Additional':
                                    $Title = 'Zusätzliche Informationen';
                                    break;
                                default:
                                    $Title = 'Informationen';
                            }
                            $FormPanelList[] = new FormColumn(new Panel($Title, $Payload, Panel::PANEL_TYPE_INFO));
                        }

                        $Form = new Form(
                            new FormGroup(array(
                                new FormRow(
                                    $FormPanelList
                                ),
                                new FormRow(
                                    new FormColumn(
                                        new Panel('Daten verwenden für', array(
                                            new RadioBox('SaveAs', 'Vorschau aktualisieren', 0),
                                            new RadioBox('SaveAs', 'Als Entwurf speichern', 1),
                                            new RadioBox('SaveAs', 'Zeugnis erstellen', 2),
                                        ), Panel::PANEL_TYPE_WARNING)
                                    )
                                )
                            ))
                        );

                        $Form->appendFormButton(
                            new Primary('Absenden')
                        );

                        // Create Certificate, Preview, Draft, Live
                        switch ($SaveAs) {
                            case 0: {
                                $Content = $Template->createCertificate($Content)->getContent();
                                break;
                            }
                            case 1: {

//                                $Draft = new Draft($Person, $Division, $Certificate, $Content);

//                                $Store = Storage::useWriter()->getDatabase();
//                                $Store->setName( 'Zeugnis-Entwurf' );
//                                $Store->setDescription( json_encode( array( 'Person' => $Draft->getPerson(), 'Division' => $Draft->getDivision() ) ) );
//                                $Store->setFileContent( $Draft );
//                                $Store->saveFile();

                                $Store = Storage::useWriter()->getDatabase(3);

                                $Draft = new Draft();
                                $Load = $Draft->decodeDraft($Store->getFileContent());

                                $CertificateClass = '\SPHERE\Application\Api\Education\Graduation\Certificate\Repository\\'.$Load->getCertificate();
                                if (class_exists($CertificateClass)) {
                                    $tblDivision = Division::useService()->getDivisionById($Load->getDivision());
                                    $tblPerson = Person::useService()->getPersonById($Load->getPerson());
                                    /** @var \SPHERE\Application\Api\Education\Graduation\Certificate\Certificate $Template */
                                    $Template = new $CertificateClass($tblPerson, $tblDivision);
                                }
                                $Content = $Template->createCertificate($Load->getData())->getContent();
                                break;
                            }
                            case 2: {
                                $Content = new External(
                                    'Zeugnis erstellen bestätigen',
                                    '/Api/Education/Graduation/Certificate',
                                    new Check(),
                                    array(
                                        'Person'      => $Person,
                                        'Division'    => $Division,
                                        'Certificate' => $Certificate,
                                        'Data'        => $Content
                                    ), false
                                );
                                break;
                            }
                        }

                    } else {
                        // TODO: Error
                        $Content = new Danger('Keine Schulklasse gewählt')
                            .new Standard('Zurück', '/Education/Graduation/Certificate/Select/Division', new ChevronLeft());
                    }
                } else {
                    // TODO: Error
                    $Content = new Danger('Keine Schulklasse gewählt')
                        .new Standard('Zurück', '/Education/Graduation/Certificate/Select/Division', new ChevronLeft());
                }
            } else {
                // TODO: Error
                $Content = new Danger('Keine Schulklasse gewählt')
                    .new Standard('Zurück', '/Education/Graduation/Certificate/Select/Division', new ChevronLeft());
            }
        } else {
            // TODO: Error
            $Content = new Danger('Keine Schulklasse gewählt')
                .new Standard('Zurück', '/Education/Graduation/Certificate/Select/Division', new ChevronLeft());
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Title('Daten für:'),
                            $Header,
                            $Form,
                        ), 5),
                        new LayoutColumn(array(
                            new Title('Vorschau der Daten'),
                            ( $SaveAs == 2 ? $Content : '<div class="cleanslate">'.$Content.'</div>' ),
                        ), 7)
                    ))
                )
            ))
        );
        return $Stage;
    }
}
