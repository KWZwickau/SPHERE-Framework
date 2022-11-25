<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\MinusSign;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Italic;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;

abstract class FrontendGradeType extends FrontendGradeBookSelect
{
    /**
     * @param null $GradeType
     *
     * @return Stage
     */
    public function frontendGradeType($GradeType = null): Stage
    {
        $Stage = new Stage('Zensuren-Typ', 'Übersicht');
        $Stage->setMessage('Hier werden die Zensuren-Typen verwaltet. Bei den Zensuren-Typen wird zwischen den beiden
            Kategorien: Kopfnote (z.B. Betragen, Mitarbeit, Fleiß usw.) und Leistungsüberprüfung
            (z.B. Klassenarbeit, Leistungskontrolle usw.) unterschieden.');

        $dataList = array();
        if (($tblGradeTypeAll = Grade::useService()->getGradeTypeAll(true))) {
            array_walk($tblGradeTypeAll, function (TblGradeType $tblGradeType) use (&$dataList) {
                $item['DisplayName'] = $tblGradeType->getIsHighlighted() ? new Bold($tblGradeType->getName()) : $tblGradeType->getName();
                $item['DisplayCode'] = $tblGradeType->getIsHighlighted() ? new Bold($tblGradeType->getCode()) : $tblGradeType->getCode();
                $category = $tblGradeType->getIsTypeBehavior() ? 'Kopfnote' : 'Leistungsüberprüfung';
                $item['Category'] = $tblGradeType->getIsHighlighted() ? new Bold($category) : $category;

                $item['Status'] = $tblGradeType->getIsActive()
                    ? new SuccessText(new PlusSign().' aktiv')
                    : new Warning(new MinusSign() . ' inaktiv');
                $item['Description'] = trim($tblGradeType->getDescription()
                    . ($tblGradeType->getIsPartGrade() ? new Italic(' (Teilnote)') : ''));
                $item['Option'] =
                    (new Standard('', '/Education/Graduation/Grade/GradeType/Edit', new Edit(), array(
                        'Id' => $tblGradeType->getId()
                    ), 'Zensuren-Typ bearbeiten'))
                    . ($tblGradeType->getIsActive()
                        ? (new Standard('', '/Education/Graduation/Grade/GradeType/Activate', new MinusSign(),
                            array('Id' => $tblGradeType->getId()), 'Deaktivieren'))
                        : (new Standard('', '/Education/Graduation/Grade/GradeType/Activate', new PlusSign(),
                            array('Id' => $tblGradeType->getId()), 'Aktivieren')))
                    . ($tblGradeType->getIsUsed()
                        ? ''
                        : (new Standard('', '/Education/Graduation/Grade/GradeType/Destroy', new Remove(),
                            array('Id' => $tblGradeType->getId()), 'Löschen')));

                $dataList[] = $item;
            });
        }

        $Form = $this->formGradeType()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($dataList, null, array(
                                'Status' => 'Status',
                                'Category' => 'Kategorie',
                                'DisplayName' => 'Name',
                                'DisplayCode' => 'Abk&uuml;rzung',
                                'Description' => 'Beschreibung',
                                'Option' => ''
                            ), array(
                                'order' => array(
                                    array('0', 'asc'),
                                    array('1', 'asc'),
                                    array('2', 'asc'),
                                ),
                                'columnDefs' => array(
                                    array('orderable' => false, 'targets' => -1),
                                ),
                            ))
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Well(Grade::useService()->createGradeType($Form, $GradeType))
                        )
                    ))
                ), new Title(new PlusSign() . ' Hinzufügen'))
            ))
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formGradeType(): Form
    {
        $typeList[1] = new SelectBoxItem(1, 'Leistungsüberprüfung');
        $typeList[2] = new SelectBoxItem(2, 'Kopfnote');

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new SelectBox('GradeType[Type]', 'Kategorie', array('Name' => $typeList)))->setRequired(), 3
                ),
                new FormColumn(
                    (new TextField('GradeType[Code]', 'LK', 'Abk&uuml;rzung'))->setRequired(), 3
                ),
                new FormColumn(
                    (new TextField('GradeType[Name]', 'Leistungskontrolle', 'Name'))->setRequired(), 6
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    new TextField('GradeType[Description]', '', 'Beschreibung'), 12
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    new CheckBox('GradeType[IsHighlighted]', 'Fett markiert', 1), 3
                ),
                new FormColumn(
                    new CheckBox('GradeType[IsPartGrade]', 'Teilnote (wird zu einer Note zusammengefasst)', 1), 9
                )
            )),
        )));
    }

    /**
     * @param null $Id
     * @param      $GradeType
     *
     * @return Stage|string
     */
    public function frontendEditGradeType($Id = null, $GradeType = null)
    {
        $Stage = new Stage('Zensuren-Typ', 'Bearbeiten');
        $tblGradeType = false;
        $error = false;
        if ($Id == null) {
            $error = true;
        } elseif (!($tblGradeType = Grade::useService()->getGradeTypeById($Id))) {
            $error = true;
        }
        if ($error) {
            return $Stage . new Danger('Zensuren-Typ nicht gefunden', new Ban())
                . new Redirect('/Education/Graduation/Grade/GradeType', Redirect::TIMEOUT_ERROR);
        }

        $Stage->addButton(
            new Standard('Zur&uuml;ck', '/Education/Graduation/Grade/GradeType', new ChevronLeft())
        );

        $Global = $this->getGlobal();
        if (!$Global->POST) {
            $Global->POST['GradeType']['Type'] = $tblGradeType->getIsTypeBehavior() ? 2 : 1;
            $Global->POST['GradeType']['Name'] = $tblGradeType->getName();
            $Global->POST['GradeType']['Code'] = $tblGradeType->getCode();
            $Global->POST['GradeType']['IsHighlighted'] = $tblGradeType->getIsHighlighted();
            $Global->POST['GradeType']['Description'] = $tblGradeType->getDescription();
            $Global->POST['GradeType']['IsPartGrade'] = $tblGradeType->getIsPartGrade();

            $Global->savePost();
        }

        $Form = $this->formGradeType()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel(
                                'Zensuren-Typ',
                                $tblGradeType->getName() . ' (' . $tblGradeType->getCode() . ')' .
                                ($tblGradeType->getDescription() !== '' ? '&nbsp;&nbsp;'
                                    . new Muted(new Small(new Small($tblGradeType->getDescription()))) : ''),
                                Panel::PANEL_TYPE_INFO
                            )
                        ),
                    ))
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Well(Grade::useService()->updateGradeType($Form, $Id, $GradeType))
                        ),
                    ))
                ), new Title(new Edit() . ' Bearbeiten'))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param bool|false $Confirm
     *
     * @return Stage|string
     */
    public function frontendDestroyGradeType(
        $Id = null,
        $Confirm = false
    ) {

        $Stage = new Stage('Zensuren-Type', 'Löschen');

        if (($tblGradeType = Grade::useService()->getGradeTypeById($Id))) {
            $Stage->addButton(
                new Standard('Zur&uuml;ck', '/Education/Graduation/Grade/GradeType', new ChevronLeft())
            );

            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(
                            'Zensuren-Typ',
                            $tblGradeType->getDisplayName() .'&nbsp;&nbsp;'.new Muted(new Small(new Small($tblGradeType->getDescription()))),
                            Panel::PANEL_TYPE_INFO
                        ),
                        new Panel(new Question().' Diesen Zensuren-Typ wirklich löschen?',
                            array(
                                $tblGradeType->getDisplayName(),
                                $tblGradeType->getDescription() ?: null
                            ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard('Ja', '/Education/Graduation/Grade/GradeType/Destroy', new Ok(), array('Id' => $Id, 'Confirm' => true))
                            .new Standard('Nein', '/Education/Graduation/Grade/GradeType', new Disable())
                        )
                    )))))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            (Grade::useService()->deleteGradeType($tblGradeType)
                                ? new Success(new SuccessIcon()
                                    . ' Der Zensuren-Typ wurde gelöscht')
                                : new Danger(new Ban() . ' Der Zensuren-Typ konnte nicht gelöscht werden')
                            ),
                            new Redirect('/Education/Graduation/Grade/GradeType', Redirect::TIMEOUT_SUCCESS)
                        )))
                    )))
                );
            }
        } else {
            return $Stage . new Danger('Zensuren-Typ nicht gefunden.', new Ban())
                . new Redirect('/Education/Graduation/Grade/GradeType', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return string
     */
    public function frontendActivateGradeType($Id = null): string
    {
        $Route = '/Education/Graduation/Grade/GradeType';

        $Stage = new Stage('Zensuren-Typ', 'Aktivieren/Deaktivieren');
        $Stage->addButton(
            new Standard('Zur&uuml;ck', $Route, new ChevronLeft())
        );

        if (($tblGradeType = Grade::useService()->getGradeTypeById($Id))) {
            $IsActive = !$tblGradeType->getIsActive();
            if ((Grade::useService()->updateGradeTypeActive($tblGradeType, $IsActive))) {

                return $Stage . new Success('Die Zensuren-Typ wurde ' . ($IsActive ? 'aktiviert.' : 'deaktiviert.'), new SuccessIcon())
                    . new Redirect($Route, Redirect::TIMEOUT_SUCCESS);
            } else {

                return $Stage . new Danger('Die Zensuren-Typ konnte nicht ' . ($IsActive ? 'aktiviert' : 'deaktiviert') . ' werden.', new Ban())
                    . new Redirect($Route, Redirect::TIMEOUT_ERROR);
            }
        } else {
            return $Stage . new Danger('Zensuren-Typ nicht gefunden.', new Ban())
                . new Redirect($Route, Redirect::TIMEOUT_ERROR);
        }
    }
}