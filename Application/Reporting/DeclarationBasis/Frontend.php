<?php

namespace SPHERE\Application\Reporting\DeclarationBasis;

use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
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

        $YearString = new Bold('Kein aktuelles Jahr gefunden');
        $YearList = Term::useService()->getYearByNow();
        $tblYear = false;
        if ($YearList) {
            $tblYear = current($YearList);
            $YearString = $tblYear->getYear();
        }

        if (($tblFutureYearList = Term::useService()->getYearAllFutureYears(1))) {
            $tblFutureYear = current($tblFutureYearList);
        } else {
            $tblFutureYear = false;
        }

        $Stage = new Stage('Stichtagsmeldung', 'Aktuelles Schuljahr: '.$YearString);
        if ($tblYear) {
            $Stage->addButton(
                new Primary(
                    'Herunterladen für das aktuelle Schuljahr ' . $tblYear->getYear(),
                    '/Api/Reporting/DeclarationBasis/Download',
                    new Download(),
                    array(
                        'YearId' => $tblYear->getId()
                    )
                )
            );

            if ($tblFutureYear) {
                $Stage->addButton(
                    new Primary(
                        'Herunterladen für das nächste Schuljahr ' . $tblFutureYear->getYear(),
                        '/Api/Reporting/DeclarationBasis/Download',
                        new Download(),
                        array(
                            'YearId' => $tblFutureYear->getId()
                        )
                    )
                );
            }
        } else {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Warning('Kein Schuljahr besitzt einen Zeitraum der das aktuelle Datum einschließt')
                            )
                        )
                    )
                )
            );
        }

        return $Stage;
    }
}