<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;

class SDataSecondary
{

    public static function setCertificateStandard(Data $Data)
    {

        // wird aktuell nicht benötigt
        // $tblCertificate = $Data->createCertificate('Oberschule Halbjahreszeugnis', 'Klasse 5-6', 'MsHj');
        if (($tblCertificate = $Data->getCertificateByCertificateClassName('MsHj'))) {
            $Data->destroyCertificate($tblCertificate);
        }

        self::setCertificateBeSOFS($Data, 'Anlage 2');
        self::setMsHjInfoHs($Data, '3.1');
        self::setMsHjInfo($Data, '3.1');
        self::setMsHjInfoRs($Data, '3.1');
        self::setMsHjInfoFsLernen($Data, '3.1');
        self::setMsHjFsLernen($Data, '3.1');
        self::setMsJFsLernen($Data, '3.2');
        self::setMsHjInfoFsGeistigeEntwicklung($Data, '3.8');
        self::setMsHjFsGeistigeEntwicklung($Data, '3.8');
        self::setMsJFsGeistigeEntwicklung($Data, '3.8');
        self::setMsHjHs($Data, '3.1');
        self::setMsHjRs($Data, '3.1');
        self::setMsJHs($Data, '3.2');
        self::setMsJ($Data, '3.2');
        self::setMsJRs($Data, '3.2');
        self::setMsAbsHs($Data, '3.4');
        self::setMsAbsHsE($Data, '3.5');
        self::setMsAbsHsQ($Data, '3.6');
        self::setMsAbsLernenHs($Data, '3.10');
        self::setMsAbsLernenEquatedHs($Data, '3.10');
        self::setMsAbsLernen($Data, '3.10');
        self::setMsAbsRs($Data, '3.7');
        self::setMsAbg($Data, '3.3');
        self::setMsAbgGeistigeEntwicklung($Data, '3.9');
    }

    /**
     * @param Data $Data
     */
    private static function setCertificateBeSOFS(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Bildungsempfehlung', '§ 34 Abs. 3 SOFS', 'BeSOFS');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeRecommendation(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 7);
                    $Data->createCertificateLevel($tblCertificate, 8);
                    $Data->createCertificateLevel($tblCertificate, 9);
                }
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $row);
                $row = 1;
                $Data->setCertificateSubject($tblCertificate, 'MA', 2, $row++);
                $Data->setCertificateSubject($tblCertificate, 'BIO', 2, $row++);
                $Data->setCertificateSubject($tblCertificate, 'CH', 2, $row++);
                $Data->setCertificateSubject($tblCertificate, 'PH', 2, $row);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsHjInfoHs(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation', 'Hauptschule', 'MsHjInfoHs');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain(), true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 7);
                    $Data->createCertificateLevel($tblCertificate, 8);
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
            if (!$Data->getCertificateGradeAll($tblCertificate)) {
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GK', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, $row);
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
                $Data->setCertificateSubject($tblCertificate, 'TC', 2, $row++);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row);
            }
        }

    }

    /**
     * @param Data $Data
     */
    private static function setMsHjInfo(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation', 'Klasse 5-6', 'MsHjInfo');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(), null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 5);
                    $Data->createCertificateLevel($tblCertificate, 6);
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
            if (!$Data->getCertificateGradeAll($tblCertificate)) {
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GK', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, $row);
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
                $Data->setCertificateSubject($tblCertificate, 'TC', 2, $row++);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsHjInfoRs(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation', 'Realschule', 'MsHjInfoRs');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseReal()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseReal(), true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 7);
                    $Data->createCertificateLevel($tblCertificate, 8);
                    $Data->createCertificateLevel($tblCertificate, 9);
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
            if (!$Data->getCertificateGradeAll($tblCertificate)) {
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GK', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, $row);
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
                $Data->setCertificateSubject($tblCertificate, 'TC', 2, $row++);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsHjInfoFsLernen(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation', 'Förderschwerpunkt Lernen', 'MsHjInfoFsLernen');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    null, true, true);
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
            if (!$Data->getCertificateGradeAll($tblCertificate)) {
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GK', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, $row);
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
                $Data->setCertificateSubject($tblCertificate, 'TC', 2, $row++);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsHjFsLernen(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahreszeugnis', 'Förderschwerpunkt Lernen', 'MsHjFsLernen');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    null, false, true);
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
            if (!$Data->getCertificateGradeAll($tblCertificate)) {
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GK', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, $row);
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
                $Data->setCertificateSubject($tblCertificate, 'TC', 2, $row++);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsJFsLernen(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis', 'Förderschwerpunkt Lernen', 'MsJFsLernen');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeSecondary(),
                    null, false, true);
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
            if (!$Data->getCertificateGradeAll($tblCertificate)) {
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GK', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, $row);
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
                $Data->setCertificateSubject($tblCertificate, 'TC', 2, $row++);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsHjInfoFsGeistigeEntwicklung(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation', 'Förderschwerpunkt geistige Entwicklung', 'MsHjInfoFsGeistigeEntwicklung');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    null, true, true);
            }
        }
        // auf dem Zeugnis befinden sich keine Fächer
    }

    /**
     * @param Data $Data
     */
    private static function setMsHjFsGeistigeEntwicklung(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahreszeugnis', 'Förderschwerpunkt geistige Entwicklung', 'MsHjFsGeistigeEntwicklung');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    null, false, true);
            }
        }
        // auf dem Zeugnis befinden sich keine Fächer
    }

    /**
     * @param Data $Data
     */
    private static function setMsJFsGeistigeEntwicklung(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis', 'Förderschwerpunkt geistige Entwicklung', 'MsJFsGeistigeEntwicklung');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeSecondary(),
                    null, false, true);
            }
        }
        // auf dem Zeugnis befinden sich keine Fächer
    }

    /**
     * @param Data $Data
     */
    private static function setMsHjHs(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahreszeugnis', 'Hauptschule', 'MsHjHs');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 9);
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
            if (!$Data->getCertificateGradeAll($tblCertificate)) {
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GK', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, $row);
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
                $Data->setCertificateSubject($tblCertificate, 'TC', 2, $row++);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsHjRs(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahreszeugnis', 'Realschule', 'MsHjRs');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseReal()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseReal());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 10);
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
            if (!$Data->getCertificateGradeAll($tblCertificate)) {
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GK', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, $row);
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
                $Data->setCertificateSubject($tblCertificate, 'TC', 2, $row++);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsJHs(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis', 'Hauptschule', 'MsJHs');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 7);
                    $Data->createCertificateLevel($tblCertificate, 8);
                }
            }
            // Begrenzung des Einschätzungfelds
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 300);
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 300);
            }
            if (!$Data->getCertificateGradeAll($tblCertificate)) {
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GK', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, $row);
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
                $Data->setCertificateSubject($tblCertificate, 'TC', 2, $row++);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsJ(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis', 'Klasse 5-6', 'MsJ');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeSecondary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 5);
                    $Data->createCertificateLevel($tblCertificate, 6);
                }
            }
            // Begrenzung des Einschätzungfelds
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 300);
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 300);
            }
            if (!$Data->getCertificateGradeAll($tblCertificate)) {
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GK', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, $row);
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
                $Data->setCertificateSubject($tblCertificate, 'TC', 2, $row++);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsJRs(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis', 'Realschule', 'MsJRs');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseReal()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseReal());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 7);
                    $Data->createCertificateLevel($tblCertificate, 8);
                    $Data->createCertificateLevel($tblCertificate, 9);
                }
            }
            // Begrenzung des Einschätzungfelds
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 300);
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 300);
            }
            if (!$Data->getCertificateGradeAll($tblCertificate)) {
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GK', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, $row);
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
                $Data->setCertificateSubject($tblCertificate, 'TC', 2, $row++);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsAbsHs(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abschlusszeugnis', 'Hauptschule', 'MsAbsHs');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 9);
                }
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GK', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, $row);
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
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row);
            }
        }
    }

    /**
     * @param Data $Data
     * @param $CertificateNumber
     */
    private static function setMsAbsHsE(Data $Data, string $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abschlusszeugnis', 'Hauptschulabschluss gleichgestellten Abschluss',
            'MsAbsHsE');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GK', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, $row);
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
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsAbsHsQ(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abschlusszeugnis', 'Hauptschule qualifiziert',
            'MsAbsHsQ');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 9);
                }
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GK', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, $row);
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
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsAbsLernenHs(Data $Data, $CertificateNumber)
    {
        $tblCertificate = $Data->createCertificate('Oberschule Abschlusszeugnis', 'Förderschwerpunkt Lernen + Hauptschulbildungsgang', 'MsAbsLernenHs');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain(), false, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 9);
                }
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GK', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, $row);
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
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsAbsLernenEquatedHs(Data $Data, $CertificateNumber)
    {
        $tblCertificate = $Data->createCertificate('Oberschule Abschlusszeugnis',
            'Förderschwerpunkt Lernen + Hauptschulabschluss gleichgestellten Abschluss', 'MsAbsLernenEquatedHs');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain(), false, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 9);
                }
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GK', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, $row);
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
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsAbsLernen(Data $Data, $CertificateNumber)
    {
        $tblCertificate = $Data->createCertificate('Oberschule Abschlusszeugnis', 'Abschluss im Förderschwerpunkt Lernen', 'MsAbsLernen');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain(), false, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 9);
                }
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GK', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, $row);
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
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsAbsRs(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abschlusszeugnis', 'Realschule', 'MsAbsRs');

        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseReal()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseReal());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 10);
                }
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++, false);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++, false);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GK', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $row);
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
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsAbg(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abgangszeugnis', '', 'MsAbg',
            null, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeSecondary());
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GK', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, $row);
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
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsAbgGeistigeEntwicklung(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abgangszeugnis', 'Förderschwerpunkt geistige Entwicklung', 'MsAbgGeistigeEntwicklung',
            null, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeSecondary());
        if($tblCertificate){
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
        }
        // auf dem Zeugnis befinden sich keine Fächer
    }
}