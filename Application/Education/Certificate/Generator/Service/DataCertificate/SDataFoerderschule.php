<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Education\Lesson\Division\Division;

class SDataFoerderschule
{

    public static function setCertificateStandard(Data $Data)
    {

        //ToDO Funktionen anpassen
        self::setFoesHjInfoGeistigeEntwicklung($Data, '2.11');
        self::setFoesHjGeistigeEntwicklung($Data, '2.11');
        self::setFoesJGeistigeEntwicklung($Data, '2.11');
        self::setFoesAbgGeistigeEntwicklung($Data, '2.12');
        self::setFoesAbsGeistigeEntwicklung($Data, '2.13');
    }

    /**
     * @param Data $Data
     */
    private static function setFoesHjInfoGeistigeEntwicklung(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Förderschule Halbjahresinformation', 'geistige Entwicklung', 'FoesHjInfoGeistigeEntwicklung');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeFoerderSchule()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeFoerderSchule(),
                    null, true, false);
            }
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '1'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '2'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '3'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '4'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '5'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '6'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '7'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '8'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '9'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '10'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '11'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
        }
        // auf dem Zeugnis befinden sich keine Fächer
    }

    /**
     * @param Data $Data
     */
    private static function setFoesHjGeistigeEntwicklung(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Förderschule Halbjahreszeugnis', 'geistige Entwicklung', 'FoesHjGeistigeEntwicklung');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeFoerderSchule()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeFoerderSchule(),
                    null, false, true);
            }
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {

                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '12'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '13'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
        }
        // auf dem Zeugnis befinden sich keine Fächer
    }

    /**
     * @param Data $Data
     */
    private static function setFoesJGeistigeEntwicklung(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Förderschule Jahreszeugnis', 'geistige Entwicklung', 'FoesJGeistigeEntwicklung');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeFoerderSchule()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeFoerderSchule(),
                    null, false, false);
            }
            if($tblCertificate->getCertificateNumber() != $CertificateNumber){
                $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
            }
            if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '1'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '2'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '3'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '4'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '5'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '6'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '7'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '8'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '9'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '10'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeFoerderSchule(), '11'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
        }
        // auf dem Zeugnis befinden sich keine Fächer
    }

    /**
     * @param Data $Data
     */
    private static function setFoesAbgGeistigeEntwicklung(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Förderschule Abgangszeugnis', 'geistige Entwicklung', 'FoesAbgGeistigeEntwicklung',
            null, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeFoerderSchule(), null, false);
        if($tblCertificate->getCertificateNumber() != $CertificateNumber){
            $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
        }
        // auf dem Zeugnis befinden sich keine Fächer
    }

    /**
     * @param Data $Data
     */
    private static function setFoesAbsGeistigeEntwicklung(Data $Data, $CertificateNumber)
    {

        $tblCertificate = $Data->createCertificate('Förderschule Abschlusszeugnis', 'geistige Entwicklung', 'FoesAbsGeistigeEntwicklung',
            null, false, false, false, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeFoerderSchule());
        if($tblCertificate->getCertificateNumber() != $CertificateNumber){
            $Data->updateCertificateNumber($tblCertificate, $CertificateNumber);
        }
        // auf dem Zeugnis befinden sich keine Fächer
    }
}