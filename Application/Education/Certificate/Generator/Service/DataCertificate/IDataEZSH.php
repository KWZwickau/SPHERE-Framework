<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;

class IDataEZSH
{

    public static function setCertificateIndividually(Data $Data)
    {

        $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('EZSH');
        if ($tblConsumerCertificate){
            self::setEzshMsHj($Data, $tblConsumerCertificate);
            self::setEzshMsCourseHj($Data, $tblConsumerCertificate);
            self::setEzshMsCourseHjZ($Data, $tblConsumerCertificate);
            self::setEzshGymHj($Data, $tblConsumerCertificate);
            self::setEzshGymHjZ($Data, $tblConsumerCertificate);
            self::setEzshGymJ($Data, $tblConsumerCertificate);
            self::setEzshGymJThreePages($Data, $tblConsumerCertificate);
            self::setEzshMsJ($Data, $tblConsumerCertificate);
            self::setEzshMsCourseJ($Data, $tblConsumerCertificate);
            self::setEzshGymAbg($Data, $tblConsumerCertificate);
            self::setEzshKurshalbjahreszeugnis($Data, $tblConsumerCertificate);

            self::setEzshMsAbsHs($Data, $tblConsumerCertificate);
            self::setEzshMsAbsHsQ($Data, $tblConsumerCertificate);
            self::setEzshMsAbsRs($Data, $tblConsumerCertificate);
            self::setEzshMsAbg($Data, $tblConsumerCertificate);
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEzshMsHj(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation', 'Klasse 5-6',
            'EZSH\EzshMsHj', $tblConsumerCertificate, false, true);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypeSecondary(), null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 5);
                    $Data->createCertificateLevel($tblCertificate, 6);
                }
            }
            // Begrenzung der Einschätzung
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                $Data->createCertificateField($tblCertificate, $FieldName, 2500);
            }
            // Begrenzung Bemerkungsfeld
            $FieldName = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                $Data->createCertificateField($tblCertificate, $FieldName, 1000);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                $Data->setCertificateSubject($tblCertificate, 'GRW', 1, 6);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, 5);
                $Data->setCertificateSubject($tblCertificate, 'TUC', 2, 6);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 7);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
            }
        }
    }
    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEzshMsCourseHj(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation', 'Klasse 7-9',
            'EZSH\EzshMsCourseHj', $tblConsumerCertificate, false, true, true);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypeSecondary(), null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 7);
                    $Data->createCertificateLevel($tblCertificate, 8);
                    $Data->createCertificateLevel($tblCertificate, 9);
                }
            }
            // Begrenzung der Einschätzung
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                $Data->createCertificateField($tblCertificate, $FieldName, 2500);
            }
            // Begrenzung Bemerkungsfeld
            $FieldName = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                $Data->createCertificateField($tblCertificate, $FieldName, 1000);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                $Data->setCertificateSubject($tblCertificate, 'GRW', 1, 6);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, 5);
                $Data->setCertificateSubject($tblCertificate, 'TUC', 2, 6);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 7);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEzshMsCourseHjZ(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahreszeugnis', 'Klasse 9 Hauptschule',
            'EZSH\EzshMsCourseHjZ', $tblConsumerCertificate, false, false, false);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypeSecondary(), null, false);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 9);
                }
            }
            // Begrenzung der Einschätzung
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                $Data->createCertificateField($tblCertificate, $FieldName, 2500);
            }
            // Begrenzung Bemerkungsfeld
            $FieldName = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                $Data->createCertificateField($tblCertificate, $FieldName, 1000);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                $Data->setCertificateSubject($tblCertificate, 'GRW', 1, 6);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, 5);
                $Data->setCertificateSubject($tblCertificate, 'TUC', 2, 6);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 7);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEzshGymHj(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Halbjahresinformation', 'Klasse 5-9',
            'EZSH\EzshGymHj', $tblConsumerCertificate, false, true);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeGym()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypeGym(), null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 5);
                    $Data->createCertificateLevel($tblCertificate, 6);
                    $Data->createCertificateLevel($tblCertificate, 7);
                    $Data->createCertificateLevel($tblCertificate, 8);
                    $Data->createCertificateLevel($tblCertificate, 9);
                }
            }
            // Begrenzung der Einschätzung
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                $Data->createCertificateField($tblCertificate, $FieldName, 2500);
            }
            // Begrenzung Bemerkungsfeld
            $FieldName = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                $Data->createCertificateField($tblCertificate, $FieldName, 1000);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);

                $Data->setCertificateSubject($tblCertificate, 'TSCN', 1, 3, false);
                $Data->setCertificateSubject($tblCertificate, 'LA', 1, 4, false);
                // lücke für Fremdsprachen
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, 6);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, 7);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, 8);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 9);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 10);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, 5);
                $Data->setCertificateSubject($tblCertificate, 'TUC', 2, 6);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 7);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
            }
        }
    }
    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEzshGymHjZ(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Halbjahreszeugnis', 'Klasse 10',
            'EZSH\EzshGymHjZ', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeGym()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypeGym());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 10);
                }
            }
            // Begrenzung der Einschätzung
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                $Data->createCertificateField($tblCertificate, $FieldName, 2500);
            }
            // Begrenzung Bemerkungsfeld
            $FieldName = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                $Data->createCertificateField($tblCertificate, $FieldName, 1000);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                $Data->setCertificateSubject($tblCertificate, 'TSCN', 1, 3, false);
                $Data->setCertificateSubject($tblCertificate, 'LA', 1, 4, false);
                // lücke für Fremdsprachen
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, 6);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, 7);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, 8);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 9);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 10);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, 5);
                $Data->setCertificateSubject($tblCertificate, 'TUC', 2, 6);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 7);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
            }
        }
    }
    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEzshGymJ(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Jahreszeugnis', '',
            'EZSH\EzshGymJ', $tblConsumerCertificate, false, false, false, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeGym());
        if ($tblCertificate) {
            if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                $Data->createCertificateLevel($tblCertificate, 5);
                $Data->createCertificateLevel($tblCertificate, 6);
                $Data->createCertificateLevel($tblCertificate, 7);
                $Data->createCertificateLevel($tblCertificate, 8);
                $Data->createCertificateLevel($tblCertificate, 9);
            }
            // Begrenzung der Einschätzung
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                $Data->createCertificateField($tblCertificate, $FieldName, 2300);
            }
            // Begrenzung Bemerkungsfeld
            $FieldName = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                $Data->createCertificateField($tblCertificate, $FieldName, 1000);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);

                $Data->setCertificateSubject($tblCertificate, 'TSCN', 1, 3, false);
                $Data->setCertificateSubject($tblCertificate, 'LA', 1, 4, false);
                // lücke für Fremdsprachen
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, 6);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, 7);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, 8);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 9);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 10);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, 5);
                $Data->setCertificateSubject($tblCertificate, 'TUC', 2, 6);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 7);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEzshGymJThreePages(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Jahreszeugnis', 'Klasse 10 (Extra Seite für Einschätzung)',
            'EZSH\EzshGymJThreePages', $tblConsumerCertificate, false, false, true, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeGym());
        if ($tblCertificate) {
            if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                $Data->createCertificateLevel($tblCertificate, 10);
            }
            // Begrenzung der Einschätzung
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                $Data->createCertificateField($tblCertificate, $FieldName, 2300);
            }
            // Begrenzung Bemerkungsfeld
            $FieldName = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                $Data->createCertificateField($tblCertificate, $FieldName, 1000);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);

                $Data->setCertificateSubject($tblCertificate, 'TSCN', 1, 3, false);
                $Data->setCertificateSubject($tblCertificate, 'LA', 1, 4, false);
                // lücke für Fremdsprachen
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, 6);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, 7);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, 8);
                $Data->setCertificateSubject($tblCertificate, 'GRW', 1, 9);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 10);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, 5);
                $Data->setCertificateSubject($tblCertificate, 'TUC', 2, 6);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 7);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEzshMsJ(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis', 'Klasse 5-6',
            'EZSH\EzshMsJ', $tblConsumerCertificate, false, false, false, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeSecondary());
        if ($tblCertificate) {
            if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                $Data->createCertificateLevel($tblCertificate, 5);
                $Data->createCertificateLevel($tblCertificate, 6);
            }
            // Begrenzung der Einschätzung
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                $Data->createCertificateField($tblCertificate, $FieldName, 2300);
            }
            // Begrenzung Bemerkungsfeld
            $FieldName = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                $Data->createCertificateField($tblCertificate, $FieldName, 1000);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                $Data->setCertificateSubject($tblCertificate, 'GRW', 1, 6);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, 5);
                $Data->setCertificateSubject($tblCertificate, 'TUC', 2, 6);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 7);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
            }
        }
    }
    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEzshMsCourseJ(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis', 'Klasse 7-10',
            'EZSH\EzshMsCourseJ', $tblConsumerCertificate, false, false, true, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeSecondary());
        if ($tblCertificate) {
            if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                $Data->createCertificateLevel($tblCertificate, 7);
                $Data->createCertificateLevel($tblCertificate, 8);
                $Data->createCertificateLevel($tblCertificate, 9);
                $Data->createCertificateLevel($tblCertificate, 10);
            }
            // Begrenzung der Einschätzung
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                $Data->createCertificateField($tblCertificate, $FieldName, 2300);
            }
            // Begrenzung Bemerkungsfeld
            $FieldName = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                $Data->createCertificateField($tblCertificate, $FieldName, 1000);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                $Data->setCertificateSubject($tblCertificate, 'GRW', 1, 6);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, 5);
                $Data->setCertificateSubject($tblCertificate, 'TUC', 2, 6);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 7);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
            }
        }
    }
    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEzshGymAbg(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Abgangszeugnis', 'Klasse 10',
            'EZSH\EzshGymAbg', $tblConsumerCertificate, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeGym());
        if ($tblCertificate) {
            if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                $Data->createCertificateLevel($tblCertificate, 10);
            }
            // Begrenzung Bemerkungsfeld
            $FieldName = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                $Data->createCertificateField($tblCertificate, $FieldName, 1000);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);

                $Data->setCertificateSubject($tblCertificate, 'TSCN', 1, 3, false);
                $Data->setCertificateSubject($tblCertificate, 'LA', 1, 4, false);
                // lücke für Fremdsprachen
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, 6);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, 7);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, 8);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 9);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 10);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, 5);
                $Data->setCertificateSubject($tblCertificate, 'TUC', 2, 6);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 7);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
            }
        }
    }
    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEzshKurshalbjahreszeugnis(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Kurshalbjahreszeugnis', '', 'EZSH\EzshKurshalbjahreszeugnis',
            $tblConsumerCertificate, false, false, false, $Data->getTblCertificateTypeMidTermCourse(), $Data->getTblSchoolTypeGym());
        if ($tblCertificate) {
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 270);
            }
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $row = 1;
            $Data->setCertificateSubject($tblCertificate, 'DE', $row, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', $row, 2, false);
            $Data->setCertificateSubject($tblCertificate, 'EN2', $row, 3, false);
            $Data->setCertificateSubject($tblCertificate, 'EN3', $row, 4, false);
            $Data->setCertificateSubject($tblCertificate, 'TSCN', $row, 5, false);
            $Data->setCertificateSubject($tblCertificate, 'TSCF', $row, 6, false);
            $Data->setCertificateSubject($tblCertificate, 'LA', $row, 7, false);
            $Data->setCertificateSubject($tblCertificate, 'LA-F', $row, 8, false);
            $Data->setCertificateSubject($tblCertificate, 'SPA', $row, 9, false);

            $Data->setCertificateSubject($tblCertificate, 'KU', $row, 10, false);
            $Data->setCertificateSubject($tblCertificate, 'MU', $row, 11, false);
            $Data->setCertificateSubject($tblCertificate, 'GE', $row, 12);
            $Data->setCertificateSubject($tblCertificate, 'GEO', $row, 13);
            $Data->setCertificateSubject($tblCertificate, 'GRW', $row, 14);

            $row = 2;
            $Data->setCertificateSubject($tblCertificate, 'MA', $row, 1);
            $Data->setCertificateSubject($tblCertificate, 'BIO', $row, 2);
            $Data->setCertificateSubject($tblCertificate, 'CH', $row, 3);
            $Data->setCertificateSubject($tblCertificate, 'PH', $row, 4);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', $row, 5, false);
            $Data->setCertificateSubject($tblCertificate, 'RE/k', $row, 6, false);
            $Data->setCertificateSubject($tblCertificate, 'ETH', $row, 7, false);
            $Data->setCertificateSubject($tblCertificate, 'SPO', $row, 8);
            $Data->setCertificateSubject($tblCertificate, 'INF', $row, 9);
            $Data->setCertificateSubject($tblCertificate, 'PHI', $row, 10);
        }
    }

    /**
     * @param Data $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEzshMsAbsHs(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abschlusszeugnis', 'Hauptschule', 'EZSH\EzshMsAbsHs', $tblConsumerCertificate);
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $Data->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 6);
            $Data->setCertificateSubject($tblCertificate, 'INF', 2, 7);
        }
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 9);
                }
            }
        }
    }

    /**
     * @param Data $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEzshMsAbsHsQ(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abschlusszeugnis', 'Hauptschule qualifiziert',
            'EZSH\EzshMsAbsHsQ', $tblConsumerCertificate, false, false, true);
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $Data->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 6);
            $Data->setCertificateSubject($tblCertificate, 'INF', 2, 7);
        }
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 9);
                }
            }
        }
    }

    /**
     * @param Data $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEzshMsAbsRs(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abschlusszeugnis', 'Realschule', 'EZSH\EzshMsAbsRs', $tblConsumerCertificate);
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3, false);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4, false);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 6);
            $Data->setCertificateSubject($tblCertificate, 'GK', 1, 7);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 6);
            $Data->setCertificateSubject($tblCertificate, 'INF', 2, 7);
        }
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseReal()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseReal());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 10);
                }
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEzshMsAbg(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abgangszeugnis', '',
            'EZSH\EzshMsAbg', $tblConsumerCertificate, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeSecondary());
        if ($tblCertificate) {
            if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                $Data->createCertificateLevel($tblCertificate, 10);
            }
            // Begrenzung Bemerkungsfeld
            $FieldName = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                $Data->createCertificateField($tblCertificate, $FieldName, 1000);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                $Data->setCertificateSubject($tblCertificate, 'GK', 1, 6);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 8, false);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
                $Data->setCertificateSubject($tblCertificate, 'REE', 2, 6);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, 7);
            }
        }
    }
}