<?php
namespace SPHERE\Application\Reporting;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\Handler\MemoryHandler;

abstract class AbstractModule implements IModuleInterface
{

    /**
     * @param TblPerson[] $tblPersonList
     *
     * @return int
     */
    static public function countMaleGenderByPersonList($tblPersonList)
    {

        return self::countGender($tblPersonList, TblCommonBirthDates::VALUE_GENDER_MALE);
    }

    /**
     * @param TblPerson[] $tblPersonList
     * @param int         $CountGender
     *
     * @return int
     */
    static private function countGender($tblPersonList, $CountGender)
    {

        if (empty( $tblPersonList )) {
            return 0;
        } else {

            $Key = md5(json_encode($tblPersonList));

            $Cache = (new CacheFactory())->createHandler(new MemoryHandler());
            if (null === ( $Result = $Cache->getValue($Key, __METHOD__) )) {

                $Result = array(
                    TblCommonBirthDates::VALUE_GENDER_NULL   => 0,
                    TblCommonBirthDates::VALUE_GENDER_FEMALE => 0,
                    TblCommonBirthDates::VALUE_GENDER_MALE   => 0
                );
                array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$Result, $CountGender) {

                    $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                    if ($tblCommon) {
                        $tblCommonBirthDates = $tblCommon->getTblCommonBirthDates();
                        if ($tblCommonBirthDates) {
                            $Result[$tblCommonBirthDates->getGender()]++;
                        } else {
//                            if ($CountGender == TblCommonBirthDates::VALUE_GENDER_NULL) {
                                $Result[TblCommonBirthDates::VALUE_GENDER_NULL]++;
//                            }
                        }
                    } else {
//                        if ($CountGender == TblCommonBirthDates::VALUE_GENDER_NULL) {
                            $Result[TblCommonBirthDates::VALUE_GENDER_NULL]++;
//                        }
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
    static public function countFemaleGenderByPersonList($tblPersonList)
    {

        return self::countGender($tblPersonList, TblCommonBirthDates::VALUE_GENDER_FEMALE);
    }

    /**
     * @param TblPerson[] $tblPersonList
     *
     * @return int
     */
    static public function countMissingGenderByPersonList($tblPersonList)
    {

        return self::countGender($tblPersonList, TblCommonBirthDates::VALUE_GENDER_NULL);
    }
}
