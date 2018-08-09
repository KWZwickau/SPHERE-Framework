<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 20.09.2016
 * Time: 08:37
 */

namespace SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount;

use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblMinimumGradeCount;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Quantity;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
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
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $MinimumGradeCount
     *
     * @return Stage
     */
    public function frontendMinimumGradeCount($MinimumGradeCount = null)
    {

        $Stage = new Stage('Mindestnotenanzahl', 'Übersicht');
        $Stage->setMessage(
            'Hier werden die Mindestnotenanzahlen verwaltet.' . '<br>' .
            'Es muss mindestens eine Klassenstufe vergeben werden für diese dann die Mindestnotenanzahl berücksichtigt wird.' . '<br>' .
            'Optional kann die Mindestnotenanzahl auf ein Fach und/oder ein Zensuren-Typ beschränkt werden.'
        );

        $tblMinimumGradeCountAll = Gradebook::useService()->getMinimumGradeCountAll();

        $TableContent = array();
        if ($tblMinimumGradeCountAll) {
            array_walk($tblMinimumGradeCountAll,
                function (TblMinimumGradeCount $tblMinimumGradeCount) use (&$TableContent) {
                    array_push($TableContent, array(
                        'SchoolType' => $tblMinimumGradeCount->getSchoolTypeDisplayName(),
                        'Level' => $tblMinimumGradeCount->getLevelDisplayName(),
                        'Subject' => $tblMinimumGradeCount->getSubjectDisplayName(),
                        'GradeType' => $tblMinimumGradeCount->getGradeTypeDisplayName(),
                        'Period' => $tblMinimumGradeCount->getPeriodDisplayName(),
                        'Count' => $tblMinimumGradeCount->getCount(),
                        'Option' => (new Standard('', '/Education/Graduation/Gradebook/MinimumGradeCount/Edit',
                                new Edit(), array(
                                    'Id' => $tblMinimumGradeCount->getId()
                                ), 'Bearbeiten'))
                            . (new Standard('', '/Education/Graduation/Gradebook/MinimumGradeCount/Destroy',
                                new Remove(),
                                array('Id' => $tblMinimumGradeCount->getId()), 'Löschen'))
                    ));
                });
        }

        $Form = $this->formCreateMinimumGradeCount()
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($TableContent, null,
                                array(
                                    'SchoolType' => 'Schulart',
                                    'Level' => 'Klassenstufe',
                                    'Subject' => 'Fach',
                                    'GradeType' => 'Zensuren-Typ',
                                    'Period' => 'Zeitraum',
                                    'Count' => 'Anzahl',
                                    'Option' => ''
                                ),
                                array(
                                    'order' => array(
                                        array('0', 'asc'),
                                        array('1', 'asc'),
                                        array('2', 'asc'),
                                        array('3', 'asc')
                                    ),
                                    'columnDefs' => array(
                                        array('orderable' => false, 'targets' => -1),
                                    ),
                                )
                            )
                        ))
                    )),
                ), new Title(new Listing() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Well(Gradebook::useService()->createMinimumGradeCount($Form,
                                $MinimumGradeCount)
                            ))
                    ))
                ), new Title(new PlusSign() . ' Hinzufügen'))
            ))
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formCreateMinimumGradeCount()
    {

        $tblLevelAll = Division::useService()->getLevelAll();
        $tblSubjectAll = Subject::useService()->getSubjectAll();
        if (($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST'))) {
            $tblGradeTypeList = Gradebook::useService()->getGradeTypeAllByTestType($tblTestType);
        } else {
            $tblGradeTypeList = array();
        }

        $periodList[] = new SelectBoxItem(SelectBoxItem::PERIOD_FULL_YEAR, '-Gesamtes Schuljahr-');
        $periodList[] = new SelectBoxItem(SelectBoxItem::PERIOD_FIRST_PERIOD, '1. Halbjahr');
        $periodList[] = new SelectBoxItem(SelectBoxItem::PERIOD_SECOND_PERIOD, '2. Halbjahr');

        $highLightedList[] = new SelectBoxItem(SelectBoxItem::HIGHLIGHTED_ALL, 'Alle Zensuren-Typen');
        $highLightedList[] = new SelectBoxItem(SelectBoxItem::HIGHLIGHTED_IS_HIGHLIGHTED, 'Nur große Zensuren-Typen (Fett marktiert)');
        $highLightedList[] = new SelectBoxItem(SelectBoxItem::HIGHLIGHTED_IS_NOT_HIGHLIGHTED, 'Nur kleine Zensuren-Typen (nicht Fett markiert)');

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new Panel(
                        'Klassenstufe und Fach',
                        array(
                            new SelectBox('MinimumGradeCount[Level]', 'Schulart - Klassenstufe '
                                . new DangerText('*'),
                                array('{{ serviceTblType.Name }} - {{ Name }}' => $tblLevelAll)),
                            new SelectBox('MinimumGradeCount[Subject]', 'Fach',
                                array('{{ Acronym }} - {{ Name }}' => $tblSubjectAll))
                        ),
                        Panel::PANEL_TYPE_INFO
                    ), 4
                ),
                new FormColumn(
                    new Panel(
                        'Zensuren-Typen',
                        array(
                            new SelectBox('MinimumGradeCount[GradeType]', 'Zensuren-Typ',
                                array('{{ Code }} - {{ Name }}' => $tblGradeTypeList)),
                            new SelectBox('MinimumGradeCount[Highlighted]', 'oder Zensuren-Typen beschränken',
                                array('{{ Name }}' => $highLightedList))
                        ),
                        Panel::PANEL_TYPE_INFO
                    ), 4
                ),
                new FormColumn(
                    new Panel(
                        'Zeitraum und Anzahl',
                        array(
                            new SelectBox('MinimumGradeCount[Period]', 'Zeitraum',
                                array('{{ Name }}' => $periodList)),
                            new NumberField('MinimumGradeCount[Count]', '',
                                'Anzahl ' . new DangerText('*'), new Quantity())
                        ),
                        Panel::PANEL_TYPE_INFO
                    ), 4
                ),
            )),
            new FormRow(array(
                new FormColumn(array(
                    new DangerText(new Primary('Speichern', new Save()) . ' * Pflichtfeld')
                )),
            ))
        )));
    }

    /**
     * @param null $Id
     * @param null $Count
     * @return Stage|string
     */
    public function frontendEditMinimumGradeCount($Id = null, $Count = null)
    {

        $Stage = new Stage('Mindestnotenanzahl', 'Bearbeiten');
        $Stage->addButton(
            new Standard('Zurück', '/Education/Graduation/Gradebook/MinimumGradeCount', new ChevronLeft())
        );

        $tblMinimumGradeCount = Gradebook::useService()->getMinimumGradeCountById($Id);
        if ($tblMinimumGradeCount) {
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Count'] = $tblMinimumGradeCount->getCount();
                $Global->savePost();
            }

            $tblLevel = $tblMinimumGradeCount->getServiceTblLevel();
            if ($tblLevel) {
                $tblSchoolType = $tblLevel->getServiceTblType();
            } else {
                $tblSchoolType = false;
            }
            $tblSubject = $tblMinimumGradeCount->getServiceTblSubject();

            $Form = $this->formEditMinimumGradeCount()
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Schulart',
                                    $tblSchoolType ? $tblSchoolType->getName() : '',
                                    Panel::PANEL_TYPE_INFO
                                ), 3
                            ),
                            new LayoutColumn(
                                new Panel(
                                    'Klassenstufe',
                                    $tblLevel ? $tblLevel->getName() : '',
                                    Panel::PANEL_TYPE_INFO
                                ), 3
                            ),
                            $tblSubject ? new LayoutColumn(
                                new Panel(
                                    'Fach',
                                    $tblSubject->getAcronym() . ' - ' . $tblSubject->getName(),
                                    Panel::PANEL_TYPE_INFO
                                ), 3
                            ) : null,
                            new LayoutColumn(
                                new Panel(
                                    'Zensuren-Typ',
                                    $tblMinimumGradeCount->getGradeTypeDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ), 3
                            ),
                            new LayoutColumn(
                                new Panel(
                                    'Zeitraum',
                                    $tblMinimumGradeCount->getPeriodDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ), 3
                            )
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Well(Gradebook::useService()->updateMinimumGradeCount($Form, $tblMinimumGradeCount,
                                    $Count)
                                )),
                        ))
                    ), new Title(new Edit() . ' Bearbeiten'))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger(new Ban() . ' Mindestnotenanzahl nicht gefunden')
            . new Redirect('/Education/Graduation/Gradebook/MinimumGradeCount', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @return Form
     */
    private function formEditMinimumGradeCount()
    {

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new NumberField('Count', '', 'Anzahl ' . new DangerText('*'), new Quantity())
                ),
            )),
            new FormRow(array(
                new FormColumn(array(
                    new DangerText(new Primary('Speichern', new Save()) . ' * Pflichtfeld')
                )),
            ))
        )));
    }

    /**
     * @param null $Id
     * @param bool|false $Confirm
     *
     * @return Stage|string
     */
    public function frontendDestroyMinimumGradeCount(
        $Id = null,
        $Confirm = false
    ) {

        $Stage = new Stage('Mindesnotenanzahl', 'Löschen');

        $tblMinimumGradeCount = Gradebook::useService()->getMinimumGradeCountById($Id);
        if ($tblMinimumGradeCount) {
            $Stage->addButton(
                new Standard('Zur&uuml;ck', '/Education/Graduation/Gradebook/MinimumGradeCount', new ChevronLeft())
            );

            if (!$Confirm) {

                $tblLevel = $tblMinimumGradeCount->getServiceTblLevel();
                if ($tblLevel) {
                    $tblSchoolType = $tblLevel->getServiceTblType();
                } else {
                    $tblSchoolType = false;
                }
                $tblSubject = $tblMinimumGradeCount->getServiceTblSubject();
                $tblGradeType = $tblMinimumGradeCount->getTblGradeType();

                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new Question() . ' Diese Mindestnotenanzahl wirklich löschen?',
                            array(
                                $tblSchoolType ? 'Schulart: ' . $tblSchoolType->getName() : null,
                                $tblLevel ? 'Klassenstufe: ' . $tblLevel->getName() : null,
                                $tblSubject ? 'Fach: ' . $tblSubject->getAcronym() . ' - ' . $tblSubject->getName() : null,
                                $tblGradeType ? 'Zensuren-Typ: ' . $tblGradeType->getCode() . ' - ' . $tblGradeType->getName() : null,
                            ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Education/Graduation/Gradebook/MinimumGradeCount/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            . new Standard(
                                'Nein', '/Education/Graduation/Gradebook/MinimumGradeCount', new Disable())
                        )
                    )))))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            (Gradebook::useService()->destroyMinimumGradeCount($tblMinimumGradeCount)
                                ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                                    . ' Die Mindestnotenanzahl wurde gelöscht')
                                : new Danger(new Ban() . ' Die Mindestnotenanzahl konnte nicht gelöscht werden')
                            ),
                            new Redirect('/Education/Graduation/Gradebook/MinimumGradeCount', Redirect::TIMEOUT_SUCCESS)
                        )))
                    )))
                );
            }
        } else {
            return $Stage . new Danger('Mindestnotenanzahl nicht gefunden.', new Ban())
            . new Redirect('/Education/Graduation/Gradebook/MinimumGradeCount', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }
}