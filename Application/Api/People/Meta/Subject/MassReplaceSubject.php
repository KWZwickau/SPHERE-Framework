<?php

namespace SPHERE\Application\Api\People\Meta\Subject;

use SPHERE\Application\Api\MassReplace\ApiMassReplace;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\System\Extension\Extension;

class MassReplaceSubject extends Extension
{

    const CLASS_MASS_REPLACE_SUBJECT = 'SPHERE\Application\Api\People\Meta\Subject\MassReplaceSubject';

    const METHOD_REPLACE_SUBJECT = 'replaceSubject';

    const ATTR_TYPE = 'TypeId';
    const ATTR_RANKING = 'RankingId';

    /**
     * @return StudentService
     */
    private function useStudentService()
    {

        return new StudentService();
    }

    /**
     * @param string $modalField
     * @param int    $CloneField
     * @param int    $TypeId
     * @param int    $RankingId
     * @param array  $PersonIdArray
     * @param null   $Id
     *
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceSubject(
        $modalField,
        $CloneField,
        $TypeId,
        $RankingId,
        $PersonIdArray = array(),
        $Id = null
    ) {

        $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeById($TypeId);
        $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingById($RankingId);

        // get selected Subject
        $tblSubject = Subject::useService()->getSubjectById($CloneField);

        // change miss matched to null
        if (!$tblSubject && null !== $tblSubject) {
            $tblSubject = null;
        }

        if ($tblStudentSubjectType && $tblStudentSubjectRanking && !empty($PersonIdArray)) {
            $this->useStudentService()->replaceSubjectByPersonIdList($PersonIdArray, $tblSubject,
                $tblStudentSubjectType,
                $tblStudentSubjectRanking);
        }

        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));

        // Success!
        $IsChange = false;
        if($Id != null && !empty($PersonIdArray)){
            if(array_search($Id, $PersonIdArray)){
                $IsChange = true;
            }
        }
        return ApiMassReplace::pipelineClose($Field, $CloneField, $IsChange);

//        return new Code( print_r( $this->getGlobal()->POST, true ) )
//        .new Code( print_r( $CloneField, true ) );
    }
}