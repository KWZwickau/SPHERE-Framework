<?php
namespace SPHERE\Application\Setting\Consumer\School;

use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Setting\Consumer\Responsibility\Responsibility;
use SPHERE\Application\Setting\Consumer\Responsibility\Service\Entity\TblResponsibility;
use SPHERE\Application\Setting\Consumer\School\Service\Data;
use SPHERE\Application\Setting\Consumer\School\Service\Entity\TblSchool;
use SPHERE\Application\Setting\Consumer\School\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Setting\Consumer\School
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
     * @return bool|TblSchool[]
     */
    public function getSchoolAll()
    {

        return (new Data($this->getBinding()))->getSchoolAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblSchool
     */
    public function getSchoolById($Id)
    {

        return (new Data($this->getBinding()))->getSchoolById($Id);
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return false|TblSchool[]
     */
    public function getSchoolByCompany(TblCompany $tblCompany)
    {

        return (new Data($this->getBinding()))->getSchoolByCompany($tblCompany);
    }

    /**
     * @param TblCompany $tblCompany
     * @param TblType    $tblType
     *
     * @return false|TblSchool
     */
    public function getSchoolByCompanyAndType(TblCompany $tblCompany, TblType $tblType)
    {

        return (new Data($this->getBinding()))->getSchoolByCompanyAndType($tblCompany, $tblType);
    }

    /**
     * @param TblType $tblType
     *
     * @return false|TblSchool[]
     */
    public function getSchoolByType(TblType $tblType)
    {

        return (new Data($this->getBinding()))->getSchoolByType($tblType);
    }

    /**
     * @param IFormInterface $Form
     * @param integer        $School
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function createSchool(
        IFormInterface $Form,
        $Type,
        $School
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Type) {
            return $Form;
        }

        $Error = false;
        if (!( Type::useService()->getTypeById($Type['Type']) )) {
            $Form->setError('Type[Type]', 'Bitte geben Sie eine Schulart an');
            $Error = true;
        }
        if (null === $School) {
            $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Bitte wählen Sie eine Schule aus')))));
            $Error = true;
        } else {
            $tblCompany = Company::useService()->getCompanyById($School);
            if (!$tblCompany) {
                $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Bitte wählen Sie eine Schule aus')))));
                $Error = true;
            }
        }

        if (!$Error) {
            $tblCompany = Company::useService()->getCompanyById($School);
            $tblType = Type::useService()->getTypeById($Type['Type']);
            if ($tblCompany && (new Data($this->getBinding()))->addSchool($tblCompany, $tblType)
            ) {
                return new Success('Die Schule wurde erfolgreich hinzugefügt')
                .new Redirect('/Setting/Consumer/School', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblCompany->getId()));
            } else {
                return new Danger('Die Schule konnte nicht hinzugefügt werden')
                .new Redirect('/Setting/Consumer/School', Redirect::TIMEOUT_ERROR, array('Id' => $tblCompany->getId()));
            }
        }

        return $Form;
    }

    /**
     * @param IFormInterface $Form
     * @param TblSchool      $tblSchool
     * @param string         $CompanyNumber
     * @param array          $School
     *
     * @return IFormInterface|string
     */
    public function updateSchool(IFormInterface $Form, TblSchool $tblSchool, $CompanyNumber = '', $School)
    {
        /**
         * Skip to Frontend
         */
        if (null === $School) {
            return $Form;
        }

        if ((new Data($this->getBinding()))->updateSchool($tblSchool, $CompanyNumber)) {
            return new Success('Die Unternehmensnr. des Unfallversicherungsträgers wurde erfolgreich gespeichert')
                .new Redirect('/Setting/Consumer/School', Redirect::TIMEOUT_SUCCESS);
        }
        return new Danger('Die Unternehmensnr. des Unfallversicherungsträgers konnte nicht gespeichert werden')
            .new Redirect('/Setting/Consumer/School', Redirect::TIMEOUT_ERROR);
    }

    /**
     * @param TblSchool|null $tblSchool
     *
     * @return string
     * choose Standard from Responsibility if School is empty
     */
    public function getCompanyNumber(TblSchool $tblSchool = null)
    {

        $result = '';
        if ($tblSchool) {
            $result = $tblSchool->getCompanyNumber();
        }
        if ($result == '') {
            $tblResponsibilityList = Responsibility::useService()->getResponsibilityAll();
            if ($tblResponsibilityList) {
                /** @var TblResponsibility $tblResponsibility */
                $tblResponsibility = current($tblResponsibilityList);
                $result = $tblResponsibility->getCompanyNumber();
            }
        }

        return $result;
    }

    /**
     * @param TblSchool $tblSchool
     *
     * @return bool
     */
    public function destroySchool(TblSchool $tblSchool)
    {

        return (new Data($this->getBinding()))->removeSchool($tblSchool);
    }

    /**
     * @return bool
     */
    public function hasConsumerTechnicalSchool()
    {
        if (($tblSchoolAll = $this->getSchoolAll())) {
            foreach($tblSchoolAll as $tblSchool) {
                if (($tblType = $tblSchool->getServiceTblType())
                    && $tblType->isTechnical()
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getIsConsumerSpecialNeedSchool()
    {
        if (($tblSchoolAll = $this->getSchoolAll())) {
            foreach($tblSchoolAll as $tblSchool) {
                if (($tblType = $tblSchool->getServiceTblType())
                    && $tblType->getShortName() == 'FöS'
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return TblType[]|false
     */
    public function getConsumerSchoolTypeAll()
    {
        if (($tblSchoolAll = $this->getSchoolAll())) {
            $list = array();
            foreach($tblSchoolAll as $tblSchool) {
                if (($tblType = $tblSchool->getServiceTblType())
                ) {
                   $list[$tblType->getShortName()] = $tblType;
                }
            }

            return $list;
        }

        return false;
    }

    /**
     * nur allgemeinbildende Schularten
     *
     * @return TblType[]|false
     */
    public function getConsumerSchoolTypeCommonAll()
    {
        if (($tblSchoolAll = $this->getSchoolAll())) {
            $list = array();
            foreach($tblSchoolAll as $tblSchool) {
                if (($tblType = $tblSchool->getServiceTblType())
                    && ($tblCategory = $tblType->getTblCategory())
                    && $tblCategory->getIdentifier() == 'COMMON'
                ) {
                    $list[$tblType->getShortName()] = $tblType;
                }
            }

            return $list;
        }

        return false;
    }
}
