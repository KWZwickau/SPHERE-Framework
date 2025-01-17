<?php
namespace SPHERE\Application\Reporting;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonGender;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Reporting\Custom\Herrnhut\Person\Person;
use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\Handler\MemoryHandler;

abstract class AbstractModule implements IModuleInterface
{

    /**
     * @param TblPerson[] $tblPersonList
     * @param int         $CountGender
     *
     * @return int
     */
    static private function countGender(array $tblPersonList, int $CountGender): int
    {

        if (empty( $tblPersonList )) {
            return 0;
        } else {

            $Key = md5(json_encode($tblPersonList));

            $Cache = (new CacheFactory())->createHandler(new MemoryHandler());
            if (null === ( $Result = $Cache->getValue($Key, __METHOD__) )) {

                $Result = array(
                    TblCommonGender::VALUE_NULL   => 0,
                    TblCommonGender::VALUE_MALE   => 0,
                    TblCommonGender::VALUE_FEMALE => 0,
                    TblCommonGender::VALUE_DIVERS => 0,
                    TblCommonGender::VALUE_OTHER  => 0
                );
                array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$Result, $CountGender) {

                    $missingGender = true;
                    $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                    if ($tblCommon) {
                        $tblCommonBirthDates = $tblCommon->getTblCommonBirthDates();
                        if ($tblCommonBirthDates) {
                            if(($tblGender = $tblCommonBirthDates->getTblCommonGender())){
                                $Result[$tblGender->getId()]++;
                                $missingGender = false;
                            }
                        }
                    }
                    if($missingGender){
                        $Result[TblCommonGender::VALUE_NULL]++;
                    }
                });

                $Cache->setValue($Key, $Result, 0, __METHOD__);
            }

            return $Result[$CountGender];
        }
    }

    /**
     * @param TblPerson[] $tblPersonList
     *
     * @return int
     */
    static public function countMaleGenderByPersonList(array $tblPersonList): int
    {

        return self::countGender($tblPersonList, TblCommonGender::VALUE_MALE);
    }

    /**
     * @param TblPerson[] $tblPersonList
     *
     * @return int
     */
    static public function countFemaleGenderByPersonList(array $tblPersonList): int
    {

        return self::countGender($tblPersonList, TblCommonGender::VALUE_FEMALE);
    }

    /**
     * @param TblPerson[] $tblPersonList
     *
     * @return int
     */
    static public function countDiversGenderByPersonList(array $tblPersonList): int
    {

        return self::countGender($tblPersonList, TblCommonGender::VALUE_DIVERS);
    }

    /**
     * @param TblPerson[] $tblPersonList
     *
     * @return int
     */
    static public function countOtherGenderByPersonList(array $tblPersonList): int
    {

        return self::countGender($tblPersonList, TblCommonGender::VALUE_OTHER);
    }

    /**
     * @param TblPerson[] $tblPersonList
     *
     * @return int
     */
    static public function countMissingGenderByPersonList(array $tblPersonList): int
    {

        return self::countGender($tblPersonList, TblCommonGender::VALUE_NULL);
    }

    /**
     * @param PhpExcel $export
     * @param array    $tblPersonList
     * @param int      $Row
     * @param int      $StartColumn
     *
     * @return PhpExcel
     */
    static public function setGenderFooter(PhpExcel $export,array $tblPersonList, int &$Row, int $StartColumn = 0, $ValuePosition = 1): PhpExcel
    {

        $export->setValue($export->getCell($StartColumn, $Row), 'Weiblich:');
        $export->setValue($export->getCell($StartColumn + $ValuePosition, $Row), Person::countFemaleGenderByPersonList($tblPersonList));
        $Row++;
        $export->setValue($export->getCell($StartColumn, $Row), 'Männlich:');
        $export->setValue($export->getCell($StartColumn + $ValuePosition, $Row), Person::countMaleGenderByPersonList($tblPersonList));
        $Row++;
        if(($DiversCount = Person::countDiversGenderByPersonList($tblPersonList))){
            $export->setValue($export->getCell($StartColumn, $Row), 'Divers:');
            $export->setValue($export->getCell($StartColumn + $ValuePosition, $Row), $DiversCount);
            $Row++;
        }
        if(($OtherCount = Person::countOtherGenderByPersonList($tblPersonList))){
            $export->setValue($export->getCell($StartColumn, $Row), 'Ohne Angabe:');
            $export->setValue($export->getCell($StartColumn + $ValuePosition, $Row), $OtherCount);
            $Row++;
        }

        $export->setValue($export->getCell($StartColumn, $Row), 'Gesamt:');
        $export->setValue($export->getCell($StartColumn + $ValuePosition, $Row), count($tblPersonList));
        return $export;
    }
}
