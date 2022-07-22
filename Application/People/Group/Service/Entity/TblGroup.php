<?php
namespace SPHERE\Application\People\Group\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

/**
 * @Entity
 * @Table(name="tblGroup")
 * @Cache(usage="READ_ONLY")
 */
class TblGroup extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_IS_LOCKED = 'IsLocked';
    const ATTR_IS_CORE_GROUP = 'IsCoreGroup';
    const ATTR_META_TABLE = 'MetaTable';

    const META_TABLE_COMMON = 'COMMON';
    const META_TABLE_PROSPECT = 'PROSPECT';
    const META_TABLE_STUDENT = 'STUDENT';
    const META_TABLE_CUSTODY = 'CUSTODY';
    const META_TABLE_STAFF = 'STAFF';
    const META_TABLE_TEACHER = 'TEACHER';
    const META_TABLE_CLUB = 'CLUB';
    const META_TABLE_COMPANY_CONTACT = 'COMPANY_CONTACT';
    const META_TABLE_TUDOR = 'TUDOR';
    const META_TABLE_DEBTOR = 'DEBTOR';
    const META_TABLE_ARCHIVE = 'ARCHIVE';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Description;
    /**
     * @Column(type="text")
     */
    protected $Remark;
    /**
     * @Column(type="boolean")
     */
    protected $IsLocked;
    /**
     * @Column(type="string")
     */
    protected $MetaTable;
    /**
     * @Column(type="boolean")
     */
    protected $IsCoreGroup;

    /**
     * @param $Name
     */
    public function __construct($Name)
    {

        $this->setName($Name);
    }

    /**
     * @return string
     */
    public function getDescription($isShowCoreInfo = false, $isExcel = false)
    {
        if($isShowCoreInfo && $this->isCoreGroup()){
            $text = ' (Stammgruppe)';
            return $this->Description . ($isExcel ? $text : new Muted($text));
        }
        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription($Description)
    {

        $this->Description = $Description;
    }

    /**
     * @return string
     */
    public function getRemark()
    {

        return $this->Remark;
    }

    /**
     * @param string $Remark
     */
    public function setRemark($Remark)
    {

        $this->Remark = $Remark;
    }

    /**
     * @return bool
     */
    public function isLocked()
    {

        return (bool)$this->IsLocked;
    }

    /**
     * @param bool $IsLocked
     */
    public function setLocked($IsLocked)
    {

        $this->IsLocked = (bool)$IsLocked;
    }

    /**
     * @return string
     */
    public function getMetaTable()
    {

        return $this->MetaTable;
    }

    /**
     * @param string $MetaTable
     */
    public function setMetaTable($MetaTable)
    {

        $this->MetaTable = $MetaTable;
    }

    /**
     * @return string
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name)
    {

        $this->Name = $Name;
    }

    /**
     * @return bool|TblPerson[]
     */
    public function getTudors()
    {

        return Group::useService()->getTudors($this);
    }

    /**
     * @return bool
     */
    public function isCoreGroup()
    {

        return (bool)$this->IsCoreGroup;
    }

    /**
     * @param bool $IsCoreGroup
     */
    public function setCoreGroup($IsCoreGroup)
    {

        $this->IsCoreGroup = (bool)$IsCoreGroup;
    }

    /**
     * @param bool $hasPrefix
     *
     * @return string
     */
    public function getTudorsString($hasPrefix = true): string
    {
        if (($tudors = $this->getTudors())) {
            $list = array();
            foreach ($tudors as $tblPerson) {
                $list[] = $tblPerson->getFullName();
            }
            return ($hasPrefix ? 'Tudoren: ' : '') . implode(', ', $list);
        } else {
            return '';
        }
    }

    /**
     * @return bool|TblYear
     */
    public function getCurrentYear()
    {
        if(($tblPersonList = Group::useService()->getPersonAllByGroup($this))) {
            foreach ($tblPersonList as $tblPerson) {
                if (($tblMainDivision = Student::useService()->getCurrentMainDivisionByPerson($tblPerson))) {
                    return $tblMainDivision->getServiceTblYear();
                }
            }
        }

        return false;
    }

    /**
     * @return TblCompany[]
     */
    public function getCurrentCompanyList(): array
    {
        $list = array();
        if(($tblPersonList = Group::useService()->getPersonAllByGroup($this))) {
            foreach ($tblPersonList as $tblPerson) {
                if (($tblMainDivision = Student::useService()->getCurrentMainDivisionByPerson($tblPerson))
                    && ($tblCompany = $tblMainDivision->getServiceTblCompany())
                ) {
                    $list[$tblCompany->getId()] = $tblCompany;
                }
            }
        }

        return $list;
    }

    /**
     * @return false|TblCompany
     */
    public function getCurrentCompanySingle()
    {
        if (($list = $this->getCurrentCompanyList())) {
            return reset($list);
        }

        return false;
    }

    /**
     * @return false|TblDivision[]
     */
    public function getCurrentDivisionList()
    {
        $list = array();
        if(($tblPersonList = Group::useService()->getPersonAllByGroup($this))) {
            foreach ($tblPersonList as $tblPerson) {
                if (($tblMainDivision = Student::useService()->getCurrentMainDivisionByPerson($tblPerson))
                ) {
                    $list[$tblMainDivision->getId()] = $tblMainDivision;
                }
            }
            $list = (new Extension())->getSorter($list)->sortObjectBy('DisplayName', new StringNaturalOrderSorter());
        }

        return empty($list) ? false : $list;
    }

    /**
     * @return bool
     */
    public function getIsGroupCourseSystem(): bool
    {
        if (($tblDivisionList = $this->getCurrentDivisionList())) {
            foreach ($tblDivisionList as $tblDivision) {
                if (Division::useService()->getIsDivisionCourseSystem($tblDivision)) {
                    return true;
                }
            }
        }

        return false;
    }
}
