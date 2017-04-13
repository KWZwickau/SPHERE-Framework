<?php

namespace SPHERE\Application\Transfer\Indiware\Export;

use SPHERE\Common\Main;
use SPHERE\Common\Window\Stage;

/**
 * Class Lectureship
 * @package SPHERE\Application\Transfer\Indiware\Import
 */
class Lectureship extends Export
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Lectureship', __CLASS__.'::frontendDownload'
        ));

        parent::registerModule();
    }

    /**
     * @return Stage
     */
    public function frontendDownload()
    {

        $Stage = new Stage('Indiware', 'Daten exportieren');

        $Stage->setMessage('Lehraufträge exportieren');

        return $Stage;
    }
}