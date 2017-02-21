<?php
namespace SPHERE\Application\Transfer\Untis\Import;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Transfer\Untis\Import\Service\Data;
use SPHERE\Application\Transfer\Untis\Import\Service\Entity\TblUntisImportLectureship;
use SPHERE\Application\Transfer\Untis\Import\Service\Setup;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Untis\Import
 */
class Service extends AbstractService
{
    /**
     * @param bool $Simulate
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($Simulate, $withData)
    {
        $Protocol = ( new Setup($this->getStructure()) )->setupDatabaseSchema($Simulate);
        if (!$Simulate && $withData) {
            ( new Data($this->getBinding()) )->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param $Id
     *
     * @return false|TblUntisImportLectureship
     */
    public function getUntisImportLectureshipById($Id)
    {

        return ( new Data($this->getBinding()) )->getUntisImportLectureshipById($Id);
    }

    /**
     * @return false|TblUntisImportLectureship[]
     */
    public function getUntisImportLectureshipAll()
    {
        return ( new Data($this->getBinding()) )->getUntisImportLectureshipAll();
    }

    /**
     * @return false|TblUntisImportLectureship[]
     */
    public function getUntisImportLectureshipByAccount()
    {
        $tblAccount = Account::useService()->getAccountBySession();
        return ( new Data($this->getBinding()) )->getUntisImportLectureshipByAccount($tblAccount);
    }

    /**
     * @param         $Result
     *
     * @param TblYear $tblYear
     *
     * @return bool
     */
    public function createUntisImportLectureShip($Result, TblYear $tblYear)
    {

        // create new import
        $tblDivision = ( $Result['DivisionId'] !== null ? Division::useService()->getDivisionById($Result['DivisionId']) : null );
        $tblPerson = ( $Result['TeacherId'] !== null ? Person::useService()->getPersonById($Result['TeacherId']) : null );
        $tblSubject = ( $Result['SubjectId'] !== null ? Subject::useService()->getSubjectById($Result['SubjectId']) : null );
        $tblSubjectGroup = ( $Result['SubjectGroupId'] !== null ? Division::useService()->getSubjectGroupById($Result['SubjectGroupId']) : null );

        $tblAccount = Account::useService()->getAccountBySession();
        if (!$tblAccount) {
            $tblAccount = null;
        }

        ( new Data($this->getBinding()) )->createUntisImportLectureship(
            $tblYear,
            $Result['FileDivision'],
            $Result['FileTeacher'],
            $Result['FileSubject'],
            $Result['FileSubjectGroup'],
            $tblDivision,
            $tblPerson,
            $tblSubject,
            $tblSubjectGroup,
            $tblAccount);

        return true;
    }

    /**
     * @return bool
     */
    public function destroyUntisImportLectureship()
    {

        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            return ( new Data($this->getBinding()) )->destroyUntisImportLectureshipByAccount($tblAccount);
        }
        return false;
    }

}
