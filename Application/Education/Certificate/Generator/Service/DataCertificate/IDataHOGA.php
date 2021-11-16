<?php

namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Lesson\Division\Division;
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
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '5'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '6'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '7'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '8'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Kopfnoten
            if (!$Data->getCertificateGradeAll($tblCertificate)){
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            // Fächer
            if (!$Data->getCertificateSubjectAll($tblCertificate)){
                self::setCertificateSubjectsStandard($tblCertificate, $Data);
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
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '10'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Kopfnoten
            if (!$Data->getCertificateGradeAll($tblCertificate)){
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)){
                self::setCertificateSubjectsStandard($tblCertificate, $Data);
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
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '5'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '6'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '7'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '8'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Kopfnoten
            if (!$Data->getCertificateGradeAll($tblCertificate)){
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)){
                self::setCertificateSubjectsStandard($tblCertificate, $Data);
            }
        }
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $Data
     */
    private static function setCertificateSubjectsStandard(TblCertificate $tblCertificate, $Data)
    {
        $i = 1;
        $Data->setCertificateSubject($tblCertificate, 'DE', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'EN', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'KU', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'MU', 1, $i++);
        $Data->setCertificateSubject($tblCertificate, 'GE', 1, $i++);
        // todo GK nur OS
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
}