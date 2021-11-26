<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Education\Lesson\Division\Division;

class SDataSecondary
{

    public static function setCertificateStandard(Data $Data)
    {

        // wird aktuell nicht benötigt
        // $tblCertificate = $Data->createCertificate('Oberschule Halbjahreszeugnis', 'Klasse 5-6', 'MsHj');
        if (($tblCertificate = $Data->getCertificateByCertificateClassName('MsHj'))) {
            $Data->destroyCertificate($tblCertificate);
        }

        self::setCertificateBeSOFS($Data);
        self::setMsHjInfoHs($Data);
        self::setMsHjInfo($Data);
        self::setMsHjInfoRs($Data);
        self::setMsHjInfoFsLernen($Data);
        self::setMsHjFsLernen($Data);
        self::setMsJFsLernen($Data);
        self::setMsHjInfoFsGeistigeEntwicklung($Data);
        self::setMsHjFsGeistigeEntwicklung($Data);
        self::setMsJFsGeistigeEntwicklung($Data);
        self::setMsHjHs($Data);
        self::setMsHjRs($Data);
        self::setMsJHs($Data);
        self::setMsJ($Data);
        self::setMsJRs($Data);
        self::setMsAbsHs($Data);
        self::setMsAbsHsQ($Data);
        self::setMsAbsLernenHs($Data);
        self::setMsAbsLernenEquatedHs($Data);
        self::setMsAbsLernen($Data);
        self::setMsAbsRs($Data);
        self::setMsAbg($Data);
        self::setMsAbgLernen($Data);
        self::setMsAbgLernenHs($Data);
        self::setMsAbgGeistigeEntwicklung($Data);
    }

    /**
     * @param Data $Data
     */
    private static function setCertificateBeSOFS(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Bildungsempfehlung', '§ 34 Abs. 3 SOFS', 'BeSOFS');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeRecommendation(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '7'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '8'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
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

    /**
     * @param Data $Data
     */
    private static function setMsHjInfoHs(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation', 'Hauptschule', 'MsHjInfoHs');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain(), true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '7'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '8'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
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

    /**
     * @param Data $Data
     */
    private static function setMsHjInfo(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation', 'Klasse 5-6', 'MsHjInfo');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(), null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '5'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '6'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
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
    private static function setMsHjInfoRs(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation', 'Realschule', 'MsHjInfoRs');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseReal()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseReal(), true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '7'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '8'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
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

    /**
     * @param Data $Data
     */
    private static function setMsHjInfoFsLernen(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation', 'Förderschwerpunkt Lernen', 'MsHjInfoFsLernen');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    null, true, true);
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
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

    /**
     * @param Data $Data
     */
    private static function setMsHjFsLernen(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahreszeugnis', 'Förderschwerpunkt Lernen', 'MsHjFsLernen');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    null, false, true);
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
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

    /**
     * @param Data $Data
     */
    private static function setMsJFsLernen(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis', 'Förderschwerpunkt Lernen', 'MsJFsLernen');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeSecondary(),
                    null, false, true);
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
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

    /**
     * @param Data $Data
     */
    private static function setMsHjInfoFsGeistigeEntwicklung(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation', 'Förderschwerpunkt geistige Entwicklung', 'MsHjInfoFsGeistigeEntwicklung');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    null, true, true);
            }
        }
        if ($tblCertificate) {
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
    private static function setMsHjFsGeistigeEntwicklung(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahreszeugnis', 'Förderschwerpunkt geistige Entwicklung', 'MsHjFsGeistigeEntwicklung');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    null, false, true);
            }
        }
        if ($tblCertificate) {
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
    private static function setMsJFsGeistigeEntwicklung(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis', 'Förderschwerpunkt geistige Entwicklung', 'MsJFsGeistigeEntwicklung');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeSecondary(),
                    null, false, true);
            }
        }
        if ($tblCertificate) {
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
    private static function setMsHjHs(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahreszeugnis', 'Hauptschule', 'MsHjHs');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
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

    /**
     * @param Data $Data
     */
    private static function setMsHjRs(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahreszeugnis', 'Realschule', 'MsHjRs');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseReal()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseReal());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '10'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
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

    /**
     * @param Data $Data
     */
    private static function setMsJHs(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis', 'Hauptschule', 'MsJHs');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '7'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '8'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
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
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
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

    /**
     * @param Data $Data
     */
    private static function setMsJ(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis', 'Klasse 5-6', 'MsJ');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeSecondary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '5'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '6'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
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
    private static function setMsJRs(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis', 'Realschule', 'MsJRs');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseReal()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseReal());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '7'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '8'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
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
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
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

    /**
     * @param Data $Data
     */
    private static function setMsAbsHs(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abschlusszeugnis', 'Hauptschule', 'MsAbsHs');
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
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
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsAbsHsQ(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abschlusszeugnis', 'Hauptschule qualifiziert',
            'MsAbsHsQ');
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
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
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsAbsLernenHs(Data $Data)
    {
        $tblCertificate = $Data->createCertificate('Oberschule Abschlusszeugnis', 'Förderschwerpunkt Lernen + Hauptschulbildungsgang', 'MsAbsLernenHs');
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
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
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain(), false, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsAbsLernenEquatedHs(Data $Data)
    {
        $tblCertificate = $Data->createCertificate('Oberschule Abschlusszeugnis',
            'Förderschwerpunkt Lernen + Hauptschulabschluss gleichgestellten Abschluss', 'MsAbsLernenEquatedHs');
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
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
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain(), false, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsAbsLernen(Data $Data)
    {
        $tblCertificate = $Data->createCertificate('Oberschule Abschlusszeugnis', 'Abschluss im Förderschwerpunkt Lernen', 'MsAbsLernen');
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
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
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain(), false, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsAbsRs(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abschlusszeugnis', 'Realschule', 'MsAbsRs');
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
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
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseReal()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseReal());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '10'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsAbg(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abgangszeugnis', '', 'MsAbg',
            null, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeSecondary());
        if ($tblCertificate) {
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
    private static function setMsAbgLernen(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abgangszeugnis', 'Förderschwerpunkt Lernen', 'MsAbgLernen',
            null, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeSecondary());
        if ($tblCertificate) {
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
    private static function setMsAbgLernenHs(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abgangszeugnis', 'Förderschwerpunkt Lernen + Hauptschulbildungsgang', 'MsAbgLernenHs',
            null, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeSecondary());
        if ($tblCertificate) {
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
    private static function setMsAbgGeistigeEntwicklung(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abgangszeugnis', 'Förderschwerpunkt geistige Entwicklung', 'MsAbgGeistigeEntwicklung',
            null, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeSecondary());
        if ($tblCertificate) {
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
}