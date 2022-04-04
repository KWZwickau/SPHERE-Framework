<?php
namespace SPHERE\Application\Transfer\Davinci\Import;

use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Davinci\Import
 */
class Service //  extends AbstractService
{
//    /**
//     * @param bool $doSimulation
//     * @param bool $withData
//     * @param bool $UTF8
//     *
//     * @return string
//     */
//    public function setupService($doSimulation, $withData, $UTF8)
//    {
//
////        $Protocol= '';
////        if(!$withData){
////            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
////        }
////        if (!$doSimulation && $withData) {
////            (new Data($this->getBinding()))->setupDatabaseContent();
////        }
////        return $Protocol;
//        return '';
//    }

    /**
     * @param IFormInterface|null       $Stage
     * @param null|array                $Data
     *
     * @return IFormInterface|string
     */
    public function importTimetable(IFormInterface $Stage = null, $Data = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        // ToDO Import Timetable
        if (true) {
            $Message = new Success('Import durchgeführt');
            return $Message.new Redirect('/Transfer/Davinci/Import/Timetable/Show', Redirect::TIMEOUT_SUCCESS); //ToDO Übersichtsseite
        } else {
            // ToDO Ausführlicher bericht zur Fehlermeldung
            return $Stage.new Redirect('/Transfer/Davinci/Import/Lectureship/Edit', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblTrebraImportLectureship->getId(), 'Visible' => $Visible));
        }
    }
}
