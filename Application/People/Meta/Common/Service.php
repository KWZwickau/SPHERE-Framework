<?php
namespace SPHERE\Application\People\Meta\Common;

use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Meta\Common\Service\Data;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommon;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Meta\Common\Service\Setup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Service
 *
 * @package SPHERE\Application\People\Meta\Common
 */
class Service implements IServiceInterface
{

    /** @var null|Binding */
    private $Binding = null;
    /** @var null|Structure */
    private $Structure = null;

    /**
     * Define Database Connection
     *
     * @param Identifier $Identifier
     * @param string     $EntityPath
     * @param string     $EntityNamespace
     */
    public function __construct(Identifier $Identifier, $EntityPath, $EntityNamespace)
    {

        $this->Binding = new Binding($Identifier, $EntityPath, $EntityNamespace);
        $this->Structure = new Structure($Identifier);
    }

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->Structure))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->Binding))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param IFormInterface $Form
     * @param TblPerson      $tblPerson
     * @param array          $Meta
     *
     * @return IFormInterface|Redirect
     */
    public function createMeta(IFormInterface $Form = null, TblPerson $tblPerson, $Meta)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Meta) {
            return $Form;
        }

        $tblCommon = $this->getCommonByPerson($tblPerson);
        if ($tblCommon) {
            (new Data($this->Binding))->updateCommonBirthDates(
                $tblCommon->getTblCommonBirthDates(),
                $Meta['BirthDates']['Birthday'],
                $Meta['BirthDates']['Birthplace'],
                $Meta['BirthDates']['Gender']
            );
            (new Data($this->Binding))->updateCommonInformation(
                $tblCommon->getTblCommonInformation(),
                $Meta['Information']['Nationality'],
                $Meta['Information']['Denomination'],
                $Meta['Information']['IsAssistance'],
                $Meta['Information']['AssistanceActivity']
            );
            (new Data($this->Binding))->updateCommon(
                $tblCommon,
                $Meta['Remark']
            );
        } else {
            $tblCommonBirthDates = (new Data($this->Binding))->createCommonBirthDates(
                $Meta['BirthDates']['Birthday'],
                $Meta['BirthDates']['Birthplace'],
                $Meta['BirthDates']['Gender']
            );
            $tblCommonInformation = (new Data($this->Binding))->createCommonInformation(
                $Meta['Information']['Nationality'],
                $Meta['Information']['Denomination'],
                $Meta['Information']['IsAssistance'],
                $Meta['Information']['AssistanceActivity']
            );
            (new Data($this->Binding))->createCommon(
                $tblPerson,
                $tblCommonBirthDates,
                $tblCommonInformation,
                $Meta['Remark']
            );
        }
        return new Success('Die Daten wurde erfolgreich gespeichert')
        .new Redirect('/People/Person', 3, array('Id' => $tblPerson->getId()));
    }

    /**
     *
     * @param TblPerson $tblPerson
     *
     * @return bool|TblCommon
     */
    public function getCommonByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->Binding))->getCommonByPerson($tblPerson);
    }

    /***
     * @param TblPerson $tblPerson
     * @param           $Birthday
     * @param           $Birthplace
     *
     * @param           $Denomination
     */
    public function createMetaFromImport(TblPerson $tblPerson, $Birthday, $Birthplace, $Denomination)
    {

        $tblCommonBirthDates = (new Data($this->Binding))->createCommonBirthDates(
            $Birthday,
            $Birthplace,
            0
        );
        $tblCommonInformation = (new Data($this->Binding))->createCommonInformation(
            '',
            $Denomination,
            0,
            ''
        );
        (new Data($this->Binding))->createCommon(
            $tblPerson,
            $tblCommonBirthDates,
            $tblCommonInformation,
            ''
        );
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCommon
     */
    public function getCommonById($Id)
    {

        return (new Data($this->Binding))->getCommonById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCommonBirthDates
     */
    public function getCommonBirthDatesById($Id)
    {

        return (new Data($this->Binding))->getCommonBirthDatesById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCommonInformation
     */
    public function getCommonInformationById($Id)
    {

        return (new Data($this->Binding))->getCommonInformationById($Id);
    }

    /**
     * @return bool|TblCommonInformation[]
     */
    public function getCommonInformationAll()
    {

        return (new Data($this->Binding))->getCommonInformationAll();
    }

    /**
     * @return bool|TblCommonBirthDates[]
     */
    public function getCommonBirthDatesAll()
    {

        return (new Data($this->Binding))->getCommonBirthDatesAll();
    }
}
