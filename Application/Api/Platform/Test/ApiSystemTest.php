<?php

namespace SPHERE\Application\Api\Platform\Test;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger as DangerMessage;
use SPHERE\Common\Frontend\Message\Repository\Info as InfoMessage;
use SPHERE\Common\Frontend\Message\Repository\Success as SuccessMessage;
use SPHERE\Common\Frontend\Text\Repository\Code;
use SPHERE\System\Extension\Extension;

/**
 * Class SerialLetter
 * @package SPHERE\Application\Api\Platform\Test
 */
class ApiSystemTest extends Extension implements IApiInterface
{

    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        // function's have to exist!
        $Dispatcher = new Dispatcher(__CLASS__);
        //////////////////////////////////////// first Modal
        $Dispatcher->registerMethod('openFirstModal');
        //////////////////////////////////////// second Modal
        $Dispatcher->registerMethod('openSecondModal');
        $Dispatcher->registerMethod('saveSecondModal');
        //////////////////////////////////////// third Modal
        $Dispatcher->registerMethod('openThirdModal');
        $Dispatcher->registerMethod('saveThirdModal');
        $Dispatcher->registerMethod('openThirdResult');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */ // place receiver into Frontend before using it
    public static function receiverFirstModal()
    {

        return (new ModalReceiver('Überschrift (Header)', '(Footer) '.new Close()))
            ->setIdentifier('FirstModal');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineOpenFirstModal()
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverFirstModal(), self::getEndpoint());
        // API-Target -> choose function
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'openFirstModal'
        ));
        $Pipeline->setLoadingMessage('Ladebalken', 'Anzeige des Modulverhaltens');
        // queue Emitter to Pipeline
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    public function openFirstModal()
    {

        return new Layout(
            new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Title('Beliebiger Inhalt für ein Modal')
                    ),
                    new LayoutColumn(
                        new Panel('Head', '"Default"', Panel::PANEL_TYPE_DEFAULT)
                        , 2),
                    new LayoutColumn(
                        new Panel('Head', '"Success"', Panel::PANEL_TYPE_SUCCESS)
                        , 2),
                    new LayoutColumn(
                        new Panel('Head', '"Info"', Panel::PANEL_TYPE_INFO)
                        , 2),
                    new LayoutColumn(
                        new Panel('Head', '"Warning"', Panel::PANEL_TYPE_WARNING)
                        , 2),
                    new LayoutColumn(
                        new Panel('Head', '"Danger"', Panel::PANEL_TYPE_DANGER)
                        , 2),
                    new LayoutColumn(
                        new Panel('Head', '"Primary"', Panel::PANEL_TYPE_PRIMARY)
                        , 2),
                )),
                new LayoutRow(
                    new LayoutColumn(
                        new SuccessMessage('Modal geöffnet')
                    )
                )
            ))
        );
    }

    //////////////////////////////////////// second Modal

    /**
     * @return ModalReceiver
     */ // place receiver into Frontend before using it
    public static function receiverSecondModal()
    {

        return (new ModalReceiver('Mit Form', new Close()))
            ->setIdentifier('SecondModal');
    }

    /**
     * @return BlockReceiver
     */ // place receiver into Frontend before using it
    public static function receiverSecondService()
    {

        return (new BlockReceiver(''))
            ->setIdentifier('SecondServiceEntity');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineOpenSecondModal()
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverSecondModal(), self::getEndpoint());
        // API-Target -> choose function
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'openSecondModal'
        ));
        // extra information
        $Emitter->setPostPayload(array(
            'IsInformation' => true
        ));
        $Pipeline->setLoadingMessage('Ladebalken', 'Anzeige des Modulverhaltens');
        // queue Emitter to Pipeline
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @var array $result
     *
     * @return Pipeline
     */
    public static function pipelineSave()
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverSecondModal(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveSecondModal'
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    public static function pipelineClose()
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(self::receiverSecondModal()))->getEmitter());
        return $Pipeline;
    }

    public function openSecondModal()
    {
        // !important -> disableSubmitAction() "no enter on keyborad"
        $form = (new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(new TextField('Field[ArrayKey]', '', 'Formbeispiel')),
                    // Form/Primary doesn't work (to long URL's) use Link/Primary
                    new FormColumn((new Primary('Speichern', self::getEndpoint(), new Save()))->ajaxPipelineOnClick(
                        self::pipelineSave()
                    ))
                ))
            )
        ))->disableSubmitAction();


        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Title('Eingabemaske')
                    ),
                    new LayoutColumn(
                        new Well($form)
                    ),
                ))
            )
        );
    }

    public function saveSecondModal($Field)
    {

        /** Service */
        //ToDO insert service

        /** show to User */
        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Code(print_r($Field, true))
                    ),
                    // ToDo insert pipelineClose to close modal automatic
//                    new LayoutColumn(
//                        self::pipelineClose()
//                    )
                ))
            )
        );
    }

    //////////////////////////////////////// third Modal

    /**
     * @return ModalReceiver
     */
    public static function receiverThirdModal()
    {

        return (new ModalReceiver())
            ->setIdentifier('ThirdModal');
    }

    /**
     * @return InlineReceiver
     */
    public static function receiverThirdService()
    {

        return (new InlineReceiver())
            ->setIdentifier('ThirdModalService');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineOpenThirdModal()
    {
        $Pipeline = new Pipeline();
        // open modal
        $Emitter = new ServerEmitter(self::receiverThirdModal(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'openThirdModal'
        ));
        $Pipeline->appendEmitter($Emitter);

        // start service
        $Emitter = new ServerEmitter(self::receiverThirdService(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveThirdModal'
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    public function saveThirdModal() // get Information from Post
    {

        /** ToDo Service */

        sleep(2);   //ToDO delete (only to show Loading screen)
        return self::pipelineServiceThirdModal();
    }

    /**
     * @var array $result
     *
     * @return Pipeline
     */
    public static function pipelineServiceThirdModal()
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverThirdModal(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'openThirdResult'
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    public function openThirdModal()
    {

        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new InfoMessage('Dieser Vorgang kann einige Zeit in Anspruch nehmen'
                            .new Container((new ProgressBar(0, 100, 0, 10))
                                ->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_SUCCESS))
                        )
                    ),
                    new LayoutColumn(self::receiverThirdService())
                ))
            )
        );
    }

    public function openThirdResult()
    {

        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new SuccessMessage('Der Service ist erfolgreich gewesen')
                    ),
                    new LayoutColumn(
                        'oder:'
                    ),
                    new LayoutColumn(
                        new DangerMessage('Der Service konnte nicht ausgeführt werden')
                    ),
                ))
            )
        );
    }

}