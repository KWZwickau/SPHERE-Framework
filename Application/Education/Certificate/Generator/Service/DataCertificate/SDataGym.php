<?php


namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;


use SPHERE\Application\Education\Certificate\Generator\Service\Data;

class SDataGym
{

    public static function setCertificateStandard(Data $Data)
    {

        self::setGymHjInfo($Data, '4.1');
        self::setGymHj($Data, '4.1');
        self::setGymJ($Data, '4.2');
        self::setGymKurshalbjahreszeugnis($Data, 'Anlage 4');
        self::setGymAbitur($Data, 'Anlage 18');
        self::setGymAbgSekI($Data, '4.5');
        self::setGymAbgSekII($Data, 'Anlage 6');
    }

    /**
     * @param Data $Data
     */
    private static function setGymHjInfo(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Halbjahresinformation', '', 'GymHjInfo');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
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
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
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
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
            // Platz für optionale Fremdsprachen
            $row++;
            $row++;
            $row++;
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
            $Data->setCertificateSubject($tblCertificate, 'GRW', 1, $row++);
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
            $Data->setCertificateSubject($tblCertificate, 'TC', 2, $row++);
            $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setGymHj(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Halbjahreszeugnis', '', 'GymHj');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeGym()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeGym());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 10);
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 600);
            }
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $row = 1;
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
            // Platz für optionale Fremdsprachen
            $row++;
            $row++;
            $row++;
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
            $Data->setCertificateSubject($tblCertificate, 'GRW', 1, $row++);
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
            $Data->setCertificateSubject($tblCertificate, 'TC', 2, $row++);
            $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setGymJ(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Jahreszeugnis', '', 'GymJ');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
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
            // Begrenzung des Einschätzungfelds
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 200);
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
            // Platz für optionale Fremdsprachen
            $row++;
            $row++;
            $row++;
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
            $Data->setCertificateSubject($tblCertificate, 'GRW', 1, $row++);
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
            $Data->setCertificateSubject($tblCertificate, 'TC', 2, $row++);
            $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setGymKurshalbjahreszeugnis(Data $Data, $CertificateNumber)
    {

        // Kurshalbjahreszeugnis
        $tblCertificate = $Data->createCertificate('Gymnasium Kurshalbjahreszeugnis', '', 'GymKurshalbjahreszeugnis');
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if ($Data->getTblSchoolTypeGym() && $Data->getTblCertificateTypeMidTermCourse()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeMidTermCourse(), $Data->getTblSchoolTypeGym(),
                    null);
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 270);
            }
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $row = 1;
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
            $Data->setCertificateSubject($tblCertificate, 'SOR', 1, $row++);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++, false);
            $Data->setCertificateSubject($tblCertificate, 'EN2', 1, $row++, false);
            $Data->setCertificateSubject($tblCertificate, 'FR', 1, $row++, false);
            $Data->setCertificateSubject($tblCertificate, 'RU', 1, $row++, false);
            $Data->setCertificateSubject($tblCertificate, 'LA', 1, $row++, false);
            $Data->setCertificateSubject($tblCertificate, 'SPA', 1, $row++, false);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++, false);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++, false);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, $row++);
            $Data->setCertificateSubject($tblCertificate, 'GRW', 1, $row);
            $row = 1;
            $Data->setCertificateSubject($tblCertificate, 'MA', 2, $row++);
            $Data->setCertificateSubject($tblCertificate, 'BIO', 2, $row++);
            $Data->setCertificateSubject($tblCertificate, 'CH', 2, $row++);
            $Data->setCertificateSubject($tblCertificate, 'PH', 2, $row++);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, $row++, false);
            $Data->setCertificateSubject($tblCertificate, 'RE/k', 2, $row++, false);
            $Data->setCertificateSubject($tblCertificate, 'RE/j', 2, $row++, false);
            $Data->setCertificateSubject($tblCertificate, 'ETH', 2, $row++, false);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, $row);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setGymAbitur(Data $Data, $CertificateNumber)
    {

        if (($tblCertificate = $Data->createCertificate('Gymnasium Abschlusszeugnis', 'Abitur', 'GymAbitur',
            null, false, false, false, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeGym()))
        ) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            // SSW-531 Rename
            if ($tblCertificate->getName() == 'Gymnasium Abitur') {
                $Data->updateCertificateName($tblCertificate, 'Gymnasium Abschlusszeugnis', 'Abitur');
            }

            if (!$Data->getCertificateReferenceForLanguagesAllByCertificate($tblCertificate)) {
                $Data->createCertificateReferenceForLanguages($tblCertificate, 1, 'B2', 'B2+', 'C1');
                $Data->createCertificateReferenceForLanguages($tblCertificate, 2, 'B1+', 'B2', 'B2+ - C1');
                $Data->createCertificateReferenceForLanguages($tblCertificate, 3, 'B1 - B1+', 'B2', 'B2+ - C1');
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setGymAbgSekI(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Abgangszeugnis', 'Sekundarstufe I', 'GymAbgSekI',
            null, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeGym());
        if ($tblCertificate) {
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, $row++);
                // Platz für optionale Fremdsprachen
                $row++;
                $row++;
                $row++;
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, $row++);
                $Data->setCertificateSubject($tblCertificate, 'GRW', 1, $row++);
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
                $Data->setCertificateSubject($tblCertificate, 'TC', 2, $row++);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, $row);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setGymAbgSekII(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Abgangszeugnis', 'Sekundarstufe II', 'GymAbgSekII',
            null, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeGym());
        if($tblCertificate){
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
        }
    }
}