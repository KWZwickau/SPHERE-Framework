<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;

class SDataPrimary
{
    public static function setCertificateStandard(Data $Data)
    {

        self::setGsHjOneInfo($Data, '1.1');
        self::setGsHjInformation($Data, '1.2');
        self::setGsJOne($Data, '1.1');
        self::setGsJa($Data, '1.3');
        self::setBeGs($Data, 'Anlage 1');
    }

    /**
     * @param Data $Data
     */
    private static function setGsHjOneInfo(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Halbjahresinformation', 'der ersten Klasse',
            'GsHjOneInfo');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypePrimary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypePrimary(), null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 1);
                }
            }
            // Begrenzung des Bemerkungsfeld
            // erste Klasse nicht, wegen Enter
//            $FieldName = 'Remark';
//            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
//                $Data->createCertificateField($tblCertificate, $FieldName, 4000);
//            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setGsJOne(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Jahreszeugnis', 'der ersten Klasse', 'GsJOne');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypePrimary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypePrimary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 1);
                }
            }
            // Begrenzung des Bemerkungsfeld
            // erste Klasse nicht, wegen Enter
//            $FieldName = 'Remark';
//            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
//                $Data->createCertificateField($tblCertificate, $FieldName, 4000);
//            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setGsHjInformation(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Halbjahresinformation', '', 'GsHjInformation');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypePrimary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypePrimary(), null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 2);
                    $Data->createCertificateLevel($tblCertificate, 3);
                    $Data->createCertificateLevel($tblCertificate, 4);
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 1200);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {

            $row = 1;
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
            $Data->setCertificateSubject($tblCertificate, 'SU', 1, $row++);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row);
            $row = 1;
            $Data->setCertificateSubject($tblCertificate, 'MA', 2, $row++);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, $row++);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, $row++, false);
            $Data->setCertificateSubject($tblCertificate, 'RE/k', 2, $row++, false);
            $Data->setCertificateSubject($tblCertificate, 'RE/j', 2, $row++, false);
            $Data->setCertificateSubject($tblCertificate, 'ETH', 2, $row++, false);
            $Data->setCertificateSubject($tblCertificate, 'WE', 2, $row);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setGsJa(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Jahreszeugnis', '', 'GsJa');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypePrimary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypePrimary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 2);
                    $Data->createCertificateLevel($tblCertificate, 3);
                    $Data->createCertificateLevel($tblCertificate, 4);
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
            // Begrenzung des Einschätzungfelds
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 600);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {

            $row = 1;
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
            $Data->setCertificateSubject($tblCertificate, 'SU', 1, $row++);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row);
            $row = 1;
            $Data->setCertificateSubject($tblCertificate, 'MA', 2, $row++);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, $row++);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, $row++, false);
            $Data->setCertificateSubject($tblCertificate, 'RE/k', 2, $row++, false);
            $Data->setCertificateSubject($tblCertificate, 'RE/j', 2, $row++, false);
            $Data->setCertificateSubject($tblCertificate, 'ETH', 2, $row++, false);
            $Data->setCertificateSubject($tblCertificate, 'WE', 2, $row);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setBeGs(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Bildungsempfehlung', 'Grundschule Klasse 4', 'BeGs');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypePrimary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeRecommendation(), $Data->getTblSchoolTypePrimary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    $Data->createCertificateLevel($tblCertificate, 4);
                }
            }
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'SU', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
        }

        // SSW-1981 Deaktivierung Bildungsempfehlung Klasse 5/6
        if (($tblCertificate = $Data->getCertificateByCertificateClassName('BeMi'))) {
            $Data->destroyCertificate($tblCertificate);
        }
    }


}