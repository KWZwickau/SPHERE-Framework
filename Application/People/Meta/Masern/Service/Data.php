<?php
namespace SPHERE\Application\People\Meta\Masern\Service;

use DateTime;
use SPHERE\Application\Education\School\Course\Service\Entity\TblSchoolDiploma;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalCourse;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalDiploma;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalSubjectArea;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Meta\Masern\Service\Entity\TblPersonMasern;
use SPHERE\Application\People\Meta\Student\Service\Data\Support;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentBaptism;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentBilling;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentInsuranceState;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLocker;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMasernInfo;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMedicalRecord;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSpecialNeeds;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSpecialNeedsLevel;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTechnicalSchool;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTenseOfLesson;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTrainingStatus;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransport;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentAgreement;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentBaptism;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentDisorder;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentFocus;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentIntegration;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentLiberation;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentLocker;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentMedicalRecord;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentTransfer;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentTransport;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Service\Entity\TblSiblingRank;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Meta\Masern\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblPersonMasern
     */
    public function getPersonMasernByPerson(TblPerson $tblPerson)
    {

        /** @var TblPersonMasern $Entity */
        $Entity = $this->getCachedEntityBy(__Method__, $this->getConnection()->getEntityManager(), 'TblPersonMasern',
            array(
                TblPersonMasern::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param TblPerson                 $tblPerson
     * @param DateTime|null             $MasernDate
     * @param TblStudentMasernInfo|null $MasernDocumentType
     * @param TblStudentMasernInfo|null $MasernCreatorType
     *
     * @return TblPersonMasern
     */
    public function createPersonMasern(
        TblPerson $tblPerson,
        $MasernDate = null,
        TblStudentMasernInfo $MasernDocumentType = null,
        TblStudentMasernInfo $MasernCreatorType = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblPersonMasern();
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setMasernDate($MasernDate);
        $Entity->setMasernDocumentType($MasernDocumentType);
        $Entity->setMasernCreatorType($MasernCreatorType);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblPersonMasern $tblPersonMasern
     * @param TblPerson $tblPerson
     * @param DateTime|null $MasernDate
     * @param TblStudentMasernInfo|null $MasernDocumentType
     * @param TblStudentMasernInfo|null $MasernCreatorType
     *
     * @return bool
     */
    public function updatePersonMasern(
        TblPersonMasern $tblPersonMasern,
        $tblPerson,
        $MasernDate = null,
        TblStudentMasernInfo $MasernDocumentType = null,
        TblStudentMasernInfo $MasernCreatorType = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblPersonMasern $Entity */
        $Entity = $Manager->getEntityById('TblPersonMasern', $tblPersonMasern->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setMasernDate($MasernDate);
            $Entity->setMasernDocumentType($MasernDocumentType);
            $Entity->setMasernCreatorType($MasernCreatorType);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }
}
