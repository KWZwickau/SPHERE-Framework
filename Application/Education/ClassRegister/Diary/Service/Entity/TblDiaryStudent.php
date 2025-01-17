<?php

namespace SPHERE\Application\Education\ClassRegister\Diary\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Diary\Diary;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblClassRegisterDiaryStudent")
 * @Cache(usage="READ_ONLY")
 */
class TblDiaryStudent extends Element
{
    const ATTR_TBL_DIARY = 'tblClassRegisterDiary';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';

    /**
     * @Column(type="bigint")
     */
    protected $tblClassRegisterDiary;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @return bool|TblDiary
     */
    public function getTblDiary()
    {
        if (null === $this->tblClassRegisterDiary) {
            return false;
        } else {
            return Diary::useService()->getDiaryById($this->tblClassRegisterDiary);
        }
    }

    /**
     * @param TblDiary|null $tblClassRegisterDiary
     */
    public function setTblDiary(TblDiary $tblClassRegisterDiary = null)
    {
        $this->tblClassRegisterDiary = (null === $tblClassRegisterDiary ? null : $tblClassRegisterDiary->getId());
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPerson()
    {
        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {
        $this->serviceTblPerson = (null === $tblPerson ? null : $tblPerson->getId());
    }
}