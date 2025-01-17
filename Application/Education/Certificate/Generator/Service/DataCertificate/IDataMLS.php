<?php

namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;

class IDataMLS
{
    /**
     * @param Data $Data
     */
    public static function setCertificateIndividually(Data $Data)
    {
        if (($tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('MLS'))){
            self::setGsHjInfo($Data, $tblConsumerCertificate);
            self::setGsHjInfoOne($Data, $tblConsumerCertificate);
            self::setGsJ($Data, $tblConsumerCertificate);
            self::setGsJOne($Data, $tblConsumerCertificate);
            self::setBeGs($Data, $tblConsumerCertificate);
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setGsHjInfo(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Halbjahresinformation Grundschule ', 'Klasse 2 - 4', 'MLS\GsHjInfo', $tblConsumerCertificate, false, true, false,
            $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypePrimary());
        if ($tblCertificate){
            if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                $Data->createCertificateLevel($tblCertificate, 2);
                $Data->createCertificateLevel($tblCertificate, 3);
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 1400);
            }
            // Kopfnoten
            if (!$Data->getCertificateGradeAll($tblCertificate)){
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)){
                $i = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'SU', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, $i);

                $i = 1;
                $Data->setCertificateSubject($tblCertificate, 'MA', 2, $i++);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, $i++);
                $Data->setCertificateSubject($tblCertificate, 'RE/E', 2, $i++);
                $Data->setCertificateSubject($tblCertificate, 'WE', 2, $i);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setGsHjInfoOne(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Halbjahresinformation Grundschule', 'Klasse 1', 'MLS\GsHjInfoOne', $tblConsumerCertificate, false, true, false,
            $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypePrimary());
        if ($tblCertificate){
            if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                $Data->createCertificateLevel($tblCertificate, 1);
            }
            // Begrenzung des Bemerkungsfelds
//            $FieldName = 'RemarkWithoutTeam';
//            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
//                $Data->createCertificateField($tblCertificate, $FieldName, 1400);
//            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setGsJ(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Jahreszeugnis Grundschule ', 'Klasse 2 - 4', 'MLS\GsJ', $tblConsumerCertificate, false, false, false,
            $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypePrimary());
        if ($tblCertificate){
            if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                $Data->createCertificateLevel($tblCertificate, 2);
                $Data->createCertificateLevel($tblCertificate, 3);
                $Data->createCertificateLevel($tblCertificate, 4);
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 900);
            }
            // Begrenzung des Einschätzungfelds
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 600);
            }
            // Kopfnoten
            if (!$Data->getCertificateGradeAll($tblCertificate)){
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)){
                $i = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'SU', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, $i);

                $i = 1;
                $Data->setCertificateSubject($tblCertificate, 'MA', 2, $i++);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, $i++);
                $Data->setCertificateSubject($tblCertificate, 'RE/E', 2, $i++);
                $Data->setCertificateSubject($tblCertificate, 'WE', 2, $i);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setGsJOne(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Jahreszeugnis Grundschule', 'Klasse 1', 'MLS\GsJOne', $tblConsumerCertificate, false, false, false,
            $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypePrimary());
        if ($tblCertificate){
            if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                $Data->createCertificateLevel($tblCertificate, 1);
            }
            // Begrenzung des Bemerkungsfelds
//            $FieldName = 'RemarkWithoutTeam';
//            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
//                $Data->createCertificateField($tblCertificate, $FieldName, 900);
//            }
        }
    }

    /**
     * @param Data $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setBeGs(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Bildungsempfehlung', 'Grundschule Klasse 4', 'MLS\BeGs', $tblConsumerCertificate,
            false, false, false, $Data->getTblCertificateTypeRecommendation(), $Data->getTblSchoolTypePrimary());
        if ($tblCertificate) {
            if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                $Data->createCertificateLevel($tblCertificate, 4);
            }
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'SU', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
        }
    }
}