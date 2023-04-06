<?php

namespace SPHERE\Application\Api\Education\ClassRegister;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Diary\Diary;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiDiary
 *
 * @package SPHERE\Application\Api\Education\ClassRegister
 */
class ApiDiary extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadDiaryContent');

        $Dispatcher->registerMethod('openCreateDiaryModal');
        $Dispatcher->registerMethod('saveCreateDiaryModal');

        $Dispatcher->registerMethod('openEditDiaryModal');
        $Dispatcher->registerMethod('saveEditDiaryModal');

        $Dispatcher->registerMethod('openDeleteDiaryModal');
        $Dispatcher->registerMethod('saveDeleteDiaryModal');

        $Dispatcher->registerMethod('openSelectStudentModal');
        $Dispatcher->registerMethod('saveSelectStudentModal');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal(): ModalReceiver
    {
        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalReceiver');
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
    public static function pipelineClose(): Pipeline
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineLoadDiaryContent($DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'DiaryContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadDiaryContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateDiaryModal($DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateDiaryModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineCreateDiarySave($DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateDiaryModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DiaryId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditDiaryModal($DiaryId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditDiaryModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DiaryId' => $DiaryId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DiaryId
     *
     * @return Pipeline
     */
    public static function pipelineEditDiarySave($DiaryId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditDiaryModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'DiaryId' => $DiaryId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DiaryId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteDiaryModal($DiaryId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteDiaryModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DiaryId' => $DiaryId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DiaryId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteDiarySave($DiaryId): Pipeline
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteDiaryModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'DiaryId' => $DiaryId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineOpenSelectStudentModal($DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openSelectStudentModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineSelectStudentSave($DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveSelectStudentModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Danger|TableData
     */
    public function loadDiaryContent($DivisionCourseId)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        return Diary::useFrontend()->loadDiaryTable($tblDivisionCourse);
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Danger|string
     */
    public function openCreateDiaryModal($DivisionCourseId)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        return $this->getDiaryModal(Diary::useFrontend()->formDiary($tblDivisionCourse));
    }

    /**
     * @param $form
     * @param null $DiaryId
     *
     * @return string
     */
    private function getDiaryModal($form, $DiaryId = null): string
    {
        if ($DiaryId) {
            $title = new Title(new Edit() . ' Eintrag bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Eintrag hinzufügen');
        }

        return $title
            . new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Well(
                                    $form
                                )
                            )
                        )
                    ))
            );
    }

    /**
     * @param $DivisionCourseId
     * @param $Data
     *
     * @return Danger|string
     */
    public function saveCreateDiaryModal($DivisionCourseId, $Data = null)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        if (($form = Diary::useService()->checkFormDiary($Data, $tblDivisionCourse))) {
            // display Errors on form
            return $this->getDiaryModal($form);
        }

        if (Diary::useService()->createDiary($Data, $tblDivisionCourse)) {
            return new Success('Der Eintrag wurde erfolgreich gespeichert.')
                . self::pipelineLoadDiaryContent($DivisionCourseId)
                . self::pipelineClose();
        } else {
            return new Danger('Der Eintrag konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $DiaryId
     *
     * @return string
     */
    public function openEditDiaryModal($DiaryId)
    {
        if (!($tblDiary = Diary::useService()->getDiaryById($DiaryId))) {
            return new Danger('Der Eintrag wurde nicht gefunden', new Exclamation());
        }
        if (!($tblDivisionCourse = $tblDiary->getServiceTblDivisionCourse())) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        return $this->getDiaryModal(
            Diary::useFrontend()->formDiary($tblDivisionCourse, $DiaryId, true),
            $DiaryId
        );
    }

    /**
     * @param $DiaryId
     * @param $Data
     *
     * @return Danger|string
     */
    public function saveEditDiaryModal($DiaryId, $Data)
    {
        if (!($tblDiary = Diary::useService()->getDiaryById($DiaryId))) {
            return new Danger('Der Eintrag wurde nicht gefunden', new Exclamation());
        }
        if (!($tblDivisionCourse = $tblDiary->getServiceTblDivisionCourse())) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        if (($form = Diary::useService()->checkFormDiary($Data, $tblDivisionCourse, $tblDiary))) {
            // display Errors on form
            return $this->getDiaryModal($form, $DiaryId);
        }

        if (Diary::useService()->updateDiary($tblDiary, $Data)) {
            return new Success('Der Eintrag wurde erfolgreich gespeichert.')
                . self::pipelineLoadDiaryContent($tblDivisionCourse->getId())
                . self::pipelineClose();
        } else {
            return new Danger('Der Eintrag konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $DiaryId
     *
     * @return string
     */
    public function openDeleteDiaryModal($DiaryId)
    {
        if (!($tblDiary = Diary::useService()->getDiaryById($DiaryId))) {
            return new Danger('Der Eintrag wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' Eintrag löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                new Question() . ' Diesen Eintrag wirklich löschen?',
                                array(
                                    $tblDiary->getDate(),
                                    $tblDiary->getSubject(),
                                    $tblDiary->getContent()
                                ),
                                Panel::PANEL_TYPE_DANGER
                            )
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteDiarySave($DiaryId))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                )
            );
    }

    /**
     * @param $DiaryId
     *
     * @return Danger|string
     */
    public function saveDeleteDiaryModal($DiaryId)
    {
        if (!($tblDiary = Diary::useService()->getDiaryById($DiaryId))) {
            return new Danger('Der Eintrag wurde nicht gefunden', new Exclamation());
        }
        if (!($tblDivisionCourse = $tblDiary->getServiceTblDivisionCourse())) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        if (Diary::useService()->destroyDiary($tblDiary)) {
            return new Success('Der Eintrag wurde erfolgreich gelöscht.')
                . self::pipelineLoadDiaryContent($tblDivisionCourse->getId())
                . self::pipelineClose();
        } else {
            return new Danger('Der Eintrag konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Danger|string
     */
    public function openSelectStudentModal($DivisionCourseId)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        $columns = array();
        if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
            foreach ($tblPersonList as $tblPerson) {
                $columns[$tblPerson->getId()] = new FormColumn(new RadioBox('Data[Student]', $tblPerson->getLastFirstName(), $tblPerson->getId()), 4);
            }
        }

        return new Title(new Edit() . ' Schüler auswählen')
            . new Form(array(
                    new FormGroup(array(
                        new FormRow(
                            $columns
                        ),
                        new FormRow(
                            new FormColumn(
                                (new Primary('Auswählen', ApiDiary::getEndpoint(), new Select()))
                                    ->ajaxPipelineOnClick(ApiDiary::pipelineSelectStudentSave($DivisionCourseId))
                            )
                        )
                    ))
            ));
    }

    /**
     * @param $DivisionCourseId
     * @param $Data
     *
     * @return Danger|string
     */
    public function saveSelectStudentModal($DivisionCourseId, $Data = null)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        return new Redirect(
            '/Education/Diary/Selected',
            0,
            array(
                'DivisionCourseId' => $tblDivisionCourse->getId(),
                'BasicRoute' => '/Education/Diary/Teacher',
                'StudentId' => $Data['Student'] ?? null
            )
        ) . self::pipelineClose();
    }
}