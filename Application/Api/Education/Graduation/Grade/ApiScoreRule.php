<?php

namespace SPHERE\Application\Api\Education\Graduation\Grade;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRuleSubject;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRuleSubjectDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Extension\Extension;

class ApiScoreRule  extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = ''): string
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('loadScoreRuleSubjects');
        $Dispatcher->registerMethod('saveScoreRuleSubjects');
        $Dispatcher->registerMethod('loadScoreRuleSubjectDivisionCourses');
        $Dispatcher->registerMethod('saveScoreRuleSubjectDivisionCourses');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverBlock(string $Content = '', string $Identifier = ''): BlockReceiver
    {
        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @param $ScoreRuleId
     *
     * @return Pipeline
     */
    public static function pipelineLoadScoreRuleSubjects($ScoreRuleId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ScoreRuleSubjectsContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadScoreRuleSubjects',
        ));
        $ModalEmitter->setPostPayload(array(
            'ScoreRuleId' => $ScoreRuleId
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $ScoreRuleId
     * @param null $Data
     *
     * @return string
     */
    public function loadScoreRuleSubjects($ScoreRuleId = null, $Data = null): string
    {
        if (!($tblScoreRule = Grade::useService()->getScoreRuleById($ScoreRuleId))) {
            return (new Danger("Berechnungsvorschrift wurde nicht gefunden!", new Exclamation()));
        }

        return Grade::useFrontend()->loadScoreRuleSubjects($tblScoreRule, $Data);
    }

    /**
     * @param $ScoreRuleId
     *
     * @return Pipeline
     */
    public static function pipelineSaveScoreRuleSubjects($ScoreRuleId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ScoreRuleSubjectsContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveScoreRuleSubjects'
        ));
        $ModalEmitter->setPostPayload(array(
            'ScoreRuleId' => $ScoreRuleId
        ));
        $ModalEmitter->setLoadingMessage("Wird bearbeitet");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $ScoreRuleId
     * @param $Data
     *
     * @return string
     */
    public function saveScoreRuleSubjects($ScoreRuleId, $Data): string
    {
        if (!($tblScoreRule = Grade::useService()->getScoreRuleById($ScoreRuleId))) {
            return (new Danger("Berechnungsvorschrift wurde nicht gefunden!", new Exclamation()));
        }
        if (!($tblSchoolType = Type::useService()->getTypeById($Data['SchoolType']))) {
            return (new Danger("Schulart wurde nicht gefunden!", new Exclamation()));
        }
        if (!($tblYear = Term::useService()->getYearById($Data['Year']))) {
            return (new Danger("Schuljahr wurde nicht gefunden!", new Exclamation()));
        }

        $createList = array();
        $updateList = array();
        $removeList = array();
        $keepList = array();

        if (($tblScoreRuleSubjectList = $tblScoreRule->getScoreRuleSubjects($tblYear, $tblSchoolType))) {
            foreach ($tblScoreRuleSubjectList as $tblScoreRuleSubject) {
                if (($tblSubject = $tblScoreRuleSubject->getServiceTblSubject())) {
                    // löschen
                    if (!isset($Data['Subjects'][$tblScoreRuleSubject->getLevel()][$tblSubject->getId()])) {
                        $removeList[] = $tblScoreRuleSubject;
                    } else {
                        $keepList[$tblScoreRuleSubject->getLevel()][$tblSubject->getId()] = $tblScoreRuleSubject;
                    }
                }
            }
        }

        if (isset($Data['Subjects'])) {
            foreach ($Data['Subjects'] as $level => $subjectList) {
                foreach ($subjectList as $subjectId => $value) {
                    if (($tblSubject = Subject::useService()->getSubjectById($subjectId))) {
                        if (isset($keepList[$level][$subjectId])) {
                            continue;
                            // update
                        } elseif (($tblScoreRuleSubject = Grade::useService()->getScoreRuleSubjectByYearAndSchoolTypeAndLevelAndSubject(
                            $tblYear, $tblSchoolType, $level, $tblSubject
                        ))) {
                            $tblScoreRuleSubject->setTblScoreRule($tblScoreRule);
                            $updateList[] = $tblScoreRuleSubject;
                            // neu
                        } else {
                            $createList[] = new TblScoreRuleSubject($tblYear, $tblSchoolType, $level, $tblSubject, $tblScoreRule);
                        }
                    }
                }
            }
        }

        if (!empty($createList)) {
            Grade::useService()->createEntityListBulk($createList);
        }
        if (!empty($updateList)) {
            Grade::useService()->updateEntityListBulk($updateList);
        }
        if (!empty($removeList)) {
            Grade::useService()->deleteEntityListBulk($removeList);
        }

        return new Success("Daten wurde erfolgreich gespeichert.")
            . new Redirect('/Education/Graduation/Grade/ScoreRule', Redirect::TIMEOUT_SUCCESS);
    }

    /**
     * @param $ScoreRuleId
     *
     * @return Pipeline
     */
    public static function pipelineLoadScoreRuleSubjectDivisionCourses($ScoreRuleId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ScoreRuleSubjectDivisionCoursesContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadScoreRuleSubjectDivisionCourses',
        ));
        $ModalEmitter->setPostPayload(array(
            'ScoreRuleId' => $ScoreRuleId
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $ScoreRuleId
     * @param null $Data
     *
     * @return string
     */
    public function loadScoreRuleSubjectDivisionCourses($ScoreRuleId = null, $Data = null): string
    {
        if (!($tblScoreRule = Grade::useService()->getScoreRuleById($ScoreRuleId))) {
            return (new Danger("Berechnungsvorschrift wurde nicht gefunden!", new Exclamation()));
        }

        return Grade::useFrontend()->loadScoreRuleSubjectDivisionCourses($tblScoreRule, $Data);
    }

    /**
     * @param $ScoreRuleId
     *
     * @return Pipeline
     */
    public static function pipelineSaveScoreRuleSubjectDivisionCourses($ScoreRuleId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ScoreRuleSubjectDivisionCoursesContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveScoreRuleSubjectDivisionCourses'
        ));
        $ModalEmitter->setPostPayload(array(
            'ScoreRuleId' => $ScoreRuleId
        ));
        $ModalEmitter->setLoadingMessage("Wird bearbeitet");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $ScoreRuleId
     * @param $Data
     *
     * @return string
     */
    public function saveScoreRuleSubjectDivisionCourses($ScoreRuleId, $Data): string
    {
        if (!($tblScoreRule = Grade::useService()->getScoreRuleById($ScoreRuleId))) {
            return (new Danger("Berechnungsvorschrift wurde nicht gefunden!", new Exclamation()));
        }
        if (!($tblSchoolType = Type::useService()->getTypeById($Data['SchoolType']))) {
            return (new Danger("Schulart wurde nicht gefunden!", new Exclamation()));
        }
        if (!($tblYear = Term::useService()->getYearById($Data['Year']))) {
            return (new Danger("Schuljahr wurde nicht gefunden!", new Exclamation()));
        }

        $createList = array();
        $updateList = array();
        $removeList = array();
        $keepList = array();

        if (($tblScoreRuleSubjectDivisionCourseList = $tblScoreRule->getScoreRuleSubjectDivisionCourses($tblYear, $tblSchoolType))) {
            foreach ($tblScoreRuleSubjectDivisionCourseList as $tblScoreRuleSubjectDivisionCourse) {
                if (($tblSubject = $tblScoreRuleSubjectDivisionCourse->getServiceTblSubject())
                    && ($tblDivisionCourse = $tblScoreRuleSubjectDivisionCourse->getServiceTblDivisionCourse())
                ) {
                    // löschen
                    if (!isset($Data['SubjectDivisionCourses'][$tblDivisionCourse->getId()][$tblSubject->getId()])) {
                        $removeList[] = $tblScoreRuleSubjectDivisionCourse;
                    } else {
                        $keepList[$tblDivisionCourse->getId()][$tblSubject->getId()] = $tblScoreRuleSubjectDivisionCourse;
                    }
                }
            }
        }

        if (isset($Data['SubjectDivisionCourses'])) {
            foreach ($Data['SubjectDivisionCourses'] as $divisionCourseId => $subjectList) {
                if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($divisionCourseId))) {
                    foreach ($subjectList as $subjectId => $value) {
                        if (($tblSubject = Subject::useService()->getSubjectById($subjectId))) {
                            if (isset($keepList[$divisionCourseId][$subjectId])) {
                                continue;
                                // update
                            } elseif (($tblTemp = Grade::useService()->getScoreRuleSubjectDivisionCourseByDivisionCourseAndSubject(
                                $tblDivisionCourse, $tblSubject
                            ))) {
                                $tblTemp->setTblScoreRule($tblScoreRule);
                                $updateList[] = $tblTemp;
                                // neu
                            } else {
                                $createList[] = new TblScoreRuleSubjectDivisionCourse($tblDivisionCourse, $tblSubject, $tblScoreRule);
                            }
                        }
                    }
                }
            }
        }

        if (!empty($createList)) {
            Grade::useService()->createEntityListBulk($createList);
        }
        if (!empty($updateList)) {
            Grade::useService()->updateEntityListBulk($updateList);
        }
        if (!empty($removeList)) {
            Grade::useService()->deleteEntityListBulk($removeList);
        }

        return new Success("Daten wurde erfolgreich gespeichert.")
            . new Redirect('/Education/Graduation/Grade/ScoreRule', Redirect::TIMEOUT_SUCCESS);
    }
}