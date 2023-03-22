<?php
namespace SPHERE\Application\Education\Lesson\Term;

use DateInterval;
use DateTime;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Data;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblHoliday;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblHolidayType;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYearHoliday;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYearPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYearPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Setup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\BasicData\BasicData;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
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
     * @return false|ViewYear[]
     */
    public function viewYear()
    {

        return ( new Data($this->getBinding()) )->viewYear();
    }

    /**
     * @return false|ViewYearPeriod[]
     */
    public function viewYearPeriod()
    {

        return ( new Data($this->getBinding()) )->viewYearPeriod();
    }

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
     * @deprecated
     *
     * @param TblYear $tblYear
     * @param TblDivision|null $tblDivision
     * @param bool $IsAll
     *
     * @return bool|TblPeriod[]
     */
    public function getPeriodAllByYear(TblYear $tblYear, TblDivision $tblDivision = null, bool $IsAll = false)
    {
        // aGym Klasse 12 oder bGym Klasse 13
        if ($tblDivision
            && ($tblLevel = $tblDivision->getTblLevel())
            && ($tblSchoolType = $tblLevel->getServiceTblType())
            && (($tblSchoolType->getShortName() == 'Gy' && intval($tblLevel->getName()) == 12)
                    || ($tblSchoolType->getShortName() == 'BGy' && intval($tblLevel->getName()) == 13))
        ) {
            $IsLevel12 = true;
        } else {
            $IsLevel12 = false;
        }

        return (new Data($this->getBinding()))->getPeriodAllByYear($tblYear, $IsLevel12, $IsAll);
    }

    /**
     * @param TblYear $tblYear
     * @param bool $isShortYear
     * @param bool $isAllYear
     *
     * @return false|TblPeriod[]
     */
    public function getPeriodListByYear(TblYear $tblYear, bool $isShortYear = false, bool $isAllYear = false)
    {
        return (new Data($this->getBinding()))->getPeriodListByYear($tblYear, $isShortYear, $isAllYear);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return false|TblPeriod[]
     */
    public function getPeriodListByPersonAndYear(TblPerson $tblPerson, TblYear $tblYear)
    {
        if (DivisionCourse::useService()->getIsShortYearByPersonAndYear($tblPerson, $tblYear)) {
            return $this->getPeriodListByYear($tblYear, true);
        } else {
            return $this->getPeriodListByYear($tblYear);
        }
    }

    /**
     * @return bool|TblYear[]
     */
    public function getYearAll()
    {

        return (new Data($this->getBinding()))->getYearAll();
    }

    /**
     * @param int $Year
     *
     * @return bool|TblYear[]
     */
    public function getYearAllSinceYears($Year)
    {

        $Now = (new DateTime('now'))->sub(new DateInterval('P' . $Year . 'Y'));

        $EntityList = array();
        $tblYearAll = Term::useService()->getYearAll();
        if ($tblYearAll) {
            foreach ($tblYearAll as $tblYear) {
                $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear, null, true);
                if ($tblPeriodList) {
                    $To = '';
                    $tblPeriodTemp = new TblPeriod();
                    foreach ($tblPeriodList as $tblPeriod) {
                        if (new DateTime($tblPeriod->getToDate()) > new DateTime($To) || $To == '') {
                            $To = $tblPeriod->getToDate();
                        }
                        if ($tblPeriod) {
                            $tblPeriodTemp = $tblPeriod;
                        }
                    }
                    if (new DateTime($To) >= new DateTime($Now->format('d.m.Y'))) {
                        $tblYearTempList = Term::useService()->getYearByPeriod($tblPeriodTemp);
                        if ($tblYearTempList) {
                            foreach ($tblYearTempList as $tblYearTemp) {
                                /** @var TblYear $tblYearTemp */
                                $EntityList[$tblYearTemp->getId()] = $tblYearTemp;
                            }
                        }
                    }
                }
            }
        }
        $EntityList = array_filter($EntityList);

        return (empty($EntityList) ? false : $EntityList);
    }

    /**
     * @param int $Year
     *
     * @return bool|TblYear[]
     */
    public function getYearAllFutureYears($Year)
    {

        $Now = (new DateTime('now'))->add(new DateInterval('P' . $Year . 'Y'));

        $EntityList = array();
        $tblYearAll = Term::useService()->getYearAll();
        if ($tblYearAll) {
            foreach ($tblYearAll as $tblYear) {
                $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear, null, true);
                if ($tblPeriodList) {
                    $To = '';
                    $tblPeriodTemp = new TblPeriod();
                    foreach ($tblPeriodList as $tblPeriod) {
                        if (new DateTime($tblPeriod->getToDate()) > new DateTime($To) || $To == '') {
                            $To = $tblPeriod->getToDate();
                        }
                        if ($tblPeriod) {
                            $tblPeriodTemp = $tblPeriod;
                        }
                    }
                    if (new DateTime($To) >= new DateTime($Now->format('d.m.Y'))) {
                        $tblYearTempList = Term::useService()->getYearByPeriod($tblPeriodTemp);
                        if ($tblYearTempList) {
                            foreach ($tblYearTempList as $tblYearTemp) {
                                /** @var TblYear $tblYearTemp */
                                $EntityList[$tblYearTemp->getId()] = $tblYearTemp;
                            }
                        }
                    }
                }
            }
        }
        $EntityList = array_filter($EntityList);

        return (empty($EntityList) ? false : $EntityList);
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
     * @param null|array $Year
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

        if (isset($Year['Year']) && empty($Year['Year'])) {
            $Form->setError('Year[Year]', 'Bitte geben sie ein Jahr an');
            $Error = true;
        } else {
            if (($tblYear = Term::useService()->checkYearExist($Year['Year'], $Year['Description']))) {
                $Form->setError('Year[Year]', 'Dieses Schuljahr existiert bereits');
                $Form->setError('Year[Description]', 'Ändern Sie die Beschreibung um das Jahr wiederholt speichern zu können');
                $Error = true;
            }
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->createYear($Year['Year'], $Year['Description'])) {
                return new Success('Das Schuljahr wurde erfolgreich hinzugefügt')
                . new Redirect($this->getRequest()->getUrl(), Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger('Das Schuljahr konnte nicht hinzugefügt werden')
                . new Redirect($this->getRequest()->getUrl(), Redirect::TIMEOUT_ERROR);
            }
        }

        return $Form;
    }

    /**
     * @param string $Year
     * @param string $Description
     *
     * @return false|TblYear
     */
    public function checkYearExist($Year, $Description = '')
    {

        return (new Data($this->getBinding()))->checkYearExist($Year, $Description);
    }

    /**
     * @param TblYear $tblYear
     *
     * @return bool|TblYear
     */
    public function getYearsByYear(TblYear $tblYear)
    {

        return (new Data($this->getBinding()))->getYearsByYear($tblYear);
    }

    /**
     * @param TblPeriod $tblPeriod
     *
     * @return array|bool
     */
    public function getYearByPeriod(TblPeriod $tblPeriod)
    {

        return (new Data($this->getBinding()))->getYearByPeriod($tblPeriod);
    }

    /**
     * @param $String
     *
     * @return false|Service\Entity\TblYear[]
     */
    public function getYearByName($String)
    {
        return (new Data($this->getBinding()))->getYearByName($String);
    }

    /**
     * @param DateTime $Date
     *
     * @return bool|TblYear[]
     */
    public function getYearAllByDate(DateTime $Date)
    {

        $EntityList = array();
        $tblYearAll = Term::useService()->getYearAll();
        if ($tblYearAll) {
            foreach ($tblYearAll as $tblYear) {
                $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear, null, true);
                if ($tblPeriodList) {
                    $From = '';
                    $To = '';
                    $tblPeriodTemp = new TblPeriod();
                    foreach ($tblPeriodList as $tblPeriod) {
                        if (new DateTime($tblPeriod->getFromDate()) < new DateTime($From) || $From == '') {
                            $From = $tblPeriod->getFromDate();
                        }
                        if (new DateTime($tblPeriod->getToDate()) > new DateTime($To) || $To == '') {
                            $To = $tblPeriod->getToDate();
                        }
                        if ($tblPeriod) {
                            $tblPeriodTemp = $tblPeriod;
                        }
                    }
                    if (new DateTime($From) <= new DateTime($Date->format('d.m.Y')) &&
                        new DateTime($To) >= new DateTime($Date->format('d.m.Y'))
                    ) {
                        $tblYearTempList = Term::useService()->getYearByPeriod($tblPeriodTemp);
                        if ($tblYearTempList) {
                            foreach ($tblYearTempList as $tblYearTemp) {
                                /** @var TblYear $tblYearTemp */
                                $EntityList[$tblYearTemp->getId()] = $tblYearTemp;
                            }
                        }
                    }
                }
            }
        }
//        $UsedPeriodList = array();
//        $tblPeriodAll = Term::useService()->getPeriodAll();
//        if ($tblPeriodAll) {
//            foreach ($tblPeriodAll as $tblPeriod) {
//                if (new \DateTime($tblPeriod->getFromDate()) < new \DateTime($Now->format('d.m.Y'))
//                    && new \DateTime($tblPeriod->getToDate()) > new \DateTime($Now->format('d.m.Y'))
//                ) {
//                    $UsedPeriodList[] = $tblPeriod;
//                }
//            }
//            if (!empty( $UsedPeriodList )) {
//                foreach ($UsedPeriodList as $UsedPeriod) {
//                    $EntiyArrayList = Term::useService()->getYearByPeriod($UsedPeriod);
//                    if (!empty( $EntiyArrayList )) {
//                        foreach ($EntiyArrayList as $EntityArray) {
//                            $EntityList[] = $EntityArray;
//                        }
//                    }
//                }
//            }
//        }

        $EntityList = array_filter($EntityList);

        return (empty($EntityList) ? false : $EntityList);
    }

    /**
     * @return bool|Service\Entity\TblYear[]
     */
    public function getYearByNow()
    {

        $Now = new DateTime('now');
        return $this->getYearAllByDate($Now);
    }

    /**
     * @return string
     */
    public function getYearString()
    {
        $now = new DateTime();
        $YearString = (int)$now->format('Y');
        $YearStringAdd = (int)substr($YearString, 2, 2);
        $YearStringAdd++;
        // Standard Schuljahreswechsel -> Jahr wird ein hochgezählt
        if($now < new DateTime('01.08.'.$YearString)){
            $YearString--;
            $YearStringAdd--;
        }
        $YearString .= '/'.$YearStringAdd;
        return $YearString;
    }

    /**
     * @return array
     * Array with 'Key' <br/>
     * 'PastYear' -> 2020 <br/>
     * 'PastDisplayYear' -> 2020/21 <br/>
     * 'CurrentYear' -> 2021 <br/>
     * 'CurrentDisplayYear' -> 2021/22
     */
    public function getYearStringAsArray()
    {
        $Date = array();
        $now = new DateTime();
        $YearString = (int)$now->format('Y');
        $YearStringAdd = (int)substr($YearString, 2, 2);
        $YearStringAdd++;
        // Standard Schuljahreswechsel -> Jahr wird ein hochgezählt
        if($now < new DateTime('01.08.'.$YearString)){
            $YearString--;
            $YearStringAdd--;
        }

        $PastYear = $YearString - 1;
        $PastDisplayYear = ($YearString - 1).'/'.($YearStringAdd - 1);
        $Date['PastYear'] = $PastYear;
        $Date['PastDisplayYear'] = $PastDisplayYear;

        $Date['CurrentYear'] = $YearString;
        $YearString .= '/'.$YearStringAdd;
        $Date['CurrentDisplayYear'] = $YearString;

        return $Date;
    }

    /**
     * @param $tblYear
     * @param $tblPeriod
     *
     * @return string
     */
    public function addYearPeriod($tblYear, $tblPeriod)
    {
        return (new Data($this->getBinding()))->addYearPeriod($tblYear, $tblPeriod);
    }

    /**
     * @param $Id
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
     * @param $tblYear
     * @param $tblPeriod
     *
     * @return string
     */
    public function removeYearPeriod($tblYear, $tblPeriod)
    {
        return (new Data($this->getBinding()))->removeYearPeriod($tblYear, $tblPeriod);
    }

    /**
     * @param IFormInterface $Form
     * @param null|array $Period
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

        if (isset($Period['Name']) && empty($Period['Name'])) {
            $Form->setError('Period[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }

        if (isset($Period['From']) && empty($Period['From'])) {
            $Form->setError('Period[From]', 'Bitte geben Sie Start-Datum an');
            $Error = true;
        }
        if (isset($Period['To']) && empty($Period['To'])) {
            $Form->setError('Period[To]', 'Bitte geben Sie Ende-Datum an');
            $Error = true;
        }

        if (!$Error) {
            $dateFrom = new DateTime($Period['From']);
            $dateTo = new DateTime($Period['To']);

            if ($dateFrom > $dateTo) {
                $Form->setError('Period[To]', new Exclamation()
                    . ' Das "Datum bis" darf nicht kleiner sein, als das "Datum von".');

                $Error = true;
            }
        }

        $tblPeriod = $this->getPeriodByName($Period['Name']);
        if (!empty($tblPeriod)) {
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
                $Period['Name'], $Period['From'], $Period['To'], $Period['Description'], isset($Period['IsLevel12']))
            ) {
                return new Success('Der Zeitraum wurde erfolgreich hinzugefügt')
                . new Redirect($this->getRequest()->getUrl(), Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger('Der Zeitraum konnte nicht hinzugefügt werden')
                . new Redirect($this->getRequest()->getUrl(), Redirect::TIMEOUT_ERROR);
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
     * @param IFormInterface|null $Form
     * @param TblYear             $tblYear
     * @param                     $Year
     *
     * @return IFormInterface|string
     */
    public function changeYear(
        IFormInterface &$Form,
        TblYear $tblYear,
        $Year
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Year
        ) {
            return $Form;
        }

        $Error = false;

        if (isset($Year['Year']) && empty($Year['Year'])) {
            $Form->setError('Year[Year]', 'Bitte geben Sie ein Jahr an');
            $Error = true;
        } else {
            if (($CheckYear = Term::useService()->checkYearExist($Year['Year'], $Year['Description']))) {
                if ($tblYear->getId() !== $CheckYear->getId()) {
                    $Form->setError('Year[Year]', 'Dieses Schuljahr existiert bereits');
                    $Form->setError('Year[Description]', 'Ändern Sie die Beschreibung um das Jahr wiederholt speichern zu können');
                    $Error = true;
                }
            }
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->updateYear(
                $tblYear,
                $Year['Year'],
                $Year['Description']
            )
            ) {
                $Form .= new Success('Änderungen gespeichert, die Daten werden neu geladen...')
                    . new Redirect('/Education/Lesson/Term/Create/Year', Redirect::TIMEOUT_SUCCESS);
            } else {
                $Form .= new Danger('Änderungen konnten nicht gespeichert werden')
                    . new Redirect('/Education/Lesson/Term/Create/Year', Redirect::TIMEOUT_ERROR);
            };
        }
        return $Form;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblPeriod $tblPeriod
     * @param null|array $Period
     *
     * @return IFormInterface|string
     */
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

        if (isset($Period['Name']) && empty($Period['Name'])) {
            $Stage->setError('Period[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }
        if (isset($Period['From']) && empty($Period['From'])) {
            $Stage->setError('Period[From]', 'Bitte geben Sie ein Startdatum an');
            $Error = true;
        }
        if (isset($Period['To']) && empty($Period['To'])) {
            $Stage->setError('Period[To]', 'Bitte geben Sie ein Enddatum an');
            $Error = true;
        }

        if (!$Error) {
            $dateFrom = new DateTime($Period['From']);
            $dateTo = new DateTime($Period['To']);

            if ($dateFrom > $dateTo) {
                $Stage->setError('Period[To]', new Exclamation()
                    . ' Das "Datum bis" darf nicht kleiner sein, als das "Datum von".');

                $Error = true;
            }
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->updatePeriod(
                $tblPeriod,
                $Period['Name'],
                $Period['Description'],
                $Period['From'],
                $Period['To'],
                isset($Period['IsLevel12'])
            )
            ) {
                $Stage .= new Success('Änderungen gespeichert, die Daten werden neu geladen...')
                    . new Redirect('/Education/Lesson/Term/Create/Period', Redirect::TIMEOUT_SUCCESS);
            } else {
                $Stage .= new Danger('Änderungen konnten nicht gespeichert werden')
                    . new Redirect('/Education/Lesson/Term/Create/Period', Redirect::TIMEOUT_ERROR);
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
            . new Redirect('/Education/Lesson/Term/Create/Year', Redirect::TIMEOUT_SUCCESS);
        } else {
            return new Danger('Das Jahr konnte nicht gelöscht werden')
            . new Redirect('/Education/Lesson/Term/Create/Year', Redirect::TIMEOUT_ERROR);
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
                . new Redirect('/Education/Lesson/Term/Create/Period', Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger('Der Zeitraum konnte nicht gelöscht werden')
                . new Redirect('/Education/Lesson/Term/Create/Period', Redirect::TIMEOUT_ERROR);
            }
        }
        return new Danger('Der Zeitraum wird benutzt!')
        . new Redirect('/Education/Lesson/Term/Create/Period', Redirect::TIMEOUT_ERROR);
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

    /**
     * @param string $Year
     * @param string $Description
     *
     * @return TblYear
     */
    public function insertYear($Year, $Description = '')
    {

        return (new Data($this->getBinding()))->createYear($Year, $Description);
    }

    /**
     * @param string $Name
     * @param string $From
     * @param string $To
     * @param string $Description
     * @param bool $IsLevel12
     *
     * @return TblPeriod
     */
    public function insertPeriod($Name, $From, $To, $Description = '', $IsLevel12 = false)
    {

        return (new Data($this->getBinding()))->createPeriod($Name, $From, $To, $Description, $IsLevel12);
    }

    /**
     * @param TblYear $tblYear
     * @param TblPeriod $tblPeriod
     *
     * @return TblYearPeriod
     */
    public function insertYearPeriod(TblYear $tblYear, TblPeriod $tblPeriod)
    {

        return (new Data($this->getBinding()))->addYearPeriod($tblYear, $tblPeriod);
    }

    /**
     * @param $Id
     *
     * @return false|TblHolidayType
     */
    public function getHolidayTypeById($Id)
    {

        return (new Data($this->getBinding()))->getHolidayTypeById($Id);
    }

    /**
     * @param $Identifier
     *
     * @return false|TblHolidayType
     */
    public function getHolidayTypeByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getHolidayTypeByIdentifier($Identifier);
    }

    /**
     * @param $Name
     *
     * @return false|TblHolidayType
     */
    public function getHolidayTypeByName($Name)
    {
        return (new Data($this->getBinding()))->getHolidayTypeByName($Name);
    }

    /**
     * @return false|TblHolidayType[]
     */
    public function getHolidayTypeAll()
    {

        return (new Data($this->getBinding()))->getHolidayTypeAll();
    }

    /**
     * @param $Id
     *
     * @return false|TblHoliday
     */
    public function getHolidayById($Id)
    {

        return (new Data($this->getBinding()))->getHolidayById($Id);
    }

    /**
     * @param TblYear $tblYear
     * @param DateTime $date
     * @param TblCompany|null $tblCompany
     *
     * @return false|TblHoliday
     */
    public function getHolidayByDay(TblYear $tblYear, DateTime $date, TblCompany $tblCompany = null)
    {
        if ($tblCompany) {
            if (($tblHoliday = (new Data($this->getBinding()))->getHolidayByDay($tblYear, $date, $tblCompany))) {
                // Unterrichtsfreier Tag an der Schule
                return $tblHoliday;
            } else {
                // Unterrichtsfreier Tag für alle Schulen
                return (new Data($this->getBinding()))->getHolidayByDay($tblYear, $date, null);
            }
        } else {
            return (new Data($this->getBinding()))->getHolidayByDay($tblYear, $date, null);
        }
    }

    /**
     * @param TblYear $tblYear
     * @param DateTime $date
     * @param array $tblCompanyList
     *
     * @return bool|TblHoliday
     */
    public function getHolidayByDayAndCompanyList(TblYear $tblYear, DateTime $date, array $tblCompanyList)
    {
        foreach ($tblCompanyList as $tblCompany) {
            if (($tblHoliday = (new Data($this->getBinding()))->getHolidayByDay($tblYear, $date, $tblCompany))) {
                return $tblHoliday;
            }
        }

        return (new Data($this->getBinding()))->getHolidayByDay($tblYear, $date, null);
    }

    /**
     * @return false|TblHoliday[]
     */
    public function getHolidayAll()
    {

        return (new Data($this->getBinding()))->getHolidayAll();
    }

    /**
     * @param TblYear $tblYear
     * @param TblCompany|null $tblCompany
     *
     * @return false|TblHoliday[]
     */
    public function getHolidayAllByYear(TblYear $tblYear, TblCompany $tblCompany = null)
    {
        return (new Data($this->getBinding()))->getHolidayAllByYear($tblYear, $tblCompany);
    }

    /**
     * @param $Id
     *
     * @return false|TblYearHoliday
     */
    public function getYearHolidayById($Id)
    {

        return (new Data($this->getBinding()))->getYearHolidayById($Id);
    }

    /**
     * @param TblYear $tblYear
     * @param TblCompany|null $tblCompany
     *
     * @return false|TblYearHoliday[]
     */
    public function getYearHolidayAllByYear(TblYear $tblYear, TblCompany $tblCompany = null)
    {

        return (new Data($this->getBinding()))->getYearHolidayAllByYear($tblYear, $tblCompany);
    }

    /**
     * @param TblHoliday $tblHoliday
     *
     * @return false|TblYearHoliday[]
     */
    public function getYearHolidayAllByHoliday(TblHoliday $tblHoliday)
    {

        return (new Data($this->getBinding()))->getYearHolidayAllByHoliday($tblHoliday);
    }

        /**
     * Alle möglichen Holidays innerhalb des Schuljahres
     *
     * @param TblYear $tblYear
     *
     * @return false|TblHoliday[]
     */
    public function getHolidayAllWhereYear(TblYear $tblYear)
    {

        return (new Data($this->getBinding()))->getHolidayAllWhereYear($tblYear);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function createHoliday(IFormInterface $Stage = null, $Data)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        $Error = false;
        if (isset($Data['FromDate']) && empty($Data['FromDate'])) {
            $Stage->setError('Data[FromDate]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        } elseif (isset($Data['ToDate']) && !empty($Data['ToDate'])) {
            $dateFrom = new DateTime($Data['FromDate']);
            $dateTo = new DateTime($Data['ToDate']);

            if ($dateFrom > $dateTo) {
                $Stage->setError('Data[ToDate]', new Exclamation()
                    . ' Das "Datum bis" darf nicht kleiner sein, als das "Datum von".');

                $Error = true;
            }
        }

        if (isset($Data['Name']) && empty($Data['Name'])) {
            $Stage->setError('Data[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }
        $tblHolidayType = $this->getHolidayTypeById($Data['Type']);
        if (!$tblHolidayType) {
            $Stage->setError('Data[Type]', 'Bitte wählen Sie einen Typ aus');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createHoliday(
                $tblHolidayType,
                $Data['Name'],
                $Data['FromDate'],
                $Data['ToDate']
            );

            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Unterrichtsfreien Tage sind erfasst worden.')
            . new Redirect('/Education/Lesson/Term/Holiday', Redirect::TIMEOUT_SUCCESS
            );
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblHoliday $tblHoliday
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updateHoliday(IFormInterface $Stage = null, TblHoliday $tblHoliday, $Data)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        $Error = false;
        if (isset($Data['FromDate']) && empty($Data['FromDate'])) {
            $Stage->setError('Data[FromDate]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        } elseif (isset($Data['ToDate']) && !empty($Data['ToDate'])) {
            $dateFrom = new DateTime($Data['FromDate']);
            $dateTo = new DateTime($Data['ToDate']);

            if ($dateFrom > $dateTo) {
                $Stage->setError('Data[ToDate]', new Exclamation()
                    . ' Das "Datum bis" darf nicht kleiner sein, als das "Datum von".');

                $Error = true;
            }
        }

        if (isset($Data['Name']) && empty($Data['Name'])) {
            $Stage->setError('Data[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }
        $tblHolidayType = $this->getHolidayTypeById($Data['Type']);
        if (!$tblHolidayType) {
            $Stage->setError('Data[Type]', 'Bitte wählen Sie einen Typ aus');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->updateHoliday(
                $tblHoliday,
                $tblHolidayType,
                $Data['Name'],
                $Data['FromDate'],
                $Data['ToDate']
            );

            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Unterrichtsfreien Tage sind geändert worden.')
            . new Redirect('/Education/Lesson/Term/Holiday', Redirect::TIMEOUT_SUCCESS
            );
        }

        return $Stage;
    }

    /**
     * @param TblYear $tblYear
     * @param TblHoliday $tblHoliday
     * @param TblCompany|null $tblCompany
     *
     * @return TblYearHoliday
     */
    public function addYearHoliday(TblYear $tblYear, TblHoliday $tblHoliday, TblCompany $tblCompany = null)
    {
        return (new Data($this->getBinding()))->addYearHoliday($tblYear, $tblHoliday, $tblCompany ? $tblCompany : null);
    }

    /**
     * @param TblYear $tblYear
     * @param TblHoliday $tblHoliday
     * @param TblCompany|null $tblCompany
     *
     * @return bool
     */
    public function removeYearHoliday(TblYear $tblYear, TblHoliday $tblHoliday, TblCompany $tblCompany = null)
    {
        return (new Data($this->getBinding()))->removeYearHoliday($tblYear, $tblHoliday, $tblCompany);
    }

    /**
     * @param TblHoliday $tblHoliday
     *
     * @return bool
     */
    public function destroyHoliday(TblHoliday $tblHoliday)
    {

        // Verknüpfungen löschen
        $tblYearHolidayList = $this->getYearHolidayAllByHoliday($tblHoliday);
        if ($tblYearHolidayList){
            foreach ($tblYearHolidayList as $tblYearHoliday){
                if (($tblYear = $tblYearHoliday->getTblYear())) {
                    $tblCompany = $tblYearHoliday->getServiceTblCompany();
                    (new Data($this->getBinding()))->removeYearHoliday($tblYear, $tblHoliday, $tblCompany ? $tblCompany : null);
                }
            }
        }

        return (new Data($this->getBinding()))->destroyHoliday($tblHoliday);
    }

    /**
     * @param TblYear $tblYear
     *
     * @return array
     */
    public function getStartDateAndEndDateOfYear(TblYear $tblYear)
    {
        $startDate = false;
        $endDate = false;
        if (($tblPeriodList = $tblYear->getTblPeriodAll(null, true))) {
            foreach ($tblPeriodList as $tblPeriod) {
                if ($startDate) {
                    if ($startDate > new DateTime($tblPeriod->getFromDate())) {
                        $startDate = new DateTime($tblPeriod->getFromDate());
                    }
                } else {
                    $startDate = new DateTime($tblPeriod->getFromDate());
                }

                if ($endDate) {
                    if ($endDate < new DateTime($tblPeriod->getToDate())) {
                        $endDate = new DateTime($tblPeriod->getToDate());
                    }
                } else {
                    $endDate = new DateTime($tblPeriod->getToDate());
                }
            }
        }

        return array($startDate, $endDate);
    }

    /**
     * @param TblHolidayType $tblHolidayType
     * @param $fromDate
     * @param $toDate
     *
     * @return false|TblHoliday
     */
    public function getHolidayBy(TblHolidayType $tblHolidayType, $fromDate, $toDate)
    {
        return (new Data($this->getBinding()))->getHolidayBy($tblHolidayType, $fromDate, $toDate);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblYear $tblYear
     * @param TblCompany|null $tblCompany
     * @param $Data
     *
     * @return IFormInterface
     */
    public function importHolidayFromSystem(
        IFormInterface &$Stage = null,
        TblYear $tblYear,
        TblCompany $tblCompany = null,
        $Data
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        $error = false;
        if (!($tblState = BasicData::useService()->getStateById($Data))) {
            $Stage->setError('Data', 'Bitte wählen Sie ein Bundesland aus');
            $error = true;
        }

        if (!$error && $tblState) {
            list($startDate, $endDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
            if ($startDate && $endDate
                && ($tblHolidaySystemList = BasicData::useService()->getHolidayAllBy($startDate, $endDate))
            ) {
                foreach ($tblHolidaySystemList as $tblHolidaySystem) {
                    if (($tblHolidayTypeSystem = $tblHolidaySystem->getTblHolidayType())
                        && ($tblHolidayType = Term::useService()->getHolidayTypeByName($tblHolidayTypeSystem->getName()))
                    ) {
                        $tblStateSystem = $tblHolidaySystem->getTblState();
                        if (!$tblStateSystem || $tblStateSystem->getId() == $tblState->getId()) {
                            $tblHoliday = Term::useService()->getHolidayBy(
                                $tblHolidayType,
                                $tblHolidaySystem->getFromDateTime() ? $tblHolidaySystem->getFromDateTime() : null,
                                $tblHolidaySystem->getToDate() ? $tblHolidaySystem->getToDateTime() : null
                            );
                            if (!($tblHoliday)) {
                                $tblHoliday = (new Data($this->getBinding()))->createHoliday(
                                    $tblHolidayType,
                                    $tblHolidaySystem->getName(),
                                    $tblHolidaySystem->getFromDate(),
                                    $tblHolidaySystem->getToDate()
                                );
                            }

                            if ($tblHoliday) {
                                (new Data($this->getBinding()))->addYearHoliday($tblYear, $tblHoliday, $tblCompany);
                            }
                        }
                    }
                }

                $Stage .= new Success('Änderungen gespeichert, die Daten werden neu geladen...')
                    . new Redirect('/Education/Lesson/Term', Redirect::TIMEOUT_SUCCESS);
            }
        }

        return $Stage;
    }

    /**
     * @param string $date
     * @param TblYear $tblYear
     * @param array $tblCompanyList
     * @param bool $hasSaturdayLessons
     *
     * @return bool
     */
    public function getIsSchoolWeekHoliday(string $date, TblYear $tblYear, array $tblCompanyList, bool $hasSaturdayLessons = false): bool
    {
        $date = new DateTime($date);
        $isHoliday = false;
        for ($i = 0; $i < $hasSaturdayLessons ? 6 : 5; $i++) {
            if ($i > 0) {
                $date->add(new DateInterval('P1D'));
            }

            if ($tblCompanyList) {
                foreach ($tblCompanyList as $tblCompany) {
                    if (($isHoliday = Term::useService()->getHolidayByDay($tblYear, $date, $tblCompany))) {
                        break;
                    }
                }
            } else {
                $isHoliday = Term::useService()->getHolidayByDay($tblYear, $date, null);
            }

            if (!$isHoliday) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param TblYear $tblYear
     *
     * @return bool
     */
    public function getIsCurrentYear(TblYear $tblYear): bool
    {
        if ($tblYearListByNow = $this->getYearByNow()) {
            foreach ($tblYearListByNow as $tblYearNow) {
                if ($tblYear->getId() == $tblYearNow->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $Route
     * @param $IsAllYears
     * @param $YearId
     * @param $tblYear
     * @param $HasAllYears
     *
     * @return array
     */
    public function setYearButtonList($Route, $IsAllYears, $YearId, &$tblYear, $HasAllYears): array
    {
        $tblYear = false;
        $tblYearList = Term::useService()->getYearByNow();
        if ($YearId) {
            $tblYear = Term::useService()->getYearById($YearId);
        } elseif (!$IsAllYears && $tblYearList) {
            $tblYear = end($tblYearList);
        }

        $buttonList = array();
        if ($tblYearList) {
            $tblYearList = $this->getSorter($tblYearList)->sortObjectBy('DisplayName');
            /** @var TblYear $tblYearItem */
            foreach ($tblYearList as $tblYearItem) {
                if ($tblYear && $tblYear->getId() == $tblYearItem->getId()) {
                    $buttonList[] = (new Standard(new Info(new Bold($tblYearItem->getDisplayName())),
                        $Route, new Edit(), array('YearId' => $tblYearItem->getId())));
                } else {
                    $buttonList[] = (new Standard($tblYearItem->getDisplayName(), $Route,
                        null, array('YearId' => $tblYearItem->getId())));
                }
            }
        }

        // Fachlehrer sollen nur Zugriff auf Leistungsüberprüfungen aller aktuellen Schuljahre haben
        // #SSW-1169 Anlegen von Leistungsüberprüfung von noch nicht erreichten Schuljahren verhindern
        if ($HasAllYears) {
            if ($IsAllYears) {
                $buttonList[] = (new Standard(new Info(new Bold('Alle Schuljahre')),
                    $Route, new Edit(), array('IsAllYears' => true)));
            } else {
                $buttonList[] = (new Standard('Alle Schuljahre', $Route, null,
                    array('IsAllYears' => true)));
            }
        }

        // Abstandszeile
        $buttonList[] = new Container('&nbsp;');

        return $buttonList;
    }
}
