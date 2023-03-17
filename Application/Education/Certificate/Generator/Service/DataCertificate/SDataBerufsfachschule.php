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

        self::setBfsHjInfo($Data, 'B.01.01');
        self::setBfsHj($Data, 'B.01.03');
        self::setBfsJ($Data, 'B.01.02');
        self::setBfsPflegeJ($Data, 'B.02.02a');
        self::setBfsAbs($Data, 'B.01.05');
        self::setBfsAbg($Data, 'B.01.04');
    }

    /**
     * @param Data $Data
     */
    private static function setBfsHjInfo(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Berufsfachschule Halbjahresinformation', '',
            'BfsHjInfo');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
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

        // Informationen auf mehrere "Sonstige Informationen" aufgliedern
        // Seite 2
        $Data->createCertificateInformation($tblCertificate, 'BfsDestination', 2);
        $Data->createCertificateInformation($tblCertificate, 'CertificateName', 2);
        // Seite 3
        $Data->createCertificateInformation($tblCertificate, 'OperationTimeTotal', 3);
        $Data->createCertificateInformation($tblCertificate, 'Operation1', 3);
        $Data->createCertificateInformation($tblCertificate, 'OperationTime1', 3);
        $Data->createCertificateInformation($tblCertificate, 'Operation2', 3);
        $Data->createCertificateInformation($tblCertificate, 'OperationTime2', 3);
        $Data->createCertificateInformation($tblCertificate, 'Operation3', 3);
        $Data->createCertificateInformation($tblCertificate, 'OperationTime3', 3);
        $Data->createCertificateInformation($tblCertificate, 'Operation4', 3);
        $Data->createCertificateInformation($tblCertificate, 'OperationTime4', 3);
    }

    /**
     * @param Data $Data
     */
    private static function setBfsHj(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Berufsfachschule Halbjahreszeugnis', '',
            'BfsHj');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
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

            // Inforamtionen auf mehrere "Sonnstige Informationen" aufgliedern
            // Seite 2
            $Data->createCertificateInformation($tblCertificate, 'BfsDestination', 2);
            $Data->createCertificateInformation($tblCertificate, 'CertificateName', 2);
            // Seite 3
            $Data->createCertificateInformation($tblCertificate, 'OperationTimeTotal', 3);
            $Data->createCertificateInformation($tblCertificate, 'Operation1', 3);
            $Data->createCertificateInformation($tblCertificate, 'OperationTime1', 3);
            $Data->createCertificateInformation($tblCertificate, 'Operation2', 3);
            $Data->createCertificateInformation($tblCertificate, 'OperationTime2', 3);
            $Data->createCertificateInformation($tblCertificate, 'Operation3', 3);
            $Data->createCertificateInformation($tblCertificate, 'OperationTime3', 3);
            $Data->createCertificateInformation($tblCertificate, 'Operation4', 3);
            $Data->createCertificateInformation($tblCertificate, 'OperationTime4', 3);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setBfsJ(Data $Data, $CertificateNumber)
    {
        $tblCertificate = $Data->createCertificate('Berufsfachschule Jahreszeugnis', '',
            'BfsJ');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
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

            // Inforamtionen auf mehrere "Sonnstige Informationen" aufgliedern
            // Seite 2
            $Data->createCertificateInformation($tblCertificate, 'BfsDestination', 2);
            $Data->createCertificateInformation($tblCertificate, 'CertificateName', 2);
            // Seite 3
            $Data->createCertificateInformation($tblCertificate, 'OperationTimeTotal', 3);
            $Data->createCertificateInformation($tblCertificate, 'Operation1', 3);
            $Data->createCertificateInformation($tblCertificate, 'OperationTime1', 3);
            $Data->createCertificateInformation($tblCertificate, 'Operation2', 3);
            $Data->createCertificateInformation($tblCertificate, 'OperationTime2', 3);
            $Data->createCertificateInformation($tblCertificate, 'Operation3', 3);
            $Data->createCertificateInformation($tblCertificate, 'OperationTime3', 3);
            $Data->createCertificateInformation($tblCertificate, 'Operation4', 3);
            $Data->createCertificateInformation($tblCertificate, 'OperationTime4', 3);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setBfsAbs(Data $Data, $CertificateNumber)
    {

        if (($tblCertificate = $Data->createCertificate('Berufsfachschule Abschlusszeugnis', '', 'BfsAbs',
            null, false, false, false, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeBerufsfachschule()))
        ) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            // ToDO hinterlegung irgendwelcher Fächer?

//            'DateFrom' ist auf Seite 1
//            'DateTo' ist auf Seite 1
//            'BfsDestination' ist auf Seite 1

            $Data->createCertificateInformation($tblCertificate, 'OperationTimeTotal', 2);
            $Data->createCertificateInformation($tblCertificate, 'Operation1', 2);
            $Data->createCertificateInformation($tblCertificate, 'OperationTime1', 2);
            $Data->createCertificateInformation($tblCertificate, 'Operation2', 2);
            $Data->createCertificateInformation($tblCertificate, 'OperationTime2', 2);
            $Data->createCertificateInformation($tblCertificate, 'Operation3', 2);
            $Data->createCertificateInformation($tblCertificate, 'OperationTime3', 2);
            $Data->createCertificateInformation($tblCertificate, 'Operation4', 2);
            $Data->createCertificateInformation($tblCertificate, 'OperationTime4', 2);

            $Data->createCertificateInformation($tblCertificate, 'RemarkWithoutTeam', 3);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setBfsAbg(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Berufsfachschule Abgangszeugnis', '', 'BfsAbg',
            null, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeBerufsfachschule());
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
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
        }
    }

    /**
     * @param Data $Data
     */
    private static function setBfsPflegeJ(Data $Data, $CertificateNumber)
    {
        $tblCertificate = $Data->createCertificate('Berufsfachschule Jahreszeugnis', 'Generalistik', 'BfsPflegeJ');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if($tblCertificate->getDescription() != 'Generalistik'){
                $Data->updateCertificateName($tblCertificate, $tblCertificate->getName(), 'Generalistik');
            }
            if ($Data->getTblSchoolTypeBerufsfachschule()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeBerufsfachschule(), null, false, true);
            }

            // Informationen auf mehrere "Sonstige Informationen" aufgliedern
            // Seite 2
            $Data->createCertificateInformation($tblCertificate, 'YearGradeAverageLesson_Average', 2);
            $Data->createCertificateInformation($tblCertificate, 'YearGradeAveragePractical_Average', 2);
            $Data->createCertificateInformation($tblCertificate, 'WrittenExam_Grade', 2);
            $Data->createCertificateInformation($tblCertificate, 'PracticalExam_Grade', 2);
            // Seite 3
            $Data->createCertificateInformation($tblCertificate, 'Subarea1', 3);
            $Data->createCertificateInformation($tblCertificate, 'SubareaTimeH1', 3);
            $Data->createCertificateInformation($tblCertificate, 'SubareaTimeHDone1', 3);
            // Seite 4
            $Data->createCertificateInformation($tblCertificate, 'Subarea2', 4);
            $Data->createCertificateInformation($tblCertificate, 'SubareaTimeH2', 4);
            $Data->createCertificateInformation($tblCertificate, 'SubareaTimeHDone2', 4);
            // Seite 5
            $Data->createCertificateInformation($tblCertificate, 'Subarea3', 5);
            $Data->createCertificateInformation($tblCertificate, 'SubareaTimeH3', 5);
            $Data->createCertificateInformation($tblCertificate, 'SubareaTimeHDone3', 5);
            // Seite 6
            $Data->createCertificateInformation($tblCertificate, 'Subarea4', 6);
            $Data->createCertificateInformation($tblCertificate, 'SubareaTimeH4', 6);
            $Data->createCertificateInformation($tblCertificate, 'SubareaTimeHDone4', 6);

            //            // Begrenzung Eingabefelder
            //            // Begrenzung RemarkWithoutTeam
            $Var = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $Var)) {
                $Data->createCertificateField($tblCertificate, $Var, 300);
            }
            $Data->createCertificateInformation($tblCertificate, $Var, 7);
        }
    }
}