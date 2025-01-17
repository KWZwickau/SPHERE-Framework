<?php
namespace SPHERE\Application\Api\Transfer\ItsLearning;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Setting\ItsLearning\ItsLearning as ItsLearningApp;
use SPHERE\Common\Main;

class ItsLearning implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/StudentCustody/Download',
            __NAMESPACE__.'/ItsLearning::downloadStudentCustodyList'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Teacher/Download',
            __NAMESPACE__.'/ItsLearning::downloadTeacherList'
        ));
    }

    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @return string
     */
    public function downloadStudentCustodyList($Year)
    {

        $fileLocation = ItsLearningApp::useService()->downloadStudentCustodyCSV($Year);
        if($fileLocation){
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "itslearning_Schüler_Sorgeberechtigte.csv")->__toString();
        }
        return 'Es konnte keine Datei erzeugt werden.';
    }

    /**
     * @return string
     */
    public function downloadTeacherList()
    {

        $fileLocation = ItsLearningApp::useService()->downloadTeacherCSV();
        if($fileLocation){
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "itslearning_Lehrer.csv")->__toString();
        }
        return 'Es konnte keine Datei erzeugt werden.';
    }

}