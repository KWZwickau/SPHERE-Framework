<?php

namespace SPHERE\Application\Api\Education\Competence;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Competence\SkillRate\SkillRate;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\System\Extension\Extension;

class ApiSkillRate extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param $Method
     *
     * @return string
     * @noinspection PhpMissingParamTypeInspection
     */
    public function exportApi($Method = ''): string
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('saveEditStudentSkillRate');

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
     * @return ModalReceiver
     */
    public static function receiverModal(): ModalReceiver
    {
        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalReceiver');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineClose(): Pipeline
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());

        return $Pipeline;
    }

    /**
     * @param $PersonId
     * @param $YearId
     * @param $SubjectId
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditStudentSkillRate($PersonId, $YearId, $SubjectId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'EditStudentSkillRateContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditStudentSkillRate',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'YearId' => $YearId,
            'SubjectId' => $SubjectId
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     * @param $YearId
     * @param $SubjectId
     * @param null $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function saveEditStudentSkillRate($PersonId, $YearId, $SubjectId, $Data = null): string
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler nicht gefunden.', new Exclamation());
        }
        if (!($tblYear = Term::useService()->getYearById($YearId))) {
            return new Danger('Schuljahr nicht gefunden.', new Exclamation());
        }
        $tblSubject = $SubjectId ? Subject::useService()->getSubjectById($SubjectId) : null;

        return SkillRate::useService()->createStudentSkillRateList($tblPerson, $tblYear, $tblSubject, $Data);
    }
}