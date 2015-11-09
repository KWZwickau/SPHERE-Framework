<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentSubject")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentSubject extends Element
{

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblStudentSubjectProfile;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblStudentSubjectForeignLanguage;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblStudentSubjectElective;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblStudentSubjectTeam;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblStudentSubjectTrack;

    /**
     * @return bool|TblStudentSubjectProfile
     */
    public function getServiceTblStudentSubjectProfile()
    {

        if (null === $this->serviceTblStudentSubjectProfile) {
            return false;
        } else {
            return Student::useService()->getStudentSubjectProfileById($this->serviceTblStudentSubjectProfile);
        }
    }

    /**
     * @param TblStudentSubjectProfile|null $tblStudentSubjectProfile
     */
    public function setServiceTblStudentSubjectProfile(TblStudentSubjectProfile $tblStudentSubjectProfile = null)
    {

        $this->serviceTblStudentSubjectProfile = ( null === $tblStudentSubjectProfile ? null : $tblStudentSubjectProfile->getId() );
    }

    /**
     * @return bool|TblStudentSubjectElective
     */
    public function getServiceTblStudentSubjectElective()
    {

        if (null === $this->serviceTblStudentSubjectElective) {
            return false;
        } else {
            return Student::useService()->getStudentSubjectElectiveById($this->serviceTblStudentSubjectElective);
        }
    }

    /**
     * @param TblStudentSubjectElective|null $tblStudentSubjectElective
     */
    public function setServiceTblStudentSubjectElective(TblStudentSubjectElective $tblStudentSubjectElective = null)
    {

        $this->serviceTblStudentSubjectElective = ( null === $tblStudentSubjectElective ? null : $tblStudentSubjectElective->getId() );
    }

    /**
     * @return bool|TblStudentSubjectTeam
     */
    public function getServiceTblStudentSubjectTeam()
    {

        if (null === $this->serviceTblStudentSubjectTeam) {
            return false;
        } else {
            return Student::useService()->getStudentSubjectTeamById($this->serviceTblStudentSubjectTeam);
        }
    }

    /**
     * @param TblStudentSubjectTeam|null $tblStudentSubjectTeam
     */
    public function setServiceTblStudentSubjectTeam(TblStudentSubjectTeam $tblStudentSubjectTeam = null)
    {

        $this->serviceTblStudentSubjectTeam = ( null === $tblStudentSubjectTeam ? null : $tblStudentSubjectTeam->getId() );
    }

    /**
     * @return bool|TblStudentSubjectTrack
     */
    public function getServiceTblStudentSubjectTrack()
    {

        if (null === $this->serviceTblStudentSubjectTrack) {
            return false;
        } else {
            return Student::useService()->getStudentSubjectTrackById($this->serviceTblStudentSubjectTrack);
        }
    }

    /**
     * @param TblStudentSubjectTrack|null $tblStudentSubjectTrack
     */
    public function setServiceTblStudentSubjectTrack(TblStudentSubjectTrack $tblStudentSubjectTrack = null)
    {

        $this->serviceTblStudentSubjectTrack = ( null === $tblStudentSubjectTrack ? null : $tblStudentSubjectTrack->getId() );
    }

    /**
     * @return bool|TblStudentSubjectForeignLanguage
     */
    public function getServiceTblStudentSubjectForeignLanguage()
    {

        if (null === $this->serviceTblStudentSubjectForeignLanguage) {
            return false;
        } else {
            return Student::useService()->getStudentSubjectForeignLanguageById($this->serviceTblStudentSubjectForeignLanguage);
        }
    }

    /**
     * @param TblStudentSubjectForeignLanguage|null $tblStudentSubjectForeignLanguage
     */
    public function setServiceTblStudentSubjectForeignLanguage(
        TblStudentSubjectForeignLanguage $tblStudentSubjectForeignLanguage = null
    ) {

        $this->serviceTblStudentSubjectForeignLanguage = ( null === $tblStudentSubjectForeignLanguage ? null : $tblStudentSubjectForeignLanguage->getId() );
    }
}
