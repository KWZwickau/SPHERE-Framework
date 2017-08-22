<?php

namespace SPHERE\Application\Reporting\Individual;

use SPHERE\Application\Api\Reporting\Individual\ApiIndividual;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Individual
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Flexible Auswertung', '');

        // $Content = Individual::useService()->getView();

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            ApiIndividual::receiverService(),
                            ApiIndividual::receiverModal(),
                            ApiIndividual::receiverNavigation(),
                            ApiIndividual::pipelineNavigation(false)
                        ), 3),
                        new LayoutColumn(
                            new Layout(
                                new LayoutGroup(
                                    new LayoutRow(array(
                                        new LayoutColumn(
                                            new Title('Filteroptionen')
                                        ),
                                        new LayoutColumn(array(
                                            ApiIndividual::receiverFilter(),
                                            ApiIndividual::pipelineDisplayFilter()
                                        )),
                                        new LayoutColumn(new Title('Suchergebnis')),
                                        new LayoutColumn(ApiIndividual::receiverResult()),
                                    ))
                                )
                            )
                            , 9)
                    ))
                )
            ))
        );

        return $Stage;
    }
}
