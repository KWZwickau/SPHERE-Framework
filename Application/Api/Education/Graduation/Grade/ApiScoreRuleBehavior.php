<?php

namespace SPHERE\Application\Api\Education\Graduation\Grade;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRuleBehaviorSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
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

class ApiScoreRuleBehavior extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('saveScoreRuleEdit');

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
     * @return Pipeline
     */
    public static function pipelineLoadScoreRuleSubjects(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ScoreRuleSubjectsContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadScoreRuleSubjects',
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Data
     *
     * @return string
     */
    public function loadScoreRuleSubjects($Data = null): string
    {
        return Grade::useFrontend()->loadScoreRuleBehaviorSubjects($Data);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineSaveScoreRuleEdit(): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ScoreRuleSubjectsContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveScoreRuleEdit'
        ));
        $ModalEmitter->setLoadingMessage("Wird bearbeitet");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $Data
     *
     * @return string
     */
    public function saveScoreRuleEdit($Data): string
    {
        if (!($tblSchoolType = Type::useService()->getTypeById($Data['SchoolType']))) {
            return (new Danger("Schulart wurde nicht gefunden!", new Exclamation()));
        }

        $createList = array();
        $updateList = array();
        $removeList = array();

        if (isset($Data['Subjects'])) {
            foreach ($Data['Subjects'] as $level => $subjectList) {
                foreach ($subjectList as $subjectId => $value) {
                    $tblSubject = Subject::useService()->getSubjectById($subjectId);
                    $tblScoreRuleBehaviourSubject = Grade::useService()->getScoreRuleBehaviorSubjectBySchoolTypeAndLevelAndSubject($tblSchoolType, $level, $tblSubject ?: null);
                    if ($value !== '' && $value != '1') {
                        if ($tblScoreRuleBehaviourSubject) {
                            $tblScoreRuleBehaviourSubject->setMultiplier($value);
                            $updateList[] = $tblScoreRuleBehaviourSubject;
                        } else {
                            $createList[] = new TblScoreRuleBehaviorSubject($tblSchoolType, $level, $tblSubject ?: null, $value);
                        }
                    } else {
                        // vorhandenen Eintrag löschen
                        if ($tblScoreRuleBehaviourSubject) {
                            $removeList[] = $tblScoreRuleBehaviourSubject;
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
            . new Redirect('/Education/Graduation/Grade/BehaviorScoreRule', Redirect::TIMEOUT_SUCCESS);
    }
}