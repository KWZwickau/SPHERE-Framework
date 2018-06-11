<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 07.06.2018
 * Time: 15:44
 */

namespace SPHERE\Application\Education\Lesson\Division\Filter;

use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Lesson\Division\Filter
 */
class Service
{

    /**
     * @param IFormInterface $form
     * @param TblDivisionSubject $tblDivisionSubject
     * @param null $Data
     *
     * @return IFormInterface|string
     */
    public static function setFilter(IFormInterface $form, TblDivisionSubject $tblDivisionSubject, $Data = null)
    {

        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $form;
        }

        $filter = new Filter($tblDivisionSubject);
        $filter->setFilter($Data);
        $filter->save();

        return new Success('Die verfügbaren Schüler werden gefiltert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect('/Education/Lesson/Division/SubjectStudent/Add', Redirect::TIMEOUT_SUCCESS, array(
                'Id' => ($tblDivision = $tblDivisionSubject->getTblDivision()) ? $tblDivision->getId() : 0,
                'DivisionSubjectId' => $tblDivisionSubject->getId()
            ));
    }
}