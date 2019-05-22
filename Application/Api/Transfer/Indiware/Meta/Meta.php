<?php


namespace SPHERE\Application\Api\Transfer\Indiware\Meta;


use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Transfer\Indiware\Export\Meta\Meta as MetaApp;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;

class Meta implements IModuleInterface
{
    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Download', __CLASS__.'::downloadMeta'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // Implement useFrontend() method.
    }

    /**
     * @param string $DivisionId
     *
     * @return bool|string
     */
    public function downloadMeta($DivisionId = '')
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if($tblDivision){
            $fileLocation = MetaApp::useService()->createCsv($DivisionId);
            if ($fileLocation) {
                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Grunddaten Schüler Klasse ".$tblDivision->getDisplayName().".csv")->__toString();
            }
        }
        return false;

    }
}