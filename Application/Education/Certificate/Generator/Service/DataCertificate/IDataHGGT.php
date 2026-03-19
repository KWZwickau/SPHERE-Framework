<?php

namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;

class IDataHGGT
{
    public static function setCertificateIndividually(Data $Data)
    {

        $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('HGGT');
        if ($tblConsumerCertificate){
            self::setGymHjInfo($Data, $tblConsumerCertificate);
            self::setGymHj($Data, $tblConsumerCertificate);
            self::setGymJ($Data, $tblConsumerCertificate);
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setGymHjInfo(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Gymnasium Halbjahresinformation', '', 'HGGT\GymHjInfo', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeGym()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeGym(), null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 5);
                    $Data->createCertificateLevel($tblCertificate, 6);
                    $Data->createCertificateLevel($tblCertificate, 7);
                    $Data->createCertificateLevel($tblCertificate, 8);
                    $Data->createCertificateLevel($tblCertificate, 9);
                }
            }
            // Begrenzung des Einschätzungsfeld
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 400);
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 200);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'FR', 1, 3, false);
            $Data->setCertificateSubject($tblCertificate, 'LAT', 1, 4, false);
            $Data->setCertificateSubject($tblCertificate, 'SPA', 1, 5, false);

            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 6);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 7);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 8);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 9);
            $Data->setCertificateSubject($tblCertificate, 'GRW', 1, 10);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'PH', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'CH', 2, 4);
            $Data->setCertificateSubject($tblCertificate, 'INF', 2, 5, false);
            $Data->setCertificateSubject($tblCertificate, 'TC', 2, 6, false);
            $Data->setCertificateSubject($tblCertificate, 'REL', 2, 7, false);
            $Data->setCertificateSubject($tblCertificate, 'ETH', 2, 8, false);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 9);
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setGymHj(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Gymnasium Halbjahreszeugnis', '', 'HGGT\GymHj', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeGym()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeGym());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 10);
                }
            }
            // Begrenzung des Einschätzungsfeld
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 400);
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 200);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'FR', 1, 3, false);
            $Data->setCertificateSubject($tblCertificate, 'LAT', 1, 4, false);
            $Data->setCertificateSubject($tblCertificate, 'SPA', 1, 5, false);

            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 6);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 7);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 8);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 9);
            $Data->setCertificateSubject($tblCertificate, 'GRW', 1, 10);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'PH', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'CH', 2, 4);
            $Data->setCertificateSubject($tblCertificate, 'INF', 2, 5, false);
            $Data->setCertificateSubject($tblCertificate, 'TC', 2, 6, false);
            $Data->setCertificateSubject($tblCertificate, 'REL', 2, 7, false);
            $Data->setCertificateSubject($tblCertificate, 'ETH', 2, 8, false);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 9);
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setGymJ(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Jahreszeugnis', '', 'HGGT\GymJ', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeGym()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeGym());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 5);
                    $Data->createCertificateLevel($tblCertificate, 6);
                    $Data->createCertificateLevel($tblCertificate, 7);
                    $Data->createCertificateLevel($tblCertificate, 8);
                    $Data->createCertificateLevel($tblCertificate, 9);
                    $Data->createCertificateLevel($tblCertificate, 10);
                }
            }
            // Begrenzung des Einschätzungsfeld
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 400);
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 200);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'FR', 1, 3, false);
            $Data->setCertificateSubject($tblCertificate, 'LAT', 1, 4, false);
            $Data->setCertificateSubject($tblCertificate, 'SPA', 1, 5, false);

            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 6);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 7);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 8);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 9);
            $Data->setCertificateSubject($tblCertificate, 'GRW', 1, 10);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'PH', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'CH', 2, 4);
            $Data->setCertificateSubject($tblCertificate, 'INF', 2, 5, false);
            $Data->setCertificateSubject($tblCertificate, 'TC', 2, 6, false);
            $Data->setCertificateSubject($tblCertificate, 'REL', 2, 7, false);
            $Data->setCertificateSubject($tblCertificate, 'ETH', 2, 8, false);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 9);
        }
    }
}