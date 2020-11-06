<?php

namespace SPHERE\Application\Reporting\DeclarationBasis;

use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Reporting\DeclarationBasis
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendDeclarationBasis()
    {

//        $YearString = new Bold('Kein aktuelles Jahr gefunden');
//        $YearList = Term::useService()->getYearByNow();
//        $tblYear = false;
//        if ($YearList) {
//            $tblYear = current($YearList);
//            $YearString = $tblYear->getYear();
//        }
//
//        if (($tblFutureYearList = Term::useService()->getYearAllFutureYears(1))) {
//            $tblFutureYear = current($tblFutureYearList);
//        } else {
//            $tblFutureYear = false;
//        }

//        $Stage = new Stage('Stichtagsmeldung', 'Aktuelles Schuljahr: '.$YearString);
//        if ($tblYear) {
//            $Stage->addButton(
//                new Standard(
//                    'Herunterladen für das aktuelle Schuljahr ' . $tblYear->getYear(),
//                    '/Api/Reporting/DeclarationBasis/Download',
//                    new Download(),
//                    array(
//                        'YearId' => $tblYear->getId()
//                    )
//                )
//            );
//
//            if ($tblFutureYear) {
//                $Stage->addButton(
//                    new Standard(
//                        'Herunterladen für das nächste Schuljahr ' . $tblFutureYear->getYear(),
//                        '/Api/Reporting/DeclarationBasis/Download',
//                        new Download(),
//                        array(
//                            'YearId' => $tblFutureYear->getId()
//                        )
//                    )
//                );
//            }
//        } else {
//            $Stage->setContent(
//                new Layout(
//                    new LayoutGroup(
//                        new LayoutRow(
//                            new LayoutColumn(
//                                new Warning('Kein Schuljahr besitzt einen Zeitraum der das aktuelle Datum einschließt')
//                            )
//                        )
//                    )
//                )
//            );
//        }

        $form = $this->getForm();

        $Stage = new Stage('Stichtagsmeldung', 'Datum auswählen');
        $Stage->setContent(new Well(
            $form
        ));

        return $Stage;
    }

    /**
     * @param null $Date
     *
     * @return Form
     */
    public function getForm($Date = null)
    {
        if ($Date) {
            $global = $this->getGlobal();
            $global->POST['Date'] = $Date;
            $global->savePost();
        }

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new DatePicker('Date', 'Stichtag', 'Stichtag', new Calendar()))->setRequired()
                    , 3)
            )),
        ))
        , new Primary('Herunterladen', new Download(), true), '\Api\Reporting\DeclarationBasis\Download');
    }
}