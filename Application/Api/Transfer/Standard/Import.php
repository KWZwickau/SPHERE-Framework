<?php
namespace SPHERE\Application\Api\Transfer\Standard;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;

class Import implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/DownloadTemplateStudent',
            __NAMESPACE__.'\Import::downloadTemplateStudent'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/DownloadTemplateTeacher',
            __NAMESPACE__.'\Import::downloadTemplateTeacher'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/DownloadTemplateSchool',
            __NAMESPACE__.'\Import::downloadTemplateSchool'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/DownloadTemplateInteressent',
            __NAMESPACE__.'\Import::downloadTemplateInteressent'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/DownloadTemplateMail',
            __NAMESPACE__.'\Import::downloadTemplateMail'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/DownloadTemplateHoliday',
            __NAMESPACE__.'\Import::downloadTemplateHoliday'
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

    public function downloadTemplateStudent()
    {

        $file = "Common/Style/Resource/Template/Import_Schueler.xlsx";
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename=Schüler_Import.xlsx");
        header("Content-Length: ". filesize($file));
        readfile($file);
    }

    public function downloadTemplateTeacher()
    {

        $file = "Common/Style/Resource/Template/Import_Lehrer_Mitarbeiter.xlsx";
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename=Lehrer_Mitarbeiter_Import.xlsx");
        header("Content-Length: ". filesize($file));
        readfile($file);
    }

    public function downloadTemplateSchool()
    {

        $file = "Common/Style/Resource/Template/Import_Schulen.xlsx";
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename=Schulen_Import.xlsx");
        header("Content-Length: ". filesize($file));
        readfile($file);
    }

    public function downloadTemplateInteressent()
    {

        $file = "Common/Style/Resource/Template/Import_Interessent.xlsx";
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename=Interessent_Import.xlsx");
        header("Content-Length: ". filesize($file));
        readfile($file);
    }

    public function downloadTemplateMail()
    {

        $file = "Common/Style/Resource/Template/Import_Mail.xlsx";
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename=Mail_Import.xlsx");
        header("Content-Length: ". filesize($file));
        readfile($file);
    }

    public function downloadTemplateHoliday()
    {

        $file = "Common/Style/Resource/Template/Import_Holiday.xlsx";
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename=Mail_Holiday.xlsx");
        header("Content-Length: ". filesize($file));
        readfile($file);
    }

}