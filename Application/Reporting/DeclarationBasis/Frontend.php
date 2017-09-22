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
        $tblYearList = false;
        $YearList = Term::useService()->getYearByNow();
        if ($YearList) {
            $YearString = current($YearList)->getYear();
            // get Years that not now but have same YearString
            $tblYearList = Term::useService()->getYearsByYear(current($YearList));
        }

        $Stage = new Stage('Stichtagsmeldung', 'Aktuelles Schuljahr: '.$YearString);
        if ($tblYearList) {
            $Stage->addButton(
                new Primary('Herunterladen',
                    '/Api/Reporting/DeclarationBasis/Download', new Download())
            );
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