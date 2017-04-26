<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 26.04.2017
 * Time: 11:30
 */

namespace SPHERE\Application\Api\Education\Prepare;

use SPHERE\Application\Api\Response;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Icon\Repository\HazardSign;
use SPHERE\Common\Frontend\Icon\Repository\Success;
use SPHERE\Common\Main;
use SPHERE\System\Extension\Extension;

class Prepare extends Extension implements IModuleInterface
{

    public static function registerModule()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Reorder', __CLASS__ . '::reorderDivision'
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
     * @param null|array $Reorder
     * @param null|array $Additional
     *
     * @return Response
     */
    public function reorderDivision($Reorder = null, $Additional = null)
    {

        if ($Additional
            && $Reorder
            && isset($Additional['PrepareId'])
            && isset($Additional['PersonId'])
            && ($tblPrepare = \SPHERE\Application\Education\Certificate\Prepare\Prepare::useService()->getPrepareById($Additional['PrepareId']))
            && ($tblPerson = Person::useService()->getPersonById($Additional['PersonId']))
        ) {

            $list = \SPHERE\Application\Education\Certificate\Prepare\Prepare::useService()->getPrepareAdditionalGradesBy(
                $tblPrepare,
                $tblPerson
            );
            if ($list) {
                // update Ranking for deleted DroppedSubjects etc.
                $count = 1;
                foreach ($list as $tblPrepareAdditionalGrade) {
                    \SPHERE\Application\Education\Certificate\Prepare\Prepare::useService()->updatePrepareAdditionalGradeRanking($tblPrepareAdditionalGrade, $count++);
                }
                foreach ($Reorder as $item) {
                    if (isset($item['pre']) && isset($item['post'])) {
                        $pre = $item['pre'] - 1;
                        $post = $item['post'];

                        if (isset($list[$pre])) {
                            \SPHERE\Application\Education\Certificate\Prepare\Prepare::useService()->updatePrepareAdditionalGradeRanking(
                                $list[$pre], $post);
                        }
                    }
                }
            }
            return (new Response())->addData( new Success().' Die Sortierung der abgewählten Fächer wurde erfolgreich aktualisiert.');
        }
        return (new Response())->addError( 'Fehler!', new HazardSign().' Die Sortierung der abgewählten Fächer konnte nicht aktualisiert werden.', 0);
    }
}