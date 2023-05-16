<?php
namespace SPHERE\Application\Transfer\Import;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Transfer\Import\Annaberg\Annaberg;
use SPHERE\Application\Transfer\Import\BadDueben\BadDueben;
use SPHERE\Application\Transfer\Import\Braeunsdorf\Braeunsdorf;
use SPHERE\Application\Transfer\Import\Chemnitz\Chemnitz;
use SPHERE\Application\Transfer\Import\Coswig\Coswig;
use SPHERE\Application\Transfer\Import\Dresden\Dresden;
use SPHERE\Application\Transfer\Import\EMSP\EMSP;
use SPHERE\Application\Transfer\Import\EVMS\EVMS;
use SPHERE\Application\Transfer\Import\FSE\FSE;
use SPHERE\Application\Transfer\Import\FuxMedia\FuxSchool;
use SPHERE\Application\Transfer\Import\Herrnhut\Herrnhut;
use SPHERE\Application\Transfer\Import\Hormersdorf\Hormersdorf;
use SPHERE\Application\Transfer\Import\LebensweltZwenkau\Zwenkau;
use SPHERE\Application\Transfer\Import\Meerane\Meerane;
use SPHERE\Application\Transfer\Import\MLS\MLS;
use SPHERE\Application\Transfer\Import\Muldental\Muldental;
use SPHERE\Application\Transfer\Import\Naundorf\Naundorf;
use SPHERE\Application\Transfer\Import\Radebeul\Radebeul;
use SPHERE\Application\Transfer\Import\Schneeberg\Schneeberg;
use SPHERE\Application\Transfer\Import\Schulstiftung\Schulstiftung;
use SPHERE\Application\Transfer\Import\Seelitz\Seelitz;
use SPHERE\Application\Transfer\Import\Standard\ImportStandard;
use SPHERE\Application\Transfer\Import\Standard\Mail\Mail;
use SPHERE\Application\Transfer\Import\Tharandt\Tharandt;
use SPHERE\Application\Transfer\Import\WVSZ\WVSZ;
use SPHERE\Application\Transfer\Import\Zwickau\Zwickau;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
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

        $tblConsumer = Consumer::useService()->getConsumerBySession();
        if ($tblConsumer && $tblConsumer->getType() == TblConsumer::TYPE_SACHSEN) {
            $consumerAcronym = $tblConsumer->getAcronym();

            if ($consumerAcronym === 'EGE') {
                Annaberg::registerModule();
            }
            if ($consumerAcronym == 'ESZC') {
                Chemnitz::registerModule();
            }
            if ($consumerAcronym === 'EVSC') {
                Coswig::registerModule();
            }
            if ($consumerAcronym === 'EZSH') {
                Herrnhut::registerModule();
            }
            if ($consumerAcronym === 'FESH') {
                Hormersdorf::registerModule();
            }
            if ($consumerAcronym === 'EVAMTL') {
                Muldental::registerModule();
            }
            if ($consumerAcronym === 'EVSR') {
                Radebeul::registerModule();
            }
            if ($consumerAcronym === 'ESS') {
                Schneeberg::registerModule();
            }
            if ($consumerAcronym === 'ESRL') {
                Seelitz::registerModule();
            }
            if ($consumerAcronym === 'CSW') {
                Tharandt::registerModule();
            }
            if ($consumerAcronym === 'LWSZ') {
                Zwenkau::registerModule();
            }
            if ($consumerAcronym === 'CMS') {
                Zwickau::registerModule();
            }
            if ($consumerAcronym === 'EVGSM') {
                Meerane::registerModule();
            }
            if ($consumerAcronym === 'FES') {
                Dresden::registerModule();
            }
            if ($consumerAcronym === 'ESBD') {
                BadDueben::registerModule();
            }
            if ($consumerAcronym === 'EWS') {
                Naundorf::registerModule();
            }
            if ($consumerAcronym === 'EVSB') {
                Braeunsdorf::registerModule();
            }
            if ($consumerAcronym === 'EVMS') {
                EVMS::registerModule();
            }
            if ($consumerAcronym === 'FSE') {
                FSE::registerModule();
            }
            if ($consumerAcronym === 'EMSP') {
                EMSP::registerModule();
            }
            if ($consumerAcronym === 'MLS') {
                MLS::registerModule();
            }
            if ($consumerAcronym === 'WVSZ') {
                WVSZ::registerModule();
            }
        }

        ImportStandard::registerModule();
        Mail::registerModule();

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

        $tblConsumer = Consumer::useService()->getConsumerBySession();
        if ($tblConsumer && $tblConsumer->getType() == TblConsumer::TYPE_SACHSEN) {
            $consumerAcronym = $tblConsumer->getAcronym();
            if ($consumerAcronym === 'CMS') {
                $dataList = Zwickau::setLinks($dataList);
            }
            if ($consumerAcronym === 'CSW') {
                $dataList = Tharandt::setLinks($dataList);
            }
            if ($consumerAcronym === 'EVGSM') {
                $dataList = Meerane::setLinks($dataList);
            }
            if ($consumerAcronym === 'FES') {
                $dataList = Dresden::setLinks($dataList);
            }
            if ($consumerAcronym === 'EZSH') {
                $dataList = Herrnhut::setLinks($dataList);
            }
            if ($consumerAcronym === 'ESBD') {
                $dataList = BadDueben::setLinks($dataList);
            }
            if ($consumerAcronym === 'EWS') {
                $dataList = Naundorf::setLinks($dataList);
            }
            if ($consumerAcronym === 'EVSB') {
                $dataList = Braeunsdorf::setLinks($dataList);
            }
            if ($consumerAcronym === 'EVMS') {
                $dataList = EVMS::setLinks($dataList);
            }
            if ($consumerAcronym === 'FSE') {
                $dataList = FSE::setLinks($dataList);
            }
            if ($consumerAcronym === 'EMSP') {
                $dataList = EMSP::setLinks($dataList);
            }
            if ($consumerAcronym === 'MLS') {
                $dataList = MLS::setLinks($dataList);
            }
            if ($consumerAcronym === 'WVSZ') {
                $dataList = WVSZ::setLinks($dataList);
            }
        }

//        $dataList = Mail::setLinks($dataList);

        if(empty($dataList)){
            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('', new Bold('<H3>Standard Import</H3>'). new Layout(ImportStandard::getStandardLink()), Panel::PANEL_TYPE_PRIMARY)
                    ),
                    new LayoutColumn(
                        Main::getDispatcher()->fetchDashboard('Import')
                    )
                ))))
            );
            return $Stage;
        }

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
            new Layout(
                new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('', new Bold('<H3>Standard Import</H3>'). new Layout(ImportStandard::getStandardLink()), Panel::PANEL_TYPE_PRIMARY)
                    ),
                    new LayoutColumn(
                        $table
                    ),
                    new LayoutColumn(
                        Main::getDispatcher()->fetchDashboard('Import')
                    ),
                ))
            ))
        );

        return $Stage;
    }
}
