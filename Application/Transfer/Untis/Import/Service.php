<?php
namespace SPHERE\Application\Transfer\Untis\Import;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Transfer\Untis\Import\Service\Data;
use SPHERE\Application\Transfer\Untis\Import\Service\Entity\TblUntisImportLectureship;
use SPHERE\Application\Transfer\Untis\Import\Service\Setup;
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
     * @param int $Id
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
     * find active Account by Session
     *
     * @return false|TblUntisImportLectureship[]
     */
    public function getUntisImportLectureshipByAccount()
    {
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            return ( new Data($this->getBinding()) )->getUntisImportLectureshipByAccount($tblAccount);
        }
        return false;
    }

    /**
     * @param array   $Result
     * @param TblYear $tblYear
     *
     * @return bool
     */
    public function createUntisImportLectureShip($Result, TblYear $tblYear)
    {

        // create new import
        $tblDivision = ( $Result['DivisionId'] !== null ? Division::useService()->getDivisionById($Result['DivisionId']) : null );
        $tblTeacher = ( $Result['TeacherId'] !== null ? Teacher::useService()->getTeacherById($Result['TeacherId']) : null );
        $tblSubject = ( $Result['SubjectId'] !== null ? Subject::useService()->getSubjectById($Result['SubjectId']) : null );

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
            $tblTeacher,
            $tblSubject,
            $Result['AppSubjectGroup'],
            $tblAccount);

        return true;
    }

    /**
     * @param IFormInterface|null       $Stage
     * @param TblUntisImportLectureship $tblUntisImportLectureship
     * @param null|array                $Data
     * @param bool                      $MissingInfo
     *
     * @return IFormInterface|string
     */
    public function updateUntisImportLectureship(
        IFormInterface $Stage = null,
        TblUntisImportLectureship $tblUntisImportLectureship,
        $Data = null,
        $MissingInfo = false
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        if (isset($Data['DivisionId']) && $Data['DivisionId'] != 0) {
            $tblDivision = Division::useService()->getDivisionById($Data['DivisionId']);
        } else {
            $tblDivision = null;
        }
        if (isset($Data['TeacherId']) && $Data['TeacherId'] != 0) {
            $tblTeacher = Teacher::useService()->getTeacherById($Data['TeacherId']);
        } else {
            $tblTeacher = null;
        }
        if (isset($Data['SubjectId']) && $Data['SubjectId'] != 0) {
            $tblSubject = Subject::useService()->getSubjectById($Data['SubjectId']);
        } else {
            $tblSubject = null;
        }
        if (isset($Data['SubjectGroup'])) {
            $SubjectGroup = $Data['SubjectGroup'];
        } else {
            $SubjectGroup = '';
        }
        if (isset($Data['IsIgnore'])) {
            $IsIgnore = $Data['IsIgnore'];
        } else {
            $IsIgnore = false;
        }

        if (( new Data($this->getBinding()) )->updateUntisImportLectureship(
            $tblUntisImportLectureship,
            $tblDivision,
            $tblTeacher,
            $tblSubject,
            $SubjectGroup,
            $IsIgnore)
        ) {
            $Message = new Success('Änderungen gespeichert');
            return $Message.new Redirect('/Transfer/Untis/Import/Lectureship/Show', Redirect::TIMEOUT_SUCCESS, array('MissingInfo' => $MissingInfo));
        } else {
            $Stage->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Änderungen gespeichert')))));
            return $Stage.new Redirect('/Transfer/Untis/Import/Lectureship/Edit', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblUntisImportLectureship->getId(), 'MissingInfo' => $MissingInfo));
        }
    }

    /**
     * @param TblUntisImportLectureship $tblUntisImportLectureship
     * @param bool                      $isIgnore
     *
     * @return bool
     */
    public function updateUntisImportLectureshipIsIgnore(TblUntisImportLectureship $tblUntisImportLectureship, $isIgnore = true)
    {
        return ( new Data($this->getBinding()) )->updateUntisImportLectureshipIsIgnore($tblUntisImportLectureship, $isIgnore);
    }

    /**
     * destroy UntisImportLectureship by active Account
     *
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

    public function importUntisLectureship()
    {

        $tblUntisImportLectureshipList = $this->getUntisImportLectureshipByAccount();
        if ($tblUntisImportLectureshipList) {
            foreach ($tblUntisImportLectureshipList as $tblUntisImportLectureship) {
//                $Error = false;
//                $tblDivision = $tblUntisImportLectureship->getServiceTblDivision();
//                $tblTeacher = $tblUntisImportLectureship->getServiceTblTeacher();
//                $tblSubject = $tblUntisImportLectureship->getServiceTblSubject();
//                $SubjectGroup = $tblUntisImportLectureship->getSubjectGroup();
            }
        }
    }
}
