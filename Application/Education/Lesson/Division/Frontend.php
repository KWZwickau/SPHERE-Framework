<?php
namespace SPHERE\Application\Education\Lesson\Division;

use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Lesson\Division
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null|array $Level
     *
     * @return Stage
     */
    public function frontendCreateLevel($Level = null)
    {

        $Stage = new Stage('Klassenstufen', 'bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));

        $tblLevelAll = Division::useService()->getLevelAll();
        if ($tblLevelAll) {
            array_walk($tblLevelAll, function (TblLevel &$tblLevel) {

                $tblType = $tblLevel->getServiceTblType();
                $tblLevel->Type = ( $tblType
                    ? $tblType->getName().' '.$tblType->getDescription()
                    : ''
                );
                $tblLevel->Option = new Standard('', '', new Pencil(), array(), 'Bearbeiten')
                    .new Standard('', '', new Remove(), array(), 'Löschen');
            });
        }
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($tblLevelAll, null, array(
                                'Type'        => 'Schulart',
                                'Name'        => 'Klassenstufe',
                                'Description' => 'Beschreibung',
                                'Option'      => 'Optionen',
                            ))
                        )
                    ), new Title('Bestehende Klassenstufen')
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            Division::useService()->createLevel(
                                $this->formLevel()
                                    ->appendFormButton(new Primary('Klassenstufe hinzufügen'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $Level
                            )
                        )
                    ), new Title('Klassenstufe hinzufügen')
                )
            ))
        );

        return $Stage;
    }

    /**
     * @param null|TblLevel $tblLevel
     *
     * @return Form
     */
    public function formLevel(TblLevel $tblLevel = null)
    {

        $tblLevelAll = Division::useService()->getLevelAll();
        $acNameAll = array();
        if ($tblLevelAll) {
            array_walk($tblLevelAll, function (TblLevel $tblLevel) use (&$acNameAll) {

                if (!in_array($tblLevel->getName(), $acNameAll)) {
                    array_push($acNameAll, $tblLevel->getName());
                }
            });
        }

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Level'] ) && $tblLevel) {
            $Global->POST['Level']['Type'] = ( $tblLevel->getServiceTblType() ? $tblLevel->getServiceTblType()->getId() : 0 );
            $Global->POST['Level']['Name'] = $tblLevel->getName();
            $Global->POST['Level']['Description'] = $tblLevel->getDescription();
            $Global->savePost();
        }

        $tblSchoolTypeAll = Type::useService()->getTypeAll();
        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Klassenstufe',
                            array(
                                new SelectBox('Level[Type]', 'Schulart', array(
                                    '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
                                ), new Education()),
                                new AutoCompleter('Level[Name]', 'Klassenstufe (Nummer)', 'z.B: 5', $acNameAll,
                                    new Pencil()),
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        new Panel('Sonstiges',
                            new TextField('Level[Description]', 'zb: für Fortgeschrittene', 'Beschreibung',
                                new Pencil())
                            , Panel::PANEL_TYPE_INFO
                        ), 6),
                )),
            ))
        );
    }

    /**
     * @param null|array $Division
     *
     * @return Stage
     */
    public function frontendCreateDivision($Division = null)
    {

        $Stage = new Stage('Klassengruppen', 'bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));

        $tblDivisionAll = Division::useService()->getDivisionAll();
        if ($tblDivisionAll) {
            array_walk($tblDivisionAll, function (TblDivision &$tblDivision) {

                $tblYear = $tblDivision->getServiceTblYear();
                $tblDivision->Year = ( $tblYear
                    ? $tblYear->getName().' '.$tblYear->getDescription() : '' );
                $tblDivision->Level = ( $tblDivision->getTblLevel()->getName()
                    ? $tblDivision->getTblLevel()->getName().' '.
                    new Muted($tblDivision->getTblLevel()->getServiceTblType()->getName()) : '' );
                $tblDivision->Option = new Standard('', '/Education/Lesson/Division/Change/Division', new Pencil(),
                        array('Id' => $tblDivision->getId()))
                    .new Standard('', '/Education/Lesson/Division/Destroy/Division', new Remove(), array('Id' => $tblDivision->getId()));
            });
        }
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($tblDivisionAll, null, array(
                                'Year'        => 'Schuljahr',
                                'Level'       => 'Klassenstufe',
                                'Name'        => 'Klassengruppe',
                                'Description' => 'Beschreibung',
                                'Option'      => 'Optionen',
                            ))
                        )
                    ), new Title('Bestehende Klassengruppen')
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            Division::useService()->createDivision(
                                $this->formDivision()
                                    ->appendFormButton(new Primary('Klassengruppe hinzufügen'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $Division
                            )
                        )
                    ), new Title('Klassengruppe hinzufügen')
                )
            ))
        );

        return $Stage;
    }

    /**
     * @param null|TblDivision $tblDivision
     *
     * @return Form
     */
    public function formDivision(TblDivision $tblDivision = null)
    {

        $tblDivisionAll = Division::useService()->getDivisionAll();
        $acNameAll = array();
        if ($tblDivisionAll) {
            array_walk($tblDivisionAll, function (TblDivision $tblDivision) use (&$acNameAll) {

                if (!in_array($tblDivision->getName(), $acNameAll)) {
                    array_push($acNameAll, $tblDivision->getName());
                }
            });
        }

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Division'] ) && $tblDivision) {
            $Global->POST['Division']['Year'] = ( $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getId() : 0 );
            $Global->POST['Division']['Level'] = ( $tblDivision->getTblLevel() ? $tblDivision->getTblLevel()->getId() : 0 );
            $Global->POST['Division']['Name'] = $tblDivision->getName();
            $Global->POST['Division']['Description'] = $tblDivision->getDescription();
            $Global->savePost();
        }

        $tblYearAll = Term::useService()->getYearAll();

        $tblLevelAll = Division::useService()->getLevelAll();
        if ($tblLevelAll) {
//            array_push($tblLevelAll, new TblLevel());
        } else {
            $tblLevelAll = array();
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Klassengruppe',
                            array(
                                new SelectBox('Division[Year]', 'Schuljahr', array(
                                    '{{ Name }} {{ Description }}' => $tblYearAll
                                ), new Education()),
                                new SelectBox('Division[Level]', 'Klassenstufe', array(
                                    'Stufe: {{ Name }} {{ Description }} Schulart: {{ serviceTblType.Name }} {{ serviceTblType.Description }}' => $tblLevelAll
                                ), new Education()),
                                new AutoCompleter('Division[Name]', 'Klassengruppe (Name)', 'z.B: Alpha', $acNameAll,
                                    new Pencil()),
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        new Panel('Sonstiges',
                            new TextField('Division[Description]', 'zb: für Fortgeschrittene', 'Beschreibung',
                                new Pencil())
                            , Panel::PANEL_TYPE_INFO
                        ), 6),
                )),
            ))
        );
    }

    /**
     * @param $Id
     * @param $Division
     *
     * @return Stage
     */
    public function frontendChangeDivision($Id, $Division)
    {

        $Stage = new Stage('Klassengruppe', 'bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Create/Division', new ChevronLeft()));
        $tblDivision = Division::useService()->getDivisionById($Id);
        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Id'] ) && $tblDivision) {
            $Global->POST['Division']['Year'] = $tblDivision->getServiceTblYear()->getId();
            $Global->POST['Division']['Level'] = $tblDivision->getTblLevel()->getId();
            $Global->POST['Division']['Name'] = $tblDivision->getName();
            $Global->POST['Division']['Description'] = $tblDivision->getDescription();
            $Global->savePost();
        }
        $Stage->setContent(Division::useService()->changeDivision($this->formDivision($tblDivision)
            ->appendFormButton(new Primary('Änderung speichern'))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
            , $Division, $Id));

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage|string
     */
    public function frontendDestroyDivision($Id)
    {

        $Stage = new Stage('Klassengruppe', 'entfernen');
        $tblDivision = Division::useService()->getDivisionById($Id);
        if ($tblDivision) {
            $Stage->setContent(Division::useService()->destroyDivision($tblDivision));
        } else {
            return $Stage.new Warning('Klassengruppe nicht gefunden!')
            .new Redirect('/Education/Lesson/Division/Create/Division');
        }
        return $Stage;
    }
}
