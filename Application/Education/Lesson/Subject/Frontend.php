<?php
namespace SPHERE\Application\Education\Lesson\Subject;

use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
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
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Lesson\Subject
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null|array $Subject
     *
     * @return Stage
     */
    public function frontendCreateSubject($Subject = null)
    {

        $Stage = new Stage('Fächer', 'Bearbeiten');

        $tblSubjectAll = Subject::useService()->getSubjectAll();
        array_walk($tblSubjectAll, function (TblSubject &$tblSubject) {

            $tblSubject->Option = new Standard('', '', new Pencil(), array(), 'Bearbeiten')
                .new Standard('', '', new Remove(), array(), 'Löschen');
        });

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($tblSubjectAll, null, array(
                                'Acronym'     => 'Kürzel',
                                'Name'        => 'Name',
                                'Description' => 'Beschreibung',
                                'Option'      => 'Optionen',
                            ))
                        )
                    ), new Title('Bestehende Fächer')
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            Subject::useService()->createSubject(
                                $this->formSubject()
                                    ->appendFormButton(new Primary('Fach hinzufügen'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $Subject
                            )
                        )
                    ), new Title('Fach hinzufügen')
                )
            ))
        );

        return $Stage;
    }

    /**
     * @param null|TblSubject $tblSubject
     *
     * @return Form
     */
    public function formSubject(TblSubject $tblSubject = null)
    {

        $tblSubjectAll = Subject::useService()->getSubjectAll();
        $acAcronymAll = array();
        $acNameAll = array();
        array_walk($tblSubjectAll, function (TblSubject $tblSubject) use (&$acAcronymAll, &$acNameAll) {

            if (!in_array($tblSubject->getAcronym(), $acAcronymAll)) {
                array_push($acAcronymAll, $tblSubject->getAcronym());
            }
            if (!in_array($tblSubject->getName(), $acNameAll)) {
                array_push($acNameAll, $tblSubject->getName());
            }
        });

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Subject'] ) && $tblSubject) {
            $Global->POST['Subject']['Acronym'] = $tblSubject->getAcronym();
            $Global->POST['Subject']['Name'] = $tblSubject->getName();
            $Global->POST['Subject']['Description'] = $tblSubject->getDescription();
            $Global->savePost();
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Fach',
                            array(
                                new AutoCompleter('Subject[Acronym]', 'Kürzel', 'z.B: DE', $acAcronymAll),
                                new AutoCompleter('Subject[Name]', 'Name', 'z.B: Deutsch', $acNameAll),
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        new Panel('Sonstiges',
                            new TextField('Subject[Description]', 'zb: für Fortgeschrittene', 'Beschreibung',
                                new Pencil())
                            , Panel::PANEL_TYPE_INFO
                        ), 6),
                )),
            ))
        );
    }


}
