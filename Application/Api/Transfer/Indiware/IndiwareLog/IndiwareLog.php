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
        $file = 'UnitTest/IndiwareLog/'.$fileName;
        if (file_exists($file)) {
            // Setze die Header für den Dateidownload
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));

            // Datei lesen und ausgeben
            readfile($file);
            exit;
        } else {
            return 'Datei nicht gefunden!';
        }
        return 'Fehler beim Download.';
    }
}