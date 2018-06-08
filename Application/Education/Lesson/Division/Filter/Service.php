<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 07.06.2018
 * Time: 15:44
 */

namespace SPHERE\Application\Education\Lesson\Division\Filter;


use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;

class Service
{

    /**
     * @param IFormInterface $form
     * @param $Id
     * @param $DivisionSubjectId
     * @param null $Filter
     *
     * @return IFormInterface|string
     */
    public static function getFilter(IFormInterface $form, $Id, $DivisionSubjectId, $Filter = null)
    {

        /**
         * Skip to Frontend
         */
        if ($Filter === null) {
            return $form;
        }

        $CourseId = isset($Filter['Course']) ? $Filter['Course'] : 0;
        $GroupId = $Filter['Group'];
        $GenderId = $Filter['Gender'];
        $ReligionId = $Filter['SubjectReligion'];
        $ProfileId = isset($Filter['SubjectProfile']) ? $Filter['SubjectProfile'] : 0;
        $OrientationId = isset($Filter['SubjectOrientation']) ? $Filter['SubjectOrientation'] : 0;
        $ElectiveId = $Filter['SubjectElective'];
        $ForeignLanguageId = $Filter['SubjectForeignLanguage'];

        return new Success('Die verfügbaren Schüler werden gefiltert.',
                new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect('/Education/Lesson/Division/SubjectStudent/Add', Redirect::TIMEOUT_SUCCESS, array(
                'Id'               => $Id,
                'DivisionSubjectId'        => $DivisionSubjectId,
                'FilteredCourseId' => $CourseId,
                'FilteredGroupId' => $GroupId,
                'FilteredGenderId' => $GenderId,
                'FilteredReligionId' => $ReligionId,
                'FilteredProfileId' => $ProfileId,
                'FilteredOrientationId' => $OrientationId,
                'FilteredElectiveId' => $ElectiveId,
                'FilteredForeignLanguageId' => $ForeignLanguageId
            ));
    }
}