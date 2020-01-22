<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Lesson\Division\Division;

class SDataBerufsschule
{

    public static function setCertificateStandard(Data $Data)
    {

        self::setBsHj($Data);
    }

    private static function setBsHj(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Berufsschule Halbjahresinformation', '',
            'BsHj');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeBerufsschule()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeBerufsschule(), null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '1'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '2'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung Eingabefelder

            self::setSecondPageVariable($Data, $tblCertificate);
        }
    }

    /**
     * @param Data           $Data
     * @param TblCertificate $tblCertificate
     */
    private static function setSecondPageVariable(Data $Data, TblCertificate $tblCertificate)
    {
        // Begrenzung Remark
        $Var = 'Remark';
        if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $Var)) {
            $Data->createCertificateInformation($tblCertificate, $Var, 2);
            $Data->createCertificateField($tblCertificate, $Var, 200);
        }

        $Var = 'Operation1';
        if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $Var)) {
            $Data->createCertificateInformation($tblCertificate, $Var, 2);
        }

        $Var = 'OperationTime1';
        if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $Var)) {
            $Data->createCertificateInformation($tblCertificate, $Var, 2);
        }

        $Var = 'Operation2';
        if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $Var)) {
            $Data->createCertificateInformation($tblCertificate, $Var, 2);
        }

        $Var = 'OperationTime2';
        if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $Var)) {
            $Data->createCertificateInformation($tblCertificate, $Var, 2);
        }

        $Var = 'Operation3';
        if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $Var)) {
            $Data->createCertificateInformation($tblCertificate, $Var, 2);
        }

        $Var = 'OperationTime3';
        if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $Var)) {
            $Data->createCertificateInformation($tblCertificate, $Var, 2);
        }
    }
}