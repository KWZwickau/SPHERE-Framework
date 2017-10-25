<?php

namespace SPHERE\Application\Api\Transfer\Task;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Transfer\Export\Graduation\Graduation;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;

class Task implements IModuleInterface
{
    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Download', __NAMESPACE__.'\Task::downloadTask'
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
     * @param null $TaskId
     *
     * @return bool|string
     */
    public function downloadTask($TaskId = null)
    {

        $fileLocation = Graduation::useService()
            ->createGradeListCsv($TaskId);
        $tblTask = Evaluation::useService()->getTaskById($TaskId);
        if ($fileLocation && $tblTask) {
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Stichtagsnoten"." Stichtag ".$tblTask->getDate().".csv")->__toString();
        }

        return false;
    }
}