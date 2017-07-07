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
        $Dispatcher->registerMethod('openModal');
        $Dispatcher->registerMethod('saveModal');
        //////////////////////////////////////// second Modal
        $Dispatcher->registerMethod('openSecondModal');
        $Dispatcher->registerMethod('saveSecondModal');
        $Dispatcher->registerMethod('openSecondResult');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */ // place receiver into Frontend before using it
    public static function receiverModal()
    {

        return (new ModalReceiver('Überschrift (Header)', '(Footer) '.new Close()))
            ->setIdentifier('ModalIdentifier');
    }

    /**
     * @return BlockReceiver
     */ // place receiver into Frontend before using it
    public static function receiverAccountService()
    {

        return (new BlockReceiver(''))
            ->setIdentifier('ServiceEntity');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineOpenModal()
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        // API-Target -> choose function
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'openModal'
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
        $Emitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveModal'
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    public static function pipelineClose()
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());
        return $Pipeline;
    }

    public function openModal()
    {
        // !important -> disableSubmitAction() "no enter on keyborad"
        $form = (new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(new TextField('Field[ArrayKey]', '', 'Formbeispiel')),
                    // PrimaryFormButton doesn't work (to long URL's)
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
                        new Title('Eingabemaskte')
                    ),
                    new LayoutColumn(
                        new Well($form)
                    ),
                ))
            )
        );
    }

    public function saveModal($Field)
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

    //////////////////////////////////////// second Modal

    /**
     * @return ModalReceiver
     */
    public static function receiverSecondModal()
    {

        return (new ModalReceiver())
            ->setIdentifier('secondModal');
    }

    /**
     * @return InlineReceiver
     */
    public static function receiverSecondService()
    {

        return (new InlineReceiver())
            ->setIdentifier('secondModalService');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineOpenSecondModal()
    {
        $Pipeline = new Pipeline();
        // open modal
        $Emitter = new ServerEmitter(self::receiverSecondModal(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'openSecondModal'
        ));
        $Pipeline->appendEmitter($Emitter);

        // start service
        $Emitter = new ServerEmitter(self::receiverSecondService(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveSecondModal'
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    public function saveSecondModal() // get Information from Post
    {

        /** ToDo Service */
        sleep(2);   //ToDO delete (only to show Loading screen)
        return self::pipelineServiceSecondModal();
    }

    /**
     * @var array $result
     *
     * @return Pipeline
     */
    public static function pipelineServiceSecondModal()
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverSecondModal(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'openSecondResult'
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    public function openSecondModal()
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
                    new LayoutColumn(self::receiverSecondService())
                ))
            )
        );
    }

    public function openSecondResult()
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