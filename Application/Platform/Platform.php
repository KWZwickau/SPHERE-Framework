<?php
namespace SPHERE\Application\Platform;

use SPHERE\Application\IClusterInterface;
use SPHERE\Application\Platform\Assistance\Assistance;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Platform\Gatekeeper\Gatekeeper;
use SPHERE\Application\Platform\Roadmap\Roadmap;
use SPHERE\Application\Platform\System\DataMaintenance\DataMaintenance;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Platform\System\System;
use SPHERE\Common\Frontend\Icon\Repository\CogWheels;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class System
 *
 * @package SPHERE\Application\System
 */
class Platform implements IClusterInterface
{

    public static function registerCluster()
    {

        $tblAccount = Account::useService()->getAccountBySession();
        if($tblAccount && $tblAccount->getHasAuthentication(TblIdentification::NAME_SYSTEM)) {
            $LabelColor = Label::LABEL_TYPE_DANGER;
            if($tblAccount->getServiceTblConsumer()) {
                $Acronym = $tblAccount->getServiceTblConsumer()->getAcronym();
                if($Acronym == 'REF' || $Acronym == 'DEMO') {
                    $LabelColor = Label::LABEL_TYPE_DEFAULT;
                    // Protokollanzeige nur auf REF & DEMO Mandant
                    $DateNow = new \DateTime();
                    // prüfung nur mit dem Datum
                    $DateGreen = self::getIntervalDate($DateNow, 8);
                    $DateYellow = self::getIntervalDate($DateNow, 31);

                    $tblProtocol = Protocol::useService()->getProtocolFirstEntry();
                    $LabelCountColor = Label::LABEL_TYPE_DANGER;
                    if($tblProtocol && $tblProtocol->getEntityCreate() >= $DateGreen) {
                        $LabelCountColor = Label::LABEL_TYPE_DEFAULT;
                    } elseif($tblProtocol && $tblProtocol->getEntityCreate() >= $DateYellow) {
                        $LabelCountColor = Label::LABEL_TYPE_WARNING;
                    }
//                    $ProtocolCount = DataMaintenance::useFrontend()->getProtocolEntryCount();
                    Main::getDisplay()->addServiceNavigation(
                        new Link(new Link\Route('/Platform/System/DataMaintenance/Protocol'), new Link\Name(new Bold(new Label('Protokoll', $LabelCountColor))))
                    );
                }
            }
            Main::getDisplay()->addServiceNavigation(
                new Link(
                    new Link\Route('/Setting/MyAccount/Consumer'),
                    new Link\Name(
                        new Bold(new Label(
                            'Mandant '
                            .($tblAccount->getServiceTblConsumer() ? $tblAccount->getServiceTblConsumer()->getAcronym() : '')
                            , $LabelColor))
                    )
                )
            );
        }

        /**
         * Register Application
         */
        System::registerApplication();
        Gatekeeper::registerApplication();
        Assistance::registerApplication();
        Roadmap::registerApplication();
        /**
         * Register Navigation
         */
        Main::getDisplay()->addServiceNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Plattform'), new Link\Icon(new CogWheels()))
        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__, 'Platform::frontendPlatform')
        );
    }

    /**
     * @return Stage
     */
    public function frontendPlatform()
    {

        $Stage = new Stage('Plattform', 'Systemeinstellungen');

        ob_start();
        phpinfo();
        $PhpInfo = ob_get_clean();

        $Stage->setContent(
            '<div id="phpinfo">'.
            preg_replace('!,!', ', ',
                preg_replace('!<th>(enabled)\s*</th>!i',
                    '<th><span class="badge badge-success">$1</span></th>',
                    preg_replace('!<td class="v">(On|enabled|active|Yes)\s*</td>!i',
                        '<td class="v"><span class="badge badge-success">$1</span></td>',
                        preg_replace('!<td class="v">(Off|disabled|No)\s*</td>!i',
                            '<td class="v"><span class="badge badge-danger">$1</span></td>',
                            preg_replace('!<i>no value</i>!',
                                '<span class="label label-warning">no value</span>',
                                preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $PhpInfo)
                            )
                        )
                    )
                )
            )
            .'</div>'
        );
        return $Stage;
    }

    /**
     * @param \DateTime $Date
     * @param int       $IntervalAdd
     *
     * @return \DateTime
     */
    private static function getIntervalDate(\DateTime $Date, int $IntervalAdd): \DateTime
    {

        $Date = new \DateTime($Date->format('Y-m-d'));
        $ProtocolActiveDays = DataMaintenance::useFrontend()->getProtocolActiveDays();
        $DefaultColorInterval = $ProtocolActiveDays + $IntervalAdd;
        $Date->sub(new \DateInterval('P'.$DefaultColorInterval.'D'));
        return $Date;
    }
}
