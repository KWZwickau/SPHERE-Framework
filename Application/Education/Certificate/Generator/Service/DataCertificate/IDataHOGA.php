<?php

namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;

class IDataHOGA
{
    /**
     * @param Data $Data
     */
    public static function setCertificateIndividually(Data $Data)
    {
        $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('HOGA');
        if ($tblConsumerCertificate){
            self::setMsHjInfo($Data, $tblConsumerCertificate);
            self::setMsHjZ($Data, $tblConsumerCertificate);
            self::setMsJ($Data, $tblConsumerCertificate);
            self::setMsAbg($Data, $tblConsumerCertificate);
            self::setMsAbsHs($Data, $tblConsumerCertificate);
            self::setMsAbsHsQ($Data, $tblConsumerCertificate);
            self::setMsAbsRs($Data, $tblConsumerCertificate);

            self::setGymHjInfo($Data, $tblConsumerCertificate);
            self::setGymHjZ($Data, $tblConsumerCertificate);
            self::setGymJ($Data, $tblConsumerCertificate);
            self::setGymAbgSekI($Data, $tblConsumerCertificate);

            self::setBgjHjue($Data, $tblConsumerCertificate);
            self::setBgjHjInfo($Data, $tblConsumerCertificate);
            self::setBgjJue($Data, $tblConsumerCertificate);
            self::setBgjAbs($Data, $tblConsumerCertificate);

            self::setBGymHjZ($Data, $tblConsumerCertificate);
            self::setBGymJ($Data, $tblConsumerCertificate);

            self::setFosHjZ($Data, $tblConsumerCertificate);
            self::setFosJ($Data, $tblConsumerCertificate);
            self::setFosAbg($Data, $tblConsumerCertificate);
            self::setFosAbs($Data, $tblConsumerCertificate);
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setMsHjInfo(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation', 'Klasse 5-9',
            'HOGA\MsHjInfo', $tblConsumerCertificate, false, true);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypeSecondary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypeSecondary(),
                    null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    $Data->createCertificateLevel($tblCertificate, 5);
                    $Data->createCertificateLevel($tblCertificate, 6);
                    $Data->createCertificateLevel($tblCertificate, 7);
                    $Data->createCertificateLevel($tblCertificate, 8);
                    $Data->createCertificateLevel($tblCertificate, 9);
                }
            }
            // Kopfnoten
            if (!$Data->getCertificateGradeAll($tblCertificate)){
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            // Fächer
            if (!$Data->getCertificateSubjectAll($tblCertificate)){
                self::setCertificateSubjectsStandardMs($tblCertificate, $Data);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setMsHjZ(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Oberschule Halbjahreszeugnis', 'Klasse 9-10',
            'HOGA\MsHjZ', $tblConsumerCertificate);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypeSecondary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypeSecondary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    $Data->createCertificateLevel($tblCertificate, 9);
                    $Data->createCertificateLevel($tblCertificate, 10);
                }
            }
            // Kopfnoten
            if (!$Data->getCertificateGradeAll($tblCertificate)){
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)){
                self::setCertificateSubjectsStandardMs($tblCertificate, $Data);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setMsJ(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis', 'Klasse 5-9',
            'HOGA\MsJ', $tblConsumerCertificate);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypeSecondary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeSecondary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    $Data->createCertificateLevel($tblCertificate, 5);
                    $Data->createCertificateLevel($tblCertificate, 6);
                    $Data->createCertificateLevel($tblCertificate, 7);
                    $Data->createCertificateLevel($tblCertificate, 8);
                    $Data->createCertificateLevel($tblCertificate, 9);
                }
            }
            // Kopfnoten
            if (!$Data->getCertificateGradeAll($tblCertificate)){
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)){
                self::setCertificateSubjectsStandardMs($tblCertificate, $Data);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setMsAbg(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Oberschule Abgangszeugnis', '', 'HOGA\MsAbg',
            $tblConsumerCertificate, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeSecondary());
        if ($tblCertificate) {
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                self::setCertificateSubjectsStandardMs($tblCertificate, $Data);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setMsAbsHs(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Oberschule Abschlusszeugnis', 'Hauptschulabschluss', 'HOGA\MsAbsHs',
            $tblConsumerCertificate, false, false, false, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeSecondary(),
            $Data->getTblCourseMain());
        if ($tblCertificate) {
            if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                $Data->createCertificateLevel($tblCertificate, 9);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                self::setCertificateSubjectsStandardMs($tblCertificate, $Data);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setMsAbsHsQ(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Oberschule Abschlusszeugnis', 'qualifizierter Hauptschulabschluss', 'HOGA\MsAbsHsQ',
            $tblConsumerCertificate, false, false, true, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeSecondary(),
            $Data->getTblCourseMain());
        if ($tblCertificate) {
            if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                $Data->createCertificateLevel($tblCertificate, 9);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                self::setCertificateSubjectsStandardMs($tblCertificate, $Data);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setMsAbsRs(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Oberschule Abschlusszeugnis', 'Realschulabschluss', 'HOGA\MsAbsRs',
            $tblConsumerCertificate, false, false, false, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeSecondary(),
            $Data->getTblCourseReal());
        if ($tblCertificate) {
            if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                $Data->createCertificateLevel($tblCertificate, 10);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                self::setCertificateSubjectsStandardMs($tblCertificate, $Data);
            }
        }
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $Data
     */
    private static function setCertificateSubjectsStandardMs(TblCertificate $tblCertificate, $Data)
    {
        $i = 1;
        $Data->setCertificateSubject($tblCertificate, 'DE', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'EN', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'KU', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'MU', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'GE', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'GK', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $i++);

        $Data->setCertificateSubject($tblCertificate, 'WTH', 1, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'WTH1', 1, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'WTH2', 1, $i++, false);

        $i = 1;
        $Data->setCertificateSubject($tblCertificate, 'MA', 2, $i++);
        $Data->setCertificateSubject($tblCertificate, 'BIO', 2, $i++);
        $Data->setCertificateSubject($tblCertificate, 'CH', 2, $i++);
        $Data->setCertificateSubject($tblCertificate, 'PH', 2, $i++);

        $Data->setCertificateSubject($tblCertificate, 'SPO', 2, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'SPO Ju', 2, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'SPO Mä', 2, $i++, false);

        $Data->setCertificateSubject($tblCertificate, 'ETH', 2, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'RE/k', 2, $i++, false);

        $Data->setCertificateSubject($tblCertificate, 'TC', 2, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'TC1', 2, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'TC2', 2, $i++, false);

        $Data->setCertificateSubject($tblCertificate, 'INF', 2, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'INF1', 2, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'INF2', 2, $i++, false);
    }

    /**
     * @param Data $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setGymHjInfo(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Halbjahresinformation', '', 'HOGA\GymHjInfo', $tblConsumerCertificate);
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
    private static function setGymHjZ(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Gymnasium Halbjahreszeugnis', '', 'HOGA\GymHjZ', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeGym()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeGym());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 10);
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
    private static function setGymJ(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Jahreszeugnis', '', 'HOGA\GymJ', $tblConsumerCertificate);
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
    private static function setGymAbgSekI(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Abgangszeugnis', 'Sekundarstufe I', 'HOGA\GymAbgSekI',
            $tblConsumerCertificate, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeGym());
        if ($tblCertificate) {
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                self::setCertificateSubjectsStandardGym($tblCertificate, $Data);
            }
        }
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $Data
     */
    private static function setCertificateSubjectsStandardGym(TblCertificate $tblCertificate, $Data)
    {
        $i = 1;
        $Data->setCertificateSubject($tblCertificate, 'DE', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'EN', 1, $i++);
        // 1,3 freilassen für Fremdsprache
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

        $Data->setCertificateSubject($tblCertificate, 'SPO', 2, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'SPO Ju', 2, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'SPO Mä', 2, $i++, false);

        $Data->setCertificateSubject($tblCertificate, 'ETH', 2, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'RE/k', 2, $i++, false);

        $Data->setCertificateSubject($tblCertificate, 'TC', 2, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'TC1', 2, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'TC2', 2, $i++, false);

        $Data->setCertificateSubject($tblCertificate, 'INF', 2, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'INF1', 2, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'INF2', 2, $i++, false);
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setBgjHjue(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Berufsgrundbildungsjahr Halbjahresübersicht', '',
            'HOGA\BgjHalbjahresuebersicht', $tblConsumerCertificate, false, true);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypeBerufsgrundbildungsjahr()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeBerufsgrundbildungsjahr());
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)){
                $i = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'MA', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'VBWL', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'GeSA', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'RE', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'INF', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'ETH', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'GE/GK', 1, $i++);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setBgjHjInfo(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Berufsgrundbildungsjahr Halbjahresinformation', '', 'HOGA\BgjHjInfo',
            $tblConsumerCertificate, false, true, false, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeBerufsgrundbildungsjahr());
        if ($tblCertificate) {
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $i = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $i++);

                $i = 1;
                $Data->setCertificateSubject($tblCertificate, 'GE/GK', 2, $i++);
                $Data->setCertificateSubject($tblCertificate, 'ETH', 2, $i++);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setBgjJue(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Berufsgrundbildungsjahr Jahresübersicht', '',
            'HOGA\BgjJahresuebersicht', $tblConsumerCertificate);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypeBerufsgrundbildungsjahr()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeBerufsgrundbildungsjahr());
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)){
                $i = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'MA', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'VBWL', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'GeSA', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'RE', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'INF', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'ETH', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'GE/GK', 1, $i++);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setBgjAbs(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Berufsgrundbildungsjahr Abschlusszeugnis', '', 'HOGA\BgjAbs',
            $tblConsumerCertificate, false, false, false, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeBerufsgrundbildungsjahr());
        if ($tblCertificate) {
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $i = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $i++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $i++);

                $i = 1;
                $Data->setCertificateSubject($tblCertificate, 'GE/GK', 2, $i++);
                $Data->setCertificateSubject($tblCertificate, 'ETH', 2, $i++);
            }

            $Data->createCertificateInformation($tblCertificate, 'IndustrialPlacement', 2);
            $Data->createCertificateInformation($tblCertificate, 'IndustrialPlacementDuration', 2);
        }
    }

    /**
     * @param Data $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setBGymHjZ(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Berufliches Gymnasium Halbjahreszeugnis', 'Klasse 11', 'HOGA\BGymHjZ', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeBeruflichesGymnasium()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeBeruflichesGymnasium());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 11);
                }
            }
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            self::setCertificateSubjectsStandardBGym($tblCertificate, $Data);
        }
    }

    /**
     * @param Data $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setBGymJ(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Berufliches Gymnasium Jahreszeugnis', 'Klasse 11', 'HOGA\BGymJ', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeBeruflichesGymnasium()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeBeruflichesGymnasium());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 11);
                }
            }
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            self::setCertificateSubjectsStandardBGym($tblCertificate, $Data);
        }
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $Data
     */
    private static function setCertificateSubjectsStandardBGym(TblCertificate $tblCertificate, $Data)
    {
        $i = 1;
        $Data->setCertificateSubject($tblCertificate, 'DE', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'ETH', 1, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'RE/e', 1, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'RE/k', 1, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'EN', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'GE/GK', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'INF', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'KU', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'MA', 1, $i++);

        $i = 1;
        $Data->setCertificateSubject($tblCertificate, 'SPO', 2, $i++);
        $Data->setCertificateSubject($tblCertificate, 'WI/RE', 2, $i++);
        $Data->setCertificateSubject($tblCertificate, 'CH', 2, $i++);
        $Data->setCertificateSubject($tblCertificate, 'BIO', 2, $i++);
        $Data->setCertificateSubject($tblCertificate, 'SPA', 2, $i++);
        $Data->setCertificateSubject($tblCertificate, 'GS', 2, $i++);
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setFosHjZ(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Fachoberschule Halbjahreszeugnis', '', 'HOGA\FosHjZ',
            $tblConsumerCertificate, false, false, false, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeFachoberschule());
        if ($tblCertificate) {
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                self::setCertificateSubjectsStandardFos($tblCertificate, $Data);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setFosJ(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Fachoberschule Jahreszeugnis', '', 'HOGA\FosJ',
            $tblConsumerCertificate, false, false, false, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeFachoberschule());
        if ($tblCertificate) {
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                self::setCertificateSubjectsStandardFos($tblCertificate, $Data);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setFosAbg(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Fachoberschule Abgangszeugnis', '', 'HOGA\FosAbg',
            $tblConsumerCertificate, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeFachoberschule());
        if ($tblCertificate) {
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                self::setCertificateSubjectsStandardFos($tblCertificate, $Data);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setFosAbs(Data $Data, TblConsumer $tblConsumerCertificate)
    {
        $tblCertificate = $Data->createCertificate('Fachoberschule Abschlusszeugnis', 'Fachhochschulreife', 'HOGA\FosAbs',
            $tblConsumerCertificate, false, false, false, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeFachoberschule());
        if ($tblCertificate) {
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                self::setCertificateSubjectsStandardFos($tblCertificate, $Data);
            }

            $Data->createCertificateInformation($tblCertificate, 'SubjectArea', 1);
//            $Data->createCertificateInformation($tblCertificate, 'AddEducation_Average', 1);
            $Data->createCertificateInformation($tblCertificate, 'RemarkWithoutTeam', 1);
            $Data->createCertificateInformation($tblCertificate, 'Job_Grade', 1);

            $Data->createCertificateInformation($tblCertificate, 'SkilledWork', 2);
            $Data->createCertificateInformation($tblCertificate, 'SkilledWork_Grade', 2);
            $Data->createCertificateInformation($tblCertificate, 'SkilledWork_GradeText', 2);
        }
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $Data
     */
    private static function setCertificateSubjectsStandardFos(TblCertificate $tblCertificate, $Data)
    {
        $i = 1;
        $Data->setCertificateSubject($tblCertificate, 'DE', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'RE', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'GE/GK', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'EN', 1, $i++);

        $Data->setCertificateSubject($tblCertificate, 'SPO', 1, $i++, false);

        $Data->setCertificateSubject($tblCertificate, 'VBWL', 1, $i++, false);
        $Data->setCertificateSubject($tblCertificate, 'GeSA', 1, $i++, false);

        $i = 1;
        $Data->setCertificateSubject($tblCertificate, 'BIO', 2, $i++);
        $Data->setCertificateSubject($tblCertificate, 'KU', 2, $i++);
        $Data->setCertificateSubject($tblCertificate, 'MA', 2, $i++);
        $Data->setCertificateSubject($tblCertificate, 'INF', 2, $i++);
        $Data->setCertificateSubject($tblCertificate, 'ETH', 2, $i++);
    }
}