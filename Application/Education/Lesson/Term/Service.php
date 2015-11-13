<?php
namespace SPHERE\Application\Education\Lesson\Term;

use SPHERE\Application\Education\Lesson\Term\Service\Data;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
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
                .new Redirect($this->getRequest()->getUrl(), 2);
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
     * @param $Year
     * @param $Period
     *
     * @return Success
     */
    public function addYearPeriod($Year, $Period)
    {

        $tblYear = $this->getYearById($Year);
        $tblPeriod = $this->getPeriodById($Period);

        if ((new Data($this->getBinding()))->addYearPeriod($tblYear, $tblPeriod)) {
            return new Success('Zeitraum festgelegt').
            new Redirect('/Education/Lesson/Term', 1);
        }
        return new Warning('Zeitraum konnte nicht festgelegt werden').
        new Redirect('/Education/Lesson/Term');
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
     * @param $Year
     * @param $Period
     *
     * @return Success
     */
    public function removeYearPeriod($Year, $Period)
    {

        $tblYear = $this->getYearById($Year);
        $tblPeriod = $this->getPeriodById($Period);

        if ((new Data($this->getBinding()))->removeYearPeriod($tblYear, $tblPeriod)) {
            return new Success('Zeitraum entfernt').
            new Redirect('/Education/Lesson/Term', 1);
        }
        return new Warning('Zeitraum konnte nicht entfernt werden').
        new Redirect('/Education/Lesson/Term');
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
        }

        if (isset( $Period['From'] ) && empty( $Period['From'] )) {
            $Form->setError('Period[From]', 'Bitte geben Sie Start-Datum an');
            $Error = true;
        }
        if (isset( $Period['To'] ) && empty( $Period['To'] )) {
            $Form->setError('Period[To]', 'Bitte geben Sie Ende-Datum an');
            $Error = true;
        }
        $tblPeriod = $this->getPeriodByName($Period['Name']);
        if (!empty( $tblPeriod )) {
            if ($tblPeriod->getFromDate() === $Period['From']
                && $tblPeriod->getToDate() === $Period['To']
                && $tblPeriod->getDescription() === $Period['Description']
            ) {
                $Form->setError('Period[From]', 'Kombination vergeben');
                $Form->setError('Period[To]', 'Kombination vergeben');
                $Form->setError('Period[Name]', 'Kombination vergeben');
                $Form->setError('Period[Description]', 'Kombination vergeben');
                $Form .= new Warning('Kombination aus Name, Beschreibung und Zeitraum schon vorhanden!');
                $Error = true;
            }
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
     * @param IFormInterface|null $Stage
     * @param TblYear             $tblYear
     * @param                     $Year
     *
     * @return IFormInterface|string
     */
    public function changeYear(
        IFormInterface &$Stage = null,
        TblYear $tblYear,
        $Year
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Year
        ) {
            return $Stage;
        }

        $Error = false;

        if (isset( $Year['Name'] ) && empty( $Year['Name'] )) {
            $Stage->setError('Year[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        } else {
            if ($this->getYearByName($Year['Name'])) {
                $Stage->setError('Year[Name]', 'Dieser Name wird bereits verwendet');
                $Error = true;
            }
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->updateYear(
                $tblYear,
                $Year['Name'],
                $Year['Description']
            )
            ) {
                $Stage .= new Success('Änderungen gespeichert, die Daten werden neu geladen...')
                    .new Redirect('/Education/Lesson/Term/Create/Year', 1);
            } else {
                $Stage .= new Danger('Änderungen konnten nicht gespeichert werden')
                    .new Redirect('/Education/Lesson/Term/Create/Year');
            };
        }
        return $Stage;
    }
    public function changePeriod(
        IFormInterface &$Stage = null,
        TblPeriod $tblPeriod,
        $Period
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Period
        ) {
            return $Stage;
        }

        $Error = false;

        if (isset( $Period['Name'] ) && empty( $Period['Name'] )) {
            $Stage->setError('Period[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }
        if (isset( $Period['From'] ) && empty( $Period['From'] )) {
            $Stage->setError('Period[From]', 'Bitte geben Sie ein Startdatum an');
            $Error = true;
        }
        if (isset( $Period['To'] ) && empty( $Period['To'] )) {
            $Stage->setError('Period[To]', 'Bitte geben Sie ein Enddatum an');
            $Error = true;
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->updatePeriod(
                $tblPeriod,
                $Period['Name'],
                $Period['Description'],
                $Period['From'],
                $Period['To']
            )
            ) {
                $Stage .= new Success('Änderungen gespeichert, die Daten werden neu geladen...')
                    .new Redirect('/Education/Lesson/Term/Create/Period', 1);
            } else {
                $Stage .= new Danger('Änderungen konnten nicht gespeichert werden')
                    .new Redirect('/Education/Lesson/Term/Create/Period');
            };
        }
        return $Stage;
    }
    /**
     * @param TblYear $tblYear
     *
     * @return string
     */
    public function destroyYear(TblYear $tblYear)
    {

        if (null === $tblYear) {
            return '';
        }
        if ((new Data($this->getBinding()))->destroyYear($tblYear)) {
            return new Success('Das Jahr wurde erfolgreich gelöscht')
            .new Redirect('/Education/Lesson/Term/Create/Year', 1);
        } else {
            return new Danger('Das Jahr konnte nicht gelöscht werden')
            .new Redirect('/Education/Lesson/Term/Create/Year');
        }

    }
    /**
     * @param TblPeriod $tblPeriod
     *
     * @return string
     */
    public function destroyPeriod(TblPeriod $tblPeriod)
    {

        if (null === $tblPeriod) {
            return '';
        }
        $Error = false;

        if ($this->getPeriodExistWithYear($tblPeriod)) {
            $Error = true;
        }
        if (!$Error) {
            if ((new Data($this->getBinding()))->destroyPeriod($tblPeriod)) {
                return new Success('Der Zeitraum wurde erfolgreich gelöscht')
                .new Redirect('/Education/Lesson/Term/Create/Period', 1);
            } else {
                return new Danger('Der Zeitraum konnte nicht gelöscht werden')
                .new Redirect('/Education/Lesson/Term/Create/Period');
            }
        }
        return new Danger('Der Zeitraum wird benutzt!')
        .new Redirect('/Education/Lesson/Term/Create/Period');
    }
    /**
     * @param TblPeriod $tblPeriod
     *
     * @return bool
     */
    public function getPeriodExistWithYear(TblPeriod $tblPeriod)
    {

        return (new Data($this->getBinding()))->getPeriodExistWithYear($tblPeriod);
    }
}
