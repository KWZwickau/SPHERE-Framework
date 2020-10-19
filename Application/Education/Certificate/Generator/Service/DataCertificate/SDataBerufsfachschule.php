<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;

/**
 * Class SDataBerufsfachschule
 * @package SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate
 */
class SDataBerufsfachschule
{

    /**
     * @param Data $Data
     */
    public static function setCertificateStandard(Data $Data)
    {

        self::setBfsHjInfo($Data);
        self::setBfsHj($Data);
        self::setBfsJ(($Data));
        self::setBfsAbg(($Data));
    }

    /**
     * @param Data $Data
     */
    private static function setBfsHjInfo(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Berufsfachschule Halbjahresinformation', '',
            'BfsHjInfo');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeBerufsfachschule()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeBerufsfachschule(), null, true);
                // Automaitk soll hier nicht entscheiden
//                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
//                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeBerufsfachschule(), '1'))) {
//                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
//                    }
//                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeBerufsfachschule(), '2'))) {
//                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
//                    }
//                }
            }
            // Begrenzung Eingabefelder
            // Begrenzung RemarkWithoutTeam
            $Var = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $Var)) {
                $Data->createCertificateField($tblCertificate, $Var, 900);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setBfsHj(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Berufsfachschule Halbjahreszeugnis', '',
            'BfsHj');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeBerufsfachschule()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeBerufsfachschule(), null, true);
                // Automaitk soll hier nicht entscheiden
//                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
//                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeBerufsfachschule(), '1'))) {
//                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
//                    }
//                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeBerufsfachschule(), '2'))) {
//                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
//                    }
//                }
            }
            // Begrenzung Eingabefelder
            // Begrenzung RemarkWithoutTeam
            $Var = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $Var)) {
                $Data->createCertificateField($tblCertificate, $Var, 900);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setBfsJ(Data $Data)
    {
        $tblCertificate = $Data->createCertificate('Berufsfachschule Jahreszeugnis', '',
            'BfsJ');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeBerufsfachschule()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeBerufsfachschule(), null, false);
                // Automaitk soll hier nicht entscheiden
//                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
//                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeBerufsfachschule(), '1'))) {
//                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
//                }
//                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeBerufsfachschule(), '2'))) {
//                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
//                }
//                }
            }
            // Begrenzung Eingabefelder
            // Begrenzung RemarkWithoutTeam
            $Var = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $Var)) {
                $Data->createCertificateField($tblCertificate, $Var, 300);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setBfsAbg(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Berufsfachschule Abgangszeugnis', '', 'BfsAbg',
            null, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeBerufsfachschule());
//        if ($tblCertificate) {
            // ToDO hinterlegung irgendwelcher Fächer?
//            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
//                $row = 1;
//                $column = 1;
//                $Data->setCertificateSubject($tblCertificate, 'DE', $row, $column++);
//                $Data->setCertificateSubject($tblCertificate, 'EN', $row, $column++);
//                $column++;
//                $Data->setCertificateSubject($tblCertificate, 'KU', $row, $column++);
//                $Data->setCertificateSubject($tblCertificate, 'MU', $row, $column++);
//                $Data->setCertificateSubject($tblCertificate, 'GE', $row, $column++);
//                $Data->setCertificateSubject($tblCertificate, 'GRW', $row, $column++);
//                $Data->setCertificateSubject($tblCertificate, 'GEO', $row, $column);
//
//                $row = 2;
//                $column = 1;
//                $Data->setCertificateSubject($tblCertificate, 'MA', $row, $column++);
//                $Data->setCertificateSubject($tblCertificate, 'BIO', $row, $column++);
//                $Data->setCertificateSubject($tblCertificate, 'CH', $row, $column++);
//                $Data->setCertificateSubject($tblCertificate, 'PH', $row, $column++);
//                $Data->setCertificateSubject($tblCertificate, 'SPO', $row, $column++);
//                $Data->setCertificateSubject($tblCertificate, 'RE/e', $row, $column++, false);
//                $Data->setCertificateSubject($tblCertificate, 'RE/k', $row, $column++, false);
//                $Data->setCertificateSubject($tblCertificate, 'ETH', $row, $column++, false);
//                $Data->setCertificateSubject($tblCertificate, 'TC', $row, $column++);
//                $Data->setCertificateSubject($tblCertificate, 'INF', $row, $column);
//            }
//        }
    }

}