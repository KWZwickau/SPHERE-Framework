<?php
namespace SPHERE\Application\Api\Transfer\Indiware\IndiwareLog;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
// ToDO nach dem Indiware test wieder entfernen
class IndiwareLog implements IModuleInterface
{
    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Download', __CLASS__.'::downloadIndiwareLog',
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
     * @param string $fileName
     *
     * @return string
     */
    public function downloadIndiwareLog($fileName = '')
    {


//        $DirectoryContentList = scandir('UnitTest/IndiwareLog/');
//        var_dump($DirectoryContentList);
//        exit;
        $File = 'UnitTest/IndiwareLog/'.$fileName;
        if ($File) {
            echo '<pre>';
            readFile($File);
            echo '</pre>';
            exit;
//            return FileSystem::getDownload($filePath,$fileName)->__toString();
        }
        return 'Fehler beim Download.';
    }
}