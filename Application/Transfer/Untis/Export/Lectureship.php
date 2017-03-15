<?php
namespace SPHERE\Application\Transfer\Untis\Export;

use SPHERE\Common\Main;
use SPHERE\Common\Window\Stage;

/**
 * Class Lectureship
 * @package SPHERE\Application\Transfer\Untis\Import
 */
class Lectureship extends Export
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Lectureship', __CLASS__ . '::frontendDownload'
        ));

        parent::registerModule();
    }
    /**
     * @return Stage
     */
    public function frontendDownload()
    {

        $Stage = new Stage('Untis', 'Daten exportieren');

        $Stage->setMessage('Lehraufträge exportieren');

        return $Stage;
    }
}