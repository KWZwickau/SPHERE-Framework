<?php

namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;

class SDataBGym
{
    public static function setCertificateStandard(Data $Data)
    {
        self::setBGymHjZ($Data, 'E.01.01');
        self::setBGymJ($Data, 'E.01.02');
        self::setBGymKurshalbjahreszeugnis($Data, 'E.01.03');
        self::setBGymAbgSekII($Data, 'E.01.05');
        self::setBGymAbitur($Data, 'E.01.06');
    }

    /**
     * @param Data $Data
     * @param string $CertificateNumber
     */
    private static function setBGymHjZ(Data $Data, string $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Berufliches Gymnasium Halbjahreszeugnis', 'Klasse 11', 'BGymHjZ');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
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
     * @param string $CertificateNumber
     */
    private static function setBGymJ(Data $Data, string $CertificateNumber)
    {
        $tblCertificate = $Data->createCertificate('Berufliches Gymnasium Jahreszeugnis', 'Klasse 11', 'BGymJ');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
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
     * @param Data $Data
     * @param string $CertificateNumber
     */
    private static function setBGymKurshalbjahreszeugnis(Data $Data, string $CertificateNumber)
    {
        // Kurshalbjahreszeugnis
        $tblCertificate = $Data->createCertificate('Berufliches Gymnasium Kurshalbjahreszeugnis', 'Klasse 12/13', 'BGymKurshalbjahreszeugnis');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeBeruflichesGymnasium() && $Data->getTblCertificateTypeMidTermCourse()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeMidTermCourse(), $Data->getTblSchoolTypeBeruflichesGymnasium());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 12);
                    $Data->createCertificateLevel($tblCertificate, 13);
                }
            }

            // Fächer werden fest programmiert aufgrund der Aufgabenfelder
        }
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $Data
     */
    private static function setCertificateSubjectsStandardBGym(TblCertificate $tblCertificate, $Data)
    {
        $row = 1;
        $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
        $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
        $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
        $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++);
        $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
        $Data->setCertificateSubject($tblCertificate, 'GE/GK', 1, $row++);
        $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $row++);
        $row = 1;
        $Data->setCertificateSubject($tblCertificate, 'MA', 2, $row++);
        $Data->setCertificateSubject($tblCertificate, 'BIO', 2, $row++);
        $Data->setCertificateSubject($tblCertificate, 'CH', 2, $row++);
        $Data->setCertificateSubject($tblCertificate, 'PH', 2, $row++);
        $Data->setCertificateSubject($tblCertificate, 'SPO', 2, $row++);
        $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, $row++, false);
        $Data->setCertificateSubject($tblCertificate, 'RE/k', 2, $row++, false);
        $Data->setCertificateSubject($tblCertificate, 'RE/j', 2, $row++, false);
        $Data->setCertificateSubject($tblCertificate, 'ETH', 2, $row++, false);
        $Data->setCertificateSubject($tblCertificate, 'WR', 2, $row++);
        $Data->setCertificateSubject($tblCertificate, 'GeSo', 2, $row++);
        $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row++);
    }

    /**
     * @param Data $Data
     * @param string $CertificateNumber
     */
    private static function setBGymAbgSekII(Data $Data, string $CertificateNumber)
    {
        $tblCertificate = $Data->createCertificate('Berufliches Gymnasium Abgangszeugnis', 'Sekundarstufe II', 'BGymAbgSekII',
            null, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeBeruflichesGymnasium());
        if($tblCertificate){
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
        }
    }

    /**
     * @param Data $Data
     * @param string $CertificateNumber
     */
    private static function setBGymAbitur(Data $Data, string $CertificateNumber)
    {
        if (($tblCertificate = $Data->createCertificate('Berufliches Gymnasium Abschlusszeugnis', 'Abitur', 'BGymAbitur',
            null, false, false, false, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeBeruflichesGymnasium()))
        ) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if (!$Data->getCertificateReferenceForLanguagesAllByCertificate($tblCertificate)) {
                $Data->createCertificateReferenceForLanguages($tblCertificate, 1, 'B2', 'B2+', 'C1');
                $Data->createCertificateReferenceForLanguages($tblCertificate, 2, 'B1+', 'B2', 'B2+ - C1');
                $Data->createCertificateReferenceForLanguages($tblCertificate, 3, 'B1 - B1+', 'B2', 'B2+ - C1');
            }
        }
    }
}