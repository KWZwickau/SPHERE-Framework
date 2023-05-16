<?php

namespace SPHERE\Application\Api\Reporting\DeclarationBasis;

use DateTime;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Common\Main;

/**
 * Class DeclarationBasis
 * @package SPHERE\Application\Api\Reporting\DeclarationBasis
 */
class DeclarationBasis implements IModuleInterface
{
    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__.'/Download', __CLASS__.'::downloadDivisionReport'));
    }

    public static function useService()
    {
        // Implement useService() method.
    }

    public static function useFrontend()
    {
        // Implement useFrontend() method.
    }

    /**
     * @param null $Date
     *
     * @return string
     */
    public function downloadDivisionReport($Date = null)
    {
        if ($Date != null) {
            $date = new DateTime($Date);
            if (Term::useService()->getYearAllByDate($date)) {
                $fileLocation = \SPHERE\Application\Reporting\DeclarationBasis\DeclarationBasis::useService()->createDivisionReportExcel($date);
                return FileSystem::getDownload($fileLocation->getRealPath(), "Stichtagsmeldung Integrationsschüler"
                        . (Consumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_SACHSEN) ? " SBA " : " ")
                        . $date->format('Y-m-d') . ".xlsx")->__toString();
            } else {
                return 'Für den Stichtag: ' . $date->format('d.m.Y') . ' wurde kein Schuljahr gefunden.';
            }
        }
        return 'Schuljahr nicht gefunden!';
    }
}