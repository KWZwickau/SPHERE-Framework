<?php
namespace SPHERE\Application\Billing\Accounting\Causer;

use SPHERE\Application\Billing\Accounting\Causer\Service\Data;
use SPHERE\Application\Billing\Accounting\Causer\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Window\Redirect;
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
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
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
        if (null === $GroupId) {
            return $Form;
        }
        if('0' === $GroupId){
            $Form->setError('GroupId', 'Bitte wählen Sie eine Gruppe aus');
            return $Form;
        }

        return 'Lädt...'
            .(new ProgressBar(0, 100, 0, 12))->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_SUCCESS)
            .new RedirectScript('/Billing/Accounting/Causer/View', Redirect::TIMEOUT_SUCCESS, array('GroupId' => $GroupId));
    }
}
