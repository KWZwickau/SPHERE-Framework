<?php

namespace SPHERE\Application\Api\Education\Competence;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Competence\Skill\Skill;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Extension\Extension;

class ApiSkill extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadSkillGridTable');
        $Dispatcher->registerMethod('loadSkillAreaContent');
        $Dispatcher->registerMethod('loadSkillContent');

        $Dispatcher->registerMethod('loadEditSkillGridContent');
        $Dispatcher->registerMethod('saveEditSkillGrid');

        $Dispatcher->registerMethod('openDeleteSkillGridModal');
        $Dispatcher->registerMethod('saveDeleteSkillGridModal');

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
     * @param null $SchoolTypeId
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineLoadSkillGridTable($SchoolTypeId = null, $Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SkillGridTable'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadSkillGridTable',
        ));
        $ModalEmitter->setPostPayload(array(
            'SchoolTypeId' => $SchoolTypeId,
            'Filter' => $Filter,
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $SchoolTypeId
     * @param null $Filter
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadSkillGridTable($SchoolTypeId = null, $Filter = null): string
    {
        return Skill::useFrontend()->loadSkillGridTable($SchoolTypeId, $Filter);
    }

    /**
     * @param $SchoolTypeId
     * @param $Filter
     * @param $SkillGridId
     * @param $AreaRanking
     *
     * @return Pipeline
     */
    public static function pipelineLoadSkillAreaContent($SchoolTypeId, $Filter, $SkillGridId, $AreaRanking): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SkillAreaContent_' . $AreaRanking), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadSkillAreaContent',
        ));

        $ModalEmitter->setPostPayload(array(
            'SchoolTypeId' => $SchoolTypeId,
            'Filter' => $Filter,
            'SkillGridId' => $SkillGridId,
            'AreaRanking' => $AreaRanking,
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $SchoolTypeId
     * @param $Filter
     * @param $SkillGridId
     * @param $AreaRanking
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadSkillAreaContent($SchoolTypeId, $Filter, $SkillGridId, $AreaRanking): string
    {
        return Skill::useFrontend()->getSkillAreaContent($SchoolTypeId, $Filter, $SkillGridId, $AreaRanking);
    }

    /**
     * @param $SchoolTypeId
     * @param $Filter
     * @param $SkillGridId
     * @param $AreaRanking
     * @param $SkillRanking
     *
     * @return Pipeline
     */
    public static function pipelineLoadSkillContent($SchoolTypeId, $Filter, $SkillGridId, $AreaRanking, $SkillRanking): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SkillContent_' . $AreaRanking . '_' . $SkillRanking), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadSkillContent',
        ));

        $ModalEmitter->setPostPayload(array(
            'SchoolTypeId' => $SchoolTypeId,
            'Filter' => $Filter,
            'SkillGridId' => $SkillGridId,
            'AreaRanking' => $AreaRanking,
            'SkillRanking' => $SkillRanking,
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $SchoolTypeId
     * @param $Filter
     * @param $SkillGridId
     * @param $AreaRanking
     * @param $SkillRanking
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadSkillContent($SchoolTypeId, $Filter, $SkillGridId, $AreaRanking, $SkillRanking): string
    {
        return Skill::useFrontend()->getSkillContent($SchoolTypeId, $Filter, $SkillGridId,$AreaRanking, $SkillRanking);
    }

    /**
     * @param null $SchoolTypeId
     * @param null $Filter
     * @param null $SkillGridId
     * @param null $Action
     * @param null $ActionId
     *
     * @return Pipeline
     */
    public static function pipelineLoadEditSkillGridContent($SchoolTypeId = null, $Filter = null, $SkillGridId = null, $Action = null, $ActionId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'EditSkillGridContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadEditSkillGridContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'SchoolTypeId' => $SchoolTypeId,
            'Filter' => $Filter,
            'SkillGridId' => $SkillGridId,
            'Action' => $Action,
            'ActionId' => $ActionId
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $SchoolTypeId
     * @param null $Filter
     * @param null $SkillGridId
     * @param null $Action
     * @param null $ActionId
     * @param null $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadEditSkillGridContent($SchoolTypeId = null, $Filter = null, $SkillGridId = null, $Action = null, $ActionId = null, $Data = null): string
    {
        return Skill::useFrontend()->loadEditSkillGridContent(false, $SchoolTypeId, $Filter, $SkillGridId, $Action, $ActionId, $Data);
    }

    /**
     * @param $SchoolTypeId
     * @param $Filter
     * @param $SkillGridId
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditSkillGrid($SchoolTypeId = null, $Filter = null, $SkillGridId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'EditSkillGridContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditSkillGrid',
        ));
        $ModalEmitter->setPostPayload(array(
            'SchoolTypeId' => $SchoolTypeId,
            'Filter' => $Filter,
            'SkillGridId' => $SkillGridId
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $SchoolTypeId
     * @param null $Filter
     * @param null $SkillGridId
     * @param null $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function saveEditSkillGrid($SchoolTypeId = null, $Filter = null, $SkillGridId = null, $Data = null): string
    {
        if (!($tblSchoolType = Type::useService()->getTypeById($SchoolTypeId))) {
            return new Danger('Schulart wurde nicht gefunden!', new Exclamation());
        }

        $tblSkillGrid = null;
        if ($SkillGridId) {
            $tblSkillGrid = Skill::useService()->getSkillGridById($SkillGridId);
        }

        return Skill::useService()->updateSkillGrid($tblSchoolType, $Filter, $Data, $tblSkillGrid);
    }

    /**
     * @param $SkillGridId
     * @param $SchoolTypeId
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteSkillGridModal($SkillGridId, $SchoolTypeId, $Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteSkillGridModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'SkillGridId' => $SkillGridId,
            'SchoolTypeId' => $SchoolTypeId,
            'Filter' => $Filter
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $SkillGridId
     * @param $SchoolTypeId
     * @param null $Filter
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function openDeleteSkillGridModal($SkillGridId, $SchoolTypeId, $Filter = null): string
    {
        if (!($tblSkillGrid = Skill::useService()->getSkillGridById($SkillGridId))) {
            return new Danger('Das Kompetenzraster wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' Kompetenzraster löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                new Question() . ' Dieses Kompetenzraster wirklich löschen?',
                                array(
                                    'Name: ' . new Bold($tblSkillGrid->getName()),
                                    'Klassenstufe: ' . new Bold($tblSkillGrid->getLevel()),
                                    'Fach: ' . new Bold(($tblSubject = $tblSkillGrid->getServiceTblSubject()) ? $tblSubject->getDisplayName() : 'Fächerübergreifende'),
                                    count($tblSkillGrid->getSkillAreas()) . ' Kompetenzbereiche',
                                    count($tblSkillGrid->getSkills()) . ' Kompetenzen',
                                ),
                                Panel::PANEL_TYPE_DANGER
                            )
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteSkillGridSave($SkillGridId, $SchoolTypeId, $Filter))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                )
            );
    }

    /**
     * @param $SkillGridId
     * @param $SchoolTypeId
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineDeleteSkillGridSave($SkillGridId, $SchoolTypeId, $Filter = null): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteSkillGridModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'SkillGridId' => $SkillGridId,
            'SchoolTypeId' => $SchoolTypeId,
            'Filter' => $Filter
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $SkillGridId
     * @param $SchoolTypeId
     * @param null $Filter
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function saveDeleteSkillGridModal($SkillGridId, $SchoolTypeId, $Filter = null): string
    {
        if (!($tblSkillGrid = Skill::useService()->getSkillGridById($SkillGridId))) {
            return new Danger('Der Kompetenzraster wurde nicht gefunden', new Exclamation());
        }

        if (Skill::useService()->destroySkillGrid($tblSkillGrid)) {
            return new Success('Der Kompetenzraster wurde erfolgreich gelöscht.')
                . self::pipelineLoadSkillGridTable($SchoolTypeId, $Filter)
                . self::pipelineClose();
        } else {
            return new Danger('Der Kompetenzraster konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }
}