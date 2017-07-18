<?php

namespace SPHERE\Application\Reporting\Individual;

use SPHERE\Application\Api\Reporting\Individual\ApiIndividual;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
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

        $Stage = new Stage('Flexible Auswertung');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            ApiIndividual::receiverNavigation(ApiIndividual::pipelineNavigation())
                            , 3),
                        new LayoutColumn(
                            'Filter <br/><hr/> TableResult'
                            , 9)
                    ))
                )
            )
        );

        return $Stage;
    }
}
