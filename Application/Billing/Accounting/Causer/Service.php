<?php
namespace SPHERE\Application\Billing\Accounting\Causer;

use SPHERE\Application\Billing\Accounting\Causer\Service\Data;
use SPHERE\Application\Billing\Accounting\Causer\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Window\RedirectScript;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Accounting\Causer
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param IFormInterface $Form
     * @param string|string  $GroupId
     *
     * @return IFormInterface|string
     */
    public function directRoute(IFormInterface &$Form, $GroupId = null)
    {

        /**
         * Skip to Frontend
         */
        if(null === $GroupId){
            return $Form;
        }
        if('0' === $GroupId){
            $Form->setError('GroupId', 'Bitte wählen Sie eine Gruppe aus');
            return $Form;
        }

        return 'Lädt...'
            .(new ProgressBar(0, 100, 0, 12))->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_SUCCESS)
            .new RedirectScript('/Billing/Accounting/Causer/View', 0, array('GroupId' => $GroupId));
    }
}
