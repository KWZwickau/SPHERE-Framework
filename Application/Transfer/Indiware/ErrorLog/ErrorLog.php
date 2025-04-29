<?php
namespace SPHERE\Application\Transfer\Indiware\ErrorLog;

use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableReplacementLog;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Transfer\Indiware\Import\Replacement\Replacement;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Log
 *
 * @package SPHERE\Application\Transfer\Indiware\ErrorLog
 */
class ErrorLog extends Extension implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Api Logfile'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendLogOverview'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Json', __CLASS__.'::frontendDoJson'
        ));
    }

    /**
     */
    public static function useService()
    {

    }

    /**
     */
    public static function useFrontend()
    {

    }

    public function frontendDoJson()
    {

        $Stage = new Stage('Json einspielen');
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));
        $Json = (new JsonReplacementTest())->getJson('EVSR');
        Replacement::useService()->importJsonReplacement($Json);
        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendLogOverview(): Stage
    {
        $Stage = new Stage('Indiware', 'API-Errorlog');
        $Stage->addButton(new Standard('Json "Anfragen"', __NAMESPACE__.'/Json', new Download()));
        $ReplacementLogAll = Timetable::useService()->getTimeTableReplacementLogAll();
        $TableContent = array();
        if($ReplacementLogAll){
            array_walk($ReplacementLogAll, function (&$ReplacementLog) use (&$TableContent) {
                /** @var $ReplacementLog TblTimetableReplacementLog */
                $item = array();
                $item['Date'] = $ReplacementLog->getDate();
                $item['Hour'] = $ReplacementLog->getHour();
                $item['Course'] = $ReplacementLog->getCourse();
                $item['PersonAcronym'] = $ReplacementLog->getPersonAcronym();
                $item['Room'] = $ReplacementLog->getRoom();
                $item['IsCanceled'] = ($ReplacementLog->getIsCanceled() ? "Ausfall" : "" );
                $ErrorList = explode(';', $ReplacementLog->getError());
                $item['Error'] = implode("<br/>", $ErrorList);
                $TableContent[] = $item;
            });
        }

        $Stage->setContent(
            new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                new TableData($TableContent, new Title('Auflistung', 'welche Werte konnten nicht importiert werden'), array(
                        'Date' => 'Datum',
                        'Hour' => 'Stunde',
                        'Course' => 'Klasse',
                        'PersonAcronym' => 'Lehrer Kürzel',
                        'Room' => 'Raum',
                        'IsCanceled' => 'Ausfall',
                        'Error' => 'Error',
                    ),
                    array(
                        'order' => array(
                            array(0, 'asc'),
                            array(1, 'asc'),
                            array(2, 'asc')
                        ),
                        'columnDefs' => array(
//                            array('type' => 'de_date', 'targets' => array(0, 1)),
//                            array('orderable' => false, 'width' => '1%', 'targets' => -1),
                        ),
                        'responsive' => false
                    )
                )
            ))))
        );

        return $Stage;
    }
}