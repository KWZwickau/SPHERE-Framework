<?php
namespace SPHERE\Application\Transfer\Import;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Transfer\Import\Annaberg\Annaberg;
use SPHERE\Application\Transfer\Import\Chemnitz\Chemnitz;
use SPHERE\Application\Transfer\Import\Coswig\Coswig;
use SPHERE\Application\Transfer\Import\FuxMedia\FuxSchool;
use SPHERE\Application\Transfer\Import\Herrnhut\Herrnhut;
use SPHERE\Application\Transfer\Import\Hormersdorf\Hormersdorf;
use SPHERE\Application\Transfer\Import\LebensweltZwenkau\Zwenkau;
use SPHERE\Application\Transfer\Import\Meerane\Meerane;
use SPHERE\Application\Transfer\Import\Muldental\Muldental;
use SPHERE\Application\Transfer\Import\Radebeul\Radebeul;
use SPHERE\Application\Transfer\Import\Schneeberg\Schneeberg;
use SPHERE\Application\Transfer\Import\Schulstiftung\Schulstiftung;
use SPHERE\Application\Transfer\Import\Seelitz\Seelitz;
use SPHERE\Application\Transfer\Import\Tharandt\Tharandt;
use SPHERE\Application\Transfer\Import\Zwickau\Zwickau;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Import
 *
 * @package SPHERE\Application\Transfer\Import
 */
class Import implements IApplicationInterface
{

    public static function registerApplication()
    {

        FuxSchool::registerModule();
        Schulstiftung::registerModule();

        $consumerAcronym = (Consumer::useService()->getConsumerBySession() ? Consumer::useService()->getConsumerBySession()->getAcronym() : '');
        if ($consumerAcronym === 'EGE' || $consumerAcronym == 'DEMO') {
            Annaberg::registerModule();
        }
        if ($consumerAcronym == 'ESZC' || $consumerAcronym == 'DEMO') {
            Chemnitz::registerModule();
        }
        if ($consumerAcronym === 'EVSC' || $consumerAcronym == 'DEMO') {
            Coswig::registerModule();
        }
        if ($consumerAcronym === 'EZGH' || $consumerAcronym == 'DEMO') {
            Herrnhut::registerModule();
        }
        if ($consumerAcronym === 'FEGH' || $consumerAcronym === 'FESH' || $consumerAcronym == 'DEMO') {
            Hormersdorf::registerModule();
        }
        if ($consumerAcronym === 'EVAMTL' || $consumerAcronym == 'DEMO') {
            Muldental::registerModule();
        }
        if ($consumerAcronym === 'EVSR' || $consumerAcronym == 'DEMO') {
            Radebeul::registerModule();
        }
        if ($consumerAcronym === 'ESS' || $consumerAcronym == 'DEMO') {
            Schneeberg::registerModule();
        }
        if ($consumerAcronym === 'ESRL' || $consumerAcronym == 'DEMO') {
            Seelitz::registerModule();
        }
        if ($consumerAcronym === 'CSW' || $consumerAcronym == 'DEMO') {
            Tharandt::registerModule();
        }
        if ($consumerAcronym === 'LWSZ' || $consumerAcronym == 'DEMO') {
            Zwenkau::registerModule();
        }
        if ($consumerAcronym === 'CMS' || $consumerAcronym == 'DEMO') {
            Zwickau::registerModule();
        }
        if ($consumerAcronym === 'EVGSM' || $consumerAcronym == 'DEMO') {
            Meerane::registerModule();
        }

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Daten importieren'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__ . '::frontendDashboard'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Import');

        $dataList = array();

        $consumerAcronym = (Consumer::useService()->getConsumerBySession() ? Consumer::useService()->getConsumerBySession()->getAcronym() : '');
        if ($consumerAcronym === 'CMS' || $consumerAcronym == 'DEMO') {
            $dataList = Zwickau::setLinks($dataList);
        }
        if ($consumerAcronym === 'CSW' || $consumerAcronym == 'DEMO') {
            $dataList = Tharandt::setLinks($dataList);
        }
        if ($consumerAcronym === 'EVGSM' || $consumerAcronym == 'DEMO') {
            $dataList = Meerane::setLinks($dataList);
        }

//        if(!$dataList){
//
//        }
        $table = new TableData(
            $dataList,
            null,
            array(
                'Consumer' => 'Mandant',
                'Name' => 'Name',
                'Option' => ''
            ),
            array(
                'order' => array(
                    array('0', 'asc'),
                    array('1', 'asc'),
                )
            )
        );

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            $table
                        ))
                    ))
                ))
            ))
            . Main::getDispatcher()->fetchDashboard('Import')
        );

        return $Stage;
    }
}
