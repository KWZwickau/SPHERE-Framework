<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 25.11.2016
 * Time: 11:24
 */

namespace SPHERE\Application\Education\Certificate\Generate;

use SPHERE\Application\Education\Certificate\Generate\Service\Data;
use SPHERE\Application\Education\Certificate\Generate\Service\Entity\TblGenerateCertificate;
use SPHERE\Application\Education\Certificate\Generate\Service\Setup;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

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
     * @return false|TblGenerateCertificate
     */
    public function getGenerateCertificateById($Id)
    {

        return (new Data($this->getBinding()))->getGenerateCertificateById($Id);
    }

    /**
     * @param TblYear $tblYear
     *
     * @return false|TblGenerateCertificate[]
     */
    public function getGenerateCertificateAllByYear(TblYear $tblYear)
    {

        return (new Data($this->getBinding()))->getGenerateCertificateAllByYear($tblYear);
    }

    /**
     * @param IFormInterface|null $Form
     * @param null $Data
     * @param TblYear $tblYear
     *
     * @return IFormInterface|string
     */
    public function createGenerateCertificate(IFormInterface $Form = null, $Data = null, TblYear $tblYear)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Form;
        }

        $Error = false;
        if (isset($Data['Date']) && empty($Data['Date'])) {
            $Form->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (!($tblCertificateType = Generator::useService()->getCertificateTypeById($Data['Type']))) {
            $Form->setError('Data[Type]', 'Bitte wählen Sie einen Typ aus');
            $Error = true;
        }

        if ($Error) {
            return $Form;
        }

        $tblAppointedDateTask = Evaluation::useService()->getTaskById($Data['AppointedDateTask']);
        $tblBehaviorTask = Evaluation::useService()->getTaskById($Data['BehaviorTask']);

        if ($tblAppointedDateTask && $tblBehaviorTask){
            $Name = $tblAppointedDateTask->getName() . ', ' . $tblBehaviorTask->getName();
        } elseif ($tblAppointedDateTask){
            $Name = $tblAppointedDateTask->getName();
        } elseif ($tblBehaviorTask){
            $Name = $tblBehaviorTask->getName();
        } else {
            $Name = $tblCertificateType->getName();
        }

        (new Data($this->getBinding()))->createGenerateCertificate(
            $tblYear,
            $Data['Date'],
            $Name,
            $tblCertificateType,
            $tblAppointedDateTask ? $tblAppointedDateTask : null,
            $tblBehaviorTask ? $tblBehaviorTask : null,
            $Data['HeadmasterName'],
            $Data['IsTeacherAvailable']
        );

        return new Success('Die Zeugniserstellung ist angelegt worden',
            new \SPHERE\Common\Frontend\Icon\Repository\Success())
        . new Redirect('/Education/Certificate/Generate', Redirect::TIMEOUT_SUCCESS);
    }
}