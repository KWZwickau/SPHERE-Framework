<?php

namespace SPHERE\Application\Api\People\Meta\Subject;

use SPHERE\Application\Api\MassReplace\ApiMassReplace;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

class MassReplaceSubject extends Extension
{

    const CLASS_MASS_REPLACE_SUBJECT = 'SPHERE\Application\Api\People\Meta\Subject\MassReplaceSubject';

    const METHOD_REPLACE_SUBJECT = 'replaceSubject';

    const ATTR_TYPE = 'TypeId';
    const ATTR_RANKING = 'RankingId';

    /**
     * @return Service
     */
    private function useService()
    {

        return new Service(
            new Identifier('People', 'Meta', null, null, Consumer::useService()->getConsumerBySession()),
            'SPHERE\Application\People\Meta\Student/Service/Entity',
            'SPHERE\Application\People\Meta\Student\Service\Entity'
        );
    }

    /**
     * @param string $modalField
     * @param int    $CloneField
     * @param int    $TypeId
     * @param int    $RankingId
     * @param array  $PersonIdArray
     *
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceSubject(
        $modalField,
        $CloneField,
        $TypeId,
        $RankingId,
        $PersonIdArray = array()
    ) {

        $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeById($TypeId);
        $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingById($RankingId);

        // get selected Subject
        $tblSubject = Subject::useService()->getSubjectById($CloneField);

        // change miss matched to null
        if (!$tblSubject && null !== $tblSubject) {
            $tblSubject = null;
        }

        $this->useService()->replaceSubjectByPersonIdList($PersonIdArray, $tblSubject, $tblStudentSubjectType,
            $tblStudentSubjectRanking,
            null, null);

        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));

        // Success!
        return ApiMassReplace::pipelineClose($Field, $CloneField);

//        return new Code( print_r( $this->getGlobal()->POST, true ) )
//        .new Code( print_r( $CloneField, true ) );
    }
}