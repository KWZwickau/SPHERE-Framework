<?php

namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;

class IDataKG
{
    /**
     * @param Data $Data
     *
     * @return void
     */
    public static function setCertificateIndividually(Data $Data): void
    {
        $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('KG');
        if ($tblConsumerCertificate) {
            self::setGymHjInfo($Data, $tblConsumerCertificate);
            self::setGymHjZ($Data, $tblConsumerCertificate);
            self::setGymJ($Data, $tblConsumerCertificate);
        }
    }

    /**
     * @param Data $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setGymHjInfo(Data $Data, TblConsumer $tblConsumerCertificate): void
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Halbjahresinformation', '', 'KG\GymHjInfo', $tblConsumerCertificate);
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
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            self::setCertificateSubjectsStandardGym($tblCertificate, $Data);
        }
    }

    /**
     * @param Data $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setGymHjZ(Data $Data, TblConsumer $tblConsumerCertificate): void
    {
        $tblCertificate = $Data->createCertificate('Gymnasium Halbjahreszeugnis', '', 'KG\GymHjZ', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeGym()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeGym());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 10);
                }
            }
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            self::setCertificateSubjectsStandardGym($tblCertificate, $Data);
        }
    }

    /**
     * @param Data $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setGymJ(Data $Data, TblConsumer $tblConsumerCertificate): void
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Jahreszeugnis', '', 'KG\GymJ', $tblConsumerCertificate);
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
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            self::setCertificateSubjectsStandardGym($tblCertificate, $Data);
        }
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $Data
     */
    private static function setCertificateSubjectsStandardGym(TblCertificate $tblCertificate, $Data): void
    {
        $i = 1;
        $Data->setCertificateSubject($tblCertificate, 'DE', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'EN', 1, $i++);
        // 1,3 freilassen für Fremdsprache
        $i++;
        // 1,4 freilassen für Profil
        $i++;
        $Data->setCertificateSubject($tblCertificate, 'KU', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'MU', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'GE', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'GRW', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $i++);

        $i = 1;
        $Data->setCertificateSubject($tblCertificate, 'MA', 2, $i++);
        $Data->setCertificateSubject($tblCertificate, 'BIO', 2, $i++);
        $Data->setCertificateSubject($tblCertificate, 'CH', 2, $i++);
        $Data->setCertificateSubject($tblCertificate, 'PH', 2, $i++);
        $Data->setCertificateSubject($tblCertificate, 'INF', 2, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'TC', 2, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'RE/k', 2, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'SPO', 2, $i++);
    }
}