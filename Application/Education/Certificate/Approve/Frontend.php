<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 18.07.2016
 * Time: 16:29
 */

namespace SPHERE\Application\Education\Certificate\Approve;

use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    public function frontendSelectPrepare($YearId = null)
    {

        $Stage = new Stage('Zeugnisvorbereitungen', 'Übersicht');

        $tblYearDisplayList = array();
        $tblYearList = Term::useService()->getYearAllSinceYears(2);
        if ($tblYearList) {
            foreach ($tblYearList as $item) {
                if (Prepare::useService()->getPrepareAllByYear($item)) {
                    $tblYearDisplayList[$item->getId()] = $item;
                }
            }
        }

        $tblYear = Term::useService()->getYearById($YearId);

        if (!empty($tblYearDisplayList)) {
            $tblYearDisplayList = $this->getSorter($tblYearDisplayList)->sortObjectBy('DisplayName');

            if (count($tblYearDisplayList) > 0) {
                /** @var TblYear $year */
                foreach ($tblYearDisplayList as $year) {
                    $Stage->addButton(new Standard(
                        $year->getDisplayName(),
                        '/Education/Certificate/Approve',
                        null,
                        array('YearId' => $year->getId())
                    ));
                }
            } else {
                $tblYear = current($tblYearDisplayList);
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(
                                'Schuljahr',
                                $tblYear ? $tblYear->getDisplayName() : new Warning(new Exclamation()
                                    . ' Bitte wählen Sie ein Schuljahr aus'),
                                Panel::PANEL_TYPE_INFO
                            ),
                        )),
                    ))
                ))
            ))
        );

        return $Stage;
    }
}