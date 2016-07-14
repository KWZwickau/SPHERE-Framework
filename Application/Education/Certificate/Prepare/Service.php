<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 10:42
 */

namespace SPHERE\Application\Education\Certificate\Prepare;

use SPHERE\Application\Education\Certificate\Prepare\Service\Data;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblCertificatePrepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Setup;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Education\Certificate\Prepare
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

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($Simulate);
        if (!$Simulate && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param $Id
     *
     * @return false|TblCertificatePrepare
     */
    public function getPrepareById($Id)
    {

        return (new Data($this->getBinding()))->getPrepareById($Id);
    }

    /**
     * @param TblDivision $tblDivision
     * @param null $IsApproved
     * @param null $IsPrinted
     *
     * @return false|TblCertificatePrepare[]
     */
    public function getPrepareAllByDivision(TblDivision $tblDivision, $IsApproved = null, $IsPrinted = null)
    {

        return (new Data($this->getBinding()))->getPrepareAllByDivision($tblDivision, $IsApproved, $IsPrinted);
    }

    /**
     * Fach-Note
     *
     * @param TblCertificatePrepare $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblTestType $tblTestType
     *
     * @return false|TblPrepareGrade
     */
    public function getPrepareGradeBySubject(
        TblCertificatePrepare $tblPrepare,
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblTestType $tblTestType
    ) {

        return (new Data($this->getBinding()))->getPrepareGradeBySubject(
            $tblPrepare,
            $tblPerson,
            $tblDivision,
            $tblSubject,
            $tblTestType
        );
    }

    /**
     * Kopf-Note
     *
     * @param TblCertificatePrepare $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblTestType $tblTestType
     * @param TblGradeType $tblGradeType
     *
     * @return false|TblPrepareGrade
     */
    public function getPrepareGradeByGradeType(
        TblCertificatePrepare $tblPrepare,
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblTestType $tblTestType,
        TblGradeType $tblGradeType
    ) {

        return (new Data($this->getBinding()))->getPrepareGradeByGradeType($tblPrepare, $tblPerson, $tblDivision,
            $tblTestType, $tblGradeType);
    }

    /**
     * @param TblCertificatePrepare $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblTestType $tblTestType
     *
     * @return false|TblPrepareGrade[]
     */
    public function getPrepareGradeAllByPerson(
        TblCertificatePrepare $tblPrepare,
        TblPerson $tblPerson,
        TblTestType $tblTestType
    ) {

        return (new Data($this->getBinding()))->getPrepareGradeAllByPerson($tblPrepare, $tblPerson, $tblTestType);
    }

    /**
     * @param TblCertificatePrepare $tblPrepare
     * @param TblTestType $tblTestType
     *
     * @return false|TblPrepareGrade[]
     */
    public function getPrepareGradeAllByPrepare(
        TblCertificatePrepare $tblPrepare,
        TblTestType $tblTestType
    ) {

        return (new Data($this->getBinding()))->getPrepareGradeAllByPrepare(
            $tblPrepare, $tblTestType
        );
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblDivision $tblDivision
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function createPrepare(IFormInterface $Stage = null, TblDivision $tblDivision, $Data)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        $Error = false;
        if (isset($Data['Date']) && empty($Data['Date'])) {
            $Stage->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (isset($Data['Name']) && empty($Data['Name'])) {
            $Stage->setError('Data[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createPrepare(
                $tblDivision,
                $Data['Date'],
                $Data['Name']
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Zeugnisvorbereitung ist erfasst worden.')
            . new Redirect('/Education/Certificate/Prepare/Prepare', Redirect::TIMEOUT_SUCCESS, array(
                'DivisionId' => $tblDivision->getId()
            ));
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblCertificatePrepare $tblPrepare
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updatePrepareSetAppointedDateTask(
        IFormInterface $Stage = null,
        TblCertificatePrepare $tblPrepare,
        $Data
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        $Error = false;
        $tblTask = Evaluation::useService()->getTaskById($Data);
        if (!$tblTask) {
            $Stage->setError('Data', 'Bitte wählen Sie einen Stichtagsnotenauftrag aus');
            $Error = true;
        }

        if (!$Error) {
            // Löschen der vorhandenen Zensuren
            (new Data($this->getBinding()))->destroyPrepareGrades($tblPrepare, $tblTask->getTblTestType());

            $tblDivision = $tblPrepare->getServiceTblDivision();
            $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblTask);
            $gradeList = array();
            if ($tblDivision) {
                $tblStudentListByDivision = Division::useService()->getStudentAllByDivision($tblDivision);
                $tblYear = $tblDivision->getServiceTblYear();
                if ($tblStudentListByDivision && $tblYear && $tblTestAllByTask) {
                    foreach ($tblTestAllByTask as $tblTest) {
                        foreach ($tblStudentListByDivision as $tblPerson) {
                            $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest, $tblPerson);
                            if ($tblGrade) {
                                $gradeList[$tblPerson->getId()][$tblGrade->getId()] = $tblGrade;
                            }
                        }
                    }
                }
            }

            // Speichern der Zensuren aus dem Stichtagsnotenauftrag
            if (!empty($gradeList)) {
                (new Data($this->getBinding()))->createPrepareGrades(
                    $tblPrepare,
                    $tblTask->getTblTestType(),
                    $gradeList
                );
            }

            (new Data($this->getBinding()))->updatePrepare(
                $tblPrepare,
                $tblPrepare->getDate(),
                $tblPrepare->getName(),
                $tblPrepare->isApproved(),
                $tblPrepare->isPrinted(),
                $tblTask,
                $tblPrepare->getServiceTblBehaviorTask() ? $tblPrepare->getServiceTblBehaviorTask() : null
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Stichtagsnotenauftrag wurde ausgewählt.')
            . new Redirect('/Education/Certificate/Prepare/Division', Redirect::TIMEOUT_SUCCESS, array(
                'PrepareId' => $tblPrepare->getId()
            ));
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblCertificatePrepare $tblPrepare
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updatePrepareSetBehaviorTask(
        IFormInterface $Stage = null,
        TblCertificatePrepare $tblPrepare,
        $Data
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        $Error = false;
        $tblTask = Evaluation::useService()->getTaskById($Data);
        if (!$tblTask) {
            $Stage->setError('Data', 'Bitte wählen Sie einen Kopfnotenauftrag aus');
            $Error = true;
        }

        if (!$Error) {
            // Löschen der vorhandenen Zensuren
            (new Data($this->getBinding()))->destroyPrepareGrades($tblPrepare, $tblTask->getTblTestType());

            (new Data($this->getBinding()))->updatePrepare(
                $tblPrepare,
                $tblPrepare->getDate(),
                $tblPrepare->getName(),
                $tblPrepare->isApproved(),
                $tblPrepare->isPrinted(),
                $tblPrepare->getServiceTblAppointedDateTask() ? $tblPrepare->getServiceTblAppointedDateTask() : null,
                $tblTask

            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Kopfnotenauftrag wurde ausgewählt.')
            . new Redirect('/Education/Certificate/Prepare/BehaviorTask', Redirect::TIMEOUT_SUCCESS, array(
                'PrepareId' => $tblPrepare->getId()
            ));
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblCertificatePrepare $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblScoreType|null $tblScoreType
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updatePrepareGradeForBehaviorTask(
        IFormInterface $Stage = null,
        TblCertificatePrepare $tblPrepare,
        TblPerson $tblPerson,
        TblScoreType $tblScoreType = null,
        $Data
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        if ($tblScoreType === null) {
            $tblScoreType = Gradebook::useService()->getScoreTypeByIdentifier('GRADES');
        }
        $error = false;
        if (is_array($Data)) {
            foreach ($Data as $gradeTypeId => $value) {
                if (trim($value) !== '' && $tblScoreType) {
                    if (!preg_match('!' . $tblScoreType->getPattern() . '!is', trim($value))) {
                        $error = true;
                        break;
                    }
                }
            }

            if ($error) {
                $Stage->prependGridGroup(
                    new FormGroup(new FormRow(new FormColumn(new Danger(
                            'Nicht alle eingebenen Zensuren befinden sich im Wertebereich.
                        Die Daten wurden nicht gespeichert.', new Exclamation())
                    ))));

                return $Stage;
            } else {
                if (($tblTask = $tblPrepare->getServiceTblBehaviorTask())
                    && ($tblTestType = $tblTask->getTblTestType())
                    && ($tblDivision = $tblPrepare->getServiceTblDivision())
                ) {
                    foreach ($Data as $gradeTypeId => $value) {
                        if (trim($value) && trim($value) !== ''
                            && ($tblGradeType = Gradebook::useService()->getGradeTypeById($gradeTypeId))
                        ) {
                            (new Data($this->getBinding()))->updatePrepareGradeForBehavior(
                                $tblPrepare, $tblPerson, $tblDivision, $tblTestType, $tblGradeType, trim($value)
                            );
                        }
                    }

                    return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Kopfnoten wurden gespeichert.')
                    . new Redirect('/Education/Certificate/Prepare/BehaviorGrades', Redirect::TIMEOUT_SUCCESS, array(
                        'PrepareId' => $tblPrepare->getId(),
                        'PersonId' => $tblPerson->getId(),
                    ));

                }
            }
        }

        return $Stage;
    }
}