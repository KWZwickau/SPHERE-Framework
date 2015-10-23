<?php
namespace SPHERE\Application\Education\Lesson\Term;

use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
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

        $Stage = new Stage('Schuljahre', 'Bearbeiten');

        $tblYearAll = Term::useService()->getYearAll();
        if ($tblYearAll) {
            array_walk($tblYearAll, function (TblYear &$tblYear) {

                $tblPeriodAll = $tblYear->getTblPeriodAll();
                $tblYear->Option = new Standard('', '', new Pencil(), array(), 'Bearbeiten')
                    .( empty( $tblPeriodAll )
                        ? new Standard('', '', new Remove(), array(), 'Löschen')
                        : ''
                    );
            });
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($tblYearAll, null, array(
                                'Name'        => 'Name',
                                'Description' => 'Beschreibung',
                                'Option'      => 'Optionen',
                            ))
                        )
                    ), new Title('Bestehende Schuljahre')
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            Term::useService()->createYear(
                                $this->formYear()
                                    ->appendFormButton(new Primary('Schuljahr hinzufügen'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $Year
                            )
                        )
                    ), new Title('Schuljahr hinzufügen')
                )
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
        $acAcronymAll = array();
        $acNameAll = array();
        if ($tblYearAll) {
            array_walk($tblYearAll, function (TblYear $tblYear) use (&$acAcronymAll, &$acNameAll) {

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
                                new AutoCompleter('Year[Name]', 'Name', 'z.B: '.date('Y').'/'.( date('Y') + 1 ),
                                    $acNameAll),
                                new TextField('Year[Description]', 'zb: für Gymnasium', 'Beschreibung',
                                    new Pencil())

                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                )),
            ))
        );
    }
}
