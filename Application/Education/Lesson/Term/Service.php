<?php
namespace SPHERE\Application\Education\Lesson\Term;

use SPHERE\Application\Education\Lesson\Term\Service\Data;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Lesson\Term
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
     *
     * @param TblYear $tblYear
     *
     * @return bool|TblPeriod[]
     */
    public function getPeriodAllByYear(TblYear $tblYear)
    {

        return (new Data($this->getBinding()))->getPeriodAllByYear($tblYear);
    }

    /**
     * @return bool|TblYear[]
     */
    public function getYearAll()
    {

        return (new Data($this->getBinding()))->getYearAll();
    }

    /**
     * @return bool|TblPeriod[]
     */
    public function getPeriodAll()
    {

        return (new Data($this->getBinding()))->getPeriodAll();
    }

    /**
     * @param IFormInterface $Form
     * @param null|array     $Year
     *
     * @return IFormInterface|string
     */
    public function createYear(
        IFormInterface $Form,
        $Year
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Year) {
            return $Form;
        }

        $Error = false;

        if (isset( $Year['Name'] ) && empty( $Year['Name'] )) {
            $Form->setError('Year[Name]', 'Bitte geben Sie einen eineindeutigen Namen an');
            $Error = true;
        } else {
            if ($this->getYearByName($Year['Name'])) {
                $Form->setError('Year[Name]', 'Dieser Name wird bereits verwendet');
                $Error = true;
            }
        }

        if (!$Error) {

            if ((new Data($this->getBinding()))->createYear($Year['Name'], $Year['Description'])) {
                return new Success('Das Schuljahr wurde erfolgreich hinzugefügt')
                .new Redirect($this->getRequest()->getUrl(), 3);
            } else {
                return new Danger('Das Schuljahr konnte nicht hinzugefügt werden')
                .new Redirect($this->getRequest()->getUrl());
            }
        }
        return $Form;
    }

    /**
     * @param string $Name
     *
     * @return bool|TblYear
     */
    public function getYearByName($Name)
    {

        return (new Data($this->getBinding()))->getYearByName($Name);
    }

    /**
     * @param IFormInterface $Form
     * @param null|array     $Period
     *
     * @return IFormInterface|string
     */
    public function createPeriod(
        IFormInterface $Form,
        $Period
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Period) {
            return $Form;
        }

        $Error = false;

        if (isset( $Period['Name'] ) && empty( $Period['Name'] )) {
            $Form->setError('Period[Name]', 'Bitte geben Sie einen eineindeutigen Namen an');
            $Error = true;
        } else {
            if ($this->getPeriodByName($Period['Name'])) {
                $Form->setError('Period[Name]', 'Dieser Name wird bereits verwendet');
                $Error = true;
            }
        }

        if (isset( $Period['From'] ) && empty( $Period['From'] )) {
            $Form->setError('Period[From]', 'Bitte geben Sie Start-Datum an');
            $Error = true;
        }
        if (isset( $Period['To'] ) && empty( $Period['To'] )) {
            $Form->setError('Period[To]', 'Bitte geben Sie Ende-Datum an');
            $Error = true;
        }

        if (!$Error) {

            if ((new Data($this->getBinding()))->createPeriod(
                $Period['Name'], $Period['From'], $Period['To'], $Period['Description'])
            ) {
                return new Success('Der Zeitraum wurde erfolgreich hinzugefügt')
                .new Redirect($this->getRequest()->getUrl(), 3);
            } else {
                return new Danger('Der Zeitraum konnte nicht hinzugefügt werden')
                .new Redirect($this->getRequest()->getUrl());
            }
        }
        return $Form;
    }

    /**
     * @param string $Name
     *
     * @return bool|TblPeriod
     */
    public function getPeriodByName($Name)
    {

        return (new Data($this->getBinding()))->getPeriodByName($Name);
    }

    /**
     * @param string $Id
     *
     * @return bool|TblPeriod
     */
    public function getPeriodById($Id)
    {

        return (new Data($this->getBinding()))->getPeriodById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblYear
     */
    public function getYearById($Id)
    {

        return (new Data($this->getBinding()))->getYearById($Id);
    }
}
