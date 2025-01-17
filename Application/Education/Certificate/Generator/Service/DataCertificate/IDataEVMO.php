<?php


namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;


use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;

/**
 * Class IDataEVMO
 * @package SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate
 */
class IDataEVMO
{
    /**
     * @param Data $Data
     */
    public static function setCertificateIndividually(Data $Data)
    {
        $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('EVMO');
        if ($tblConsumerCertificate){
            self::setGsHjOneTwo($Data, $tblConsumerCertificate);
            self::setGsJOneTwo($Data, $tblConsumerCertificate);
            self::setGsHjThree($Data, $tblConsumerCertificate);
            self::setGsJThree($Data, $tblConsumerCertificate);
            self::setGsHjFour($Data, $tblConsumerCertificate);
            self::setGsJFour($Data, $tblConsumerCertificate);
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setGsHjOneTwo(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Grundschule Halbjahresinformation', 'Klasse 1-2',
            'EVMO\GsHjOneTwo', $tblConsumerCertificate, false, true);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypePrimary(), null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    $Data->createCertificateLevel($tblCertificate, 1);
                    $Data->createCertificateLevel($tblCertificate, 2);
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 1800);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setGsJOneTwo(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Grundschule Jahreszeugnis', 'Klasse 1-2',
            'EVMO\GsJOneTwo', $tblConsumerCertificate);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypePrimary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    $Data->createCertificateLevel($tblCertificate, 1);
                    $Data->createCertificateLevel($tblCertificate, 2);
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 2100);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setGsHjThree(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Grundschule Halbjahresinformation', 'Klasse 3',
            'EVMO\GsHjThree', $tblConsumerCertificate, false, true);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypePrimary(), null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    $Data->createCertificateLevel($tblCertificate, 3);
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 1800);
            }
            // Kopfnoten
            if (!$Data->getCertificateGradeAll($tblCertificate)) {
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            // Fachnoten
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'SU', 1, 2);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, 3);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, 4);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, 5);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 2);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 3);
                $Data->setCertificateSubject($tblCertificate, 'WE', 2, 4);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setGsJThree(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Grundschule Jahreszeugnis', 'Klasse 3',
            'EVMO\GsJThree', $tblConsumerCertificate);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypePrimary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    $Data->createCertificateLevel($tblCertificate, 3);
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 2100);
            }
            // Kopfnoten
            if (!$Data->getCertificateGradeAll($tblCertificate)) {
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            // Fachnoten
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'SU', 1, 2);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, 3);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, 4);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, 5);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 2);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 3);
                $Data->setCertificateSubject($tblCertificate, 'WE', 2, 4);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setGsHjFour(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Grundschule Halbjahresinformation', 'Klasse 4',
            'EVMO\GsHjFour', $tblConsumerCertificate, false, true);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypePrimary(), null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    $Data->createCertificateLevel($tblCertificate, 4);
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 1400);
            }
            // Kopfnoten
            if (!$Data->getCertificateGradeAll($tblCertificate)) {
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            // Fachnoten
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'SU', 1, 2);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, 3);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, 4);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, 5);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 2);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 3);
                $Data->setCertificateSubject($tblCertificate, 'WE', 2, 4);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setGsJFour(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Grundschule Jahreszeugnis', 'Klasse 4',
            'EVMO\GsJFour', $tblConsumerCertificate);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypePrimary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    $Data->createCertificateLevel($tblCertificate, 4);
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 1200);
            }
            // Kopfnoten
            if (!$Data->getCertificateGradeAll($tblCertificate)) {
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            // Fachnoten
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'SU', 1, 2);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, 3);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, 4);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, 5);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 2);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 3);
                $Data->setCertificateSubject($tblCertificate, 'WE', 2, 4);
            }
        }
    }
}