<?php
namespace SPHERE\Application\Education\Lesson\Term;

use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Lesson\Term
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null|array $Year
     *
     * @return Stage
     */
    public function frontendCreateYear($Year = null)
    {

        $Stage = new Stage('Schuljahre', 'erstellen / bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Term', new ChevronLeft()));

        $tblYearAll = Term::useService()->getYearAll();
        if ($tblYearAll) {
            array_walk($tblYearAll, function (TblYear &$tblYear) {

                $tblPeriodAll = $tblYear->getTblPeriodAll();
                $tblYear->Option =
                    new Standard('', __NAMESPACE__.'\Edit\Year', new Pencil(),
                        array('Id' => $tblYear->getId())
                    ).
                    ( empty( $tblPeriodAll )
                        ? new Standard('', __NAMESPACE__.'\Destroy\Year', new Remove(),
                            array('Id' => $tblYear->getId())
                        ) : ''
                    );
            });
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($tblYearAll, null, array(
                                'Name' => 'Name',
                                'Description' => 'Beschreibung',
                                'Option' => 'Option',
                            ))
                        )
                    ), new Title('Bestehende Schuljahre')
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            Term::useService()->createYear(
                                $this->formYear()
                                    ->appendFormButton(new Primary('Schuljahr erstellen'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $Year
                            )
                        )
                    ), new Title('Schuljahr erstellen')
                ),
            ))
        );

        return $Stage;
    }

    /**
     * @param null|TblYear $tblYear
     *
     * @return Form
     */
    public function formYear(TblYear $tblYear = null)
    {

        $tblYearAll = Term::useService()->getYearAll();
        $acNameAll = array();
        if ($tblYearAll) {
            array_walk($tblYearAll, function (TblYear $tblYear) use (&$acNameAll) {

                if (!in_array($tblYear->getName(), $acNameAll)) {
                    array_push($acNameAll, $tblYear->getName());
                }
            });
        }

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Year'] ) && $tblYear) {
            $Global->POST['Year']['Name'] = $tblYear->getName();
            $Global->POST['Year']['Description'] = $tblYear->getDescription();
            $Global->savePost();
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Schuljahr',
                            array(
                                new AutoCompleter('Year[Name]', 'Name', 'z.B: '.date('Y').'/'.( date('Y') + 1 ).' Gymnasium',
                                    $acNameAll, new Pencil()),
                                new TextField('Year[Description]', 'z.B: für Gymnasium', 'Beschreibung',
                                    new Pencil())
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                )),
            ))
        );
    }

    /**
     * @param null|array $Period
     *
     * @return Stage
     */
    public function frontendCreatePeriod($Period = null)
    {

        $Stage = new Stage('Zeiträume', 'erstellen / bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Term', new ChevronLeft()));

        $tblPeriodAll = Term::useService()->getPeriodAll();
        if ($tblPeriodAll) {
            array_walk($tblPeriodAll, function (TblPeriod &$tblPeriod) {

                $tblPeriod->Period = $tblPeriod->getFromDate().' - '.$tblPeriod->getToDate();

                $tblPeriod->Option =
                    new Standard('', __NAMESPACE__.'\Edit\Period', new Pencil(),
                        array('Id' => $tblPeriod->getId()))
                    .( ( Term::useService()->getPeriodExistWithYear($tblPeriod) === false ) ?
                        new Standard('', __NAMESPACE__.'\Destroy\Period', new Remove(),
                            array('Id' => $tblPeriod->getId()))
                        : '' );
            });
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            Term::useService()->createPeriod(
                                $this->formPeriod()
                                    ->appendFormButton(new Primary('Zeitraum erstellen'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $Period
                            )
                        )
                    ), new Title('Zeitraum erstellen')
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($tblPeriodAll, null, array(
                                'Name'        => 'Name',
                                'Description' => 'Beschreibung',
                                'Period'      => 'Zeitraum',
                                'Option'      => 'Optionen',
                            ))
                        )
                    ), new Title('Bestehende Zeiträume')
                )
            ))
        );

        return $Stage;
    }

    /**
     * @param null|TblPeriod $tblPeriod
     *
     * @return Form
     */
    public function formPeriod(TblPeriod $tblPeriod = null)
    {

        $tblPeriodAll = Term::useService()->getPeriodAll();
        $acNameAll = array();
        if ($tblPeriodAll) {
            array_walk($tblPeriodAll, function (TblPeriod $tblPeriod) use (&$acNameAll) {

                if (!in_array($tblPeriod->getName(), $acNameAll)) {
                    array_push($acNameAll, $tblPeriod->getName());
                }
            });
        }

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Period'] ) && $tblPeriod) {
            $Global->POST['Period']['Name'] = $tblPeriod->getName();
            $Global->POST['Period']['Description'] = $tblPeriod->getDescription();
            $Global->POST['Period']['From'] = $tblPeriod->getFromDate();
            $Global->POST['Period']['To'] = $tblPeriod->getToDate();
            $Global->savePost();
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Zeitraum',
                            array(
                                new AutoCompleter('Period[Name]', 'Name', 'z.B: 1.Halbjahr',
                                    $acNameAll, new Pencil()),
                                new TextField('Period[Description]', 'z.B: für Gymnasium', 'Beschreibung',
                                    new Pencil())
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        new Panel('Datum',
                            array(
                                new DatePicker('Period[From]', 'Beginn', 'Von', new Calendar()),
                                new DatePicker('Period[To]', 'Ende', 'Bis', new Calendar()),
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                )),
            ))
        );
    }

    /**
     * @param $Id
     * @param $PeriodId
     *
     * @return Stage
     */
    public function frontendChoosePeriod($Id, $PeriodId)
    {

        $Stage = new Stage('Zeitraum', 'wählen');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Term', new ChevronLeft()));
        if (isset( $PeriodId )) {
            $Stage->setContent(Term::useService()->addYearPeriod($Id, $PeriodId));
        } else {
            $tblYear = Term::useService()->getYearById($Id);
            $tblPeriodList = Term::useService()->getPeriodAll();
            $tblPeriodListUses = Term::useService()->getPeriodAllByYear($tblYear);

            if (!empty( $tblPeriodListUses )) {
                $tblPeriodList = array_udiff($tblPeriodList, $tblPeriodListUses,
                    function (TblPeriod $ObjectA, TblPeriod $ObjectB) {

                        return $ObjectA->getId() - $ObjectB->getId();
                    }
                );
            }

            foreach ($tblPeriodList as &$tblPeriod) {
                $tblPeriod->Option = new Standard('', '/Education/Lesson/Term/Choose/Period', new Select(),
                    array('Id'       => $tblYear->getId(),
                          'PeriodId' => $tblPeriod->getId()), 'Auswählen');
                $tblPeriod->Period = $tblPeriod->getFromDate().' - '.$tblPeriod->getToDate();
            }

            if ($tblYear) {
                if ($tblPeriodList) {
                    $Stage->setContent($this->layoutYear($tblYear)
                        .new Layout(
                            new LayoutGroup(
                                new LayoutRow(
                                    new LayoutColumn(
                                        new TableData($tblPeriodList, null,
                                            array('Id'          => 'lfd.Nr.',
                                                  'Name'        => 'Name',
                                                  'Description' => 'Beschreibung',
                                                  'Period'      => 'Zeitraum',
                                                  'Option'      => 'Option')
                                        )
                                    )
                                )
                            )
                        ));
                } else {
                    $Stage->setContent(new Warning('Keine weiteren Zeiträume verfügbar!'));
                }
            } else {
                $Stage->setContent(new Warning('Schuljahr nicht gefunden!'));
            }
        }
        return $Stage;
    }

    /**
     * @param TblYear $tblYear
     *
     * @return bool|Layout
     */
    public function layoutYear(
        TblYear $tblYear
    ) {

        if ($tblYear) {
            $Panel = new Panel($tblYear->getDescription().'&nbsp'.new PullRight($tblYear->getName()), '', Panel::PANEL_TYPE_INFO);
            return new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn($Panel, 6))));
        }
        return false;
    }

    /**
     * @param $PeriodId
     * @param $Id
     *
     * @return \SPHERE\Common\Frontend\Message\Repository\Success
     */
    public function frontendRemovePeriod($PeriodId, $Id)
    {

        $Stage = new Stage('Zeitraum', 'entfernen');
        $Stage->setContent(Term::useService()->removeYearPeriod($Id, $PeriodId));
        return $Stage;
    }

    /**
     * @param $Id
     * @param $Year
     *
     * @return Stage
     */
    public function frontendEditYear($Id, $Year)
    {

        $Stage = new Stage('Jahr', 'bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Term/Create/Year', new ChevronLeft()));
        $tblYear = Term::useService()->getYearById($Id);

        if ($tblYear) {
            $Form = $this->formYear($tblYear)
                ->appendFormButton(new Primary('Änderungen speichern'))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(array(
                                new Headline('Fach bearbeiten (' . $tblYear->getName() . ' ' . $tblYear->getDescription() . ')'),
                                Term::useService()->changeYear($Form, $tblYear, $Year),
                            ))
                        )
                    )
                )
            );
//            $Stage->setContent( Term::useService()->changeYear($this->formYear($tblYear)
//            ->appendFormButton(new Primary('Änderungen speichern'))
//            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
//            ,$tblYear, $Year)
//            );
        } else {
            $Stage->setContent(new Warning('Jahr nicht gefunden!'));
        }
        return $Stage;
    }

    public function frontendDestroyYear($Id)
    {

        $Stage = new Stage('Jahr', 'Entfernen');
        $tblYear = Term::useService()->getYearById($Id);
        if ($tblYear) {
            $Stage->setContent(Term::useService()->destroyYear($tblYear));
        } else {
            return $Stage.new Warning('Jahr nicht gefunden!')
            .new Redirect('/Education/Lesson/Term/Create/Year');
        }
        return $Stage;
    }

    /**
     * @param $Id
     * @param $Period
     *
     * @return Stage
     */
    public function frontendEditPeriod($Id, $Period)
    {

        $Stage = new Stage('Zeitraum', 'bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Term/Create/Period', new ChevronLeft()));
        $tblPeriod = Term::useService()->getPeriodById($Id);

        if ($tblPeriod) {
            $Form = $this->formPeriod($tblPeriod)
                ->appendFormButton(new Primary('Änderungen speichern'))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->setContent(Term::useService()->changePeriod($Form, $tblPeriod, $Period));
        } else {
            $Stage->setContent(new Warning('Zeitraum nicht gefunden!'));
        }
        return $Stage;
    }

    public function frontendDestroyPeriod($Id)
    {

        $Stage = new Stage('Zeitraum', 'Entfernen');
        $tblPeriod = Term::useService()->getPeriodById($Id);
        if ($tblPeriod) {
            $Stage->setContent(Term::useService()->destroyPeriod($tblPeriod));
        } else {
            return $Stage.new Warning('Zeitraum nicht gefunden!')
            .new Redirect('/Education/Lesson/Term/Create/Period');
        }
        return $Stage;
    }
}
