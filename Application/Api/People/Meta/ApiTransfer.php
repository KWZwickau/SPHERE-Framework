<?php

namespace SPHERE\Application\Api\People\Meta;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Book;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiTransfer
 *
 * @package SPHERE\Application\Api\People\Meta
 */
class ApiTransfer extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('People', 'Meta', null, null, Consumer::useService()->getConsumerBySession()),
            'SPHERE\Application\People\Meta\Student/Service/Entity',
            'SPHERE\Application\People\Meta\Student\Service\Entity'
        );
    }

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('showModal');
        $Dispatcher->registerMethod('serviceApi');
        $Dispatcher->registerMethod('closeModal');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param $Name
     * @param $Label
     * @param $PersonId
     * @param $StudentTransferTypeIdentifier
     *
     * @return Pipeline
     */
    public static function pipelineOpenModal($Name, $Label, $PersonId, $StudentTransferTypeIdentifier)
    {

        $Pipeline = new Pipeline();

        // get Modal
        $Emitter = new ServerEmitter(self::receiverMassModal(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET                => 'showModal',
            'Name'                          => $Name,
            'Label'                         => $Label,
            'PersonId'                      => $PersonId,
            'StudentTransferTypeIdentifier' => $StudentTransferTypeIdentifier,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param $Name
     * @param $Label
     * @param $PersonId
     * @param $StudentTransferTypeIdentifier
     *
     * @return Pipeline
     */
    public static function pipelineModalService($Name, $Label, $PersonId, $StudentTransferTypeIdentifier)
    {

        $Pipeline = new Pipeline();

        // get Modal
        $Emitter = new ServerEmitter(self::receiverMassModal(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET                => 'serviceApi',
            'Name'                          => $Name,
            'Label'                         => $Label,
            'PersonId'                      => $PersonId,
            'StudentTransferTypeIdentifier' => $StudentTransferTypeIdentifier,
        ));
        $Pipeline->appendEmitter($Emitter);

        // refresh SelectBox
        $Emitter = new ServerEmitter(self::receiverForm(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET                => 'formSchoolSelectBox',
            'Name'                          => $Name,
            'Label'                         => $Label,
            'PersonId'                      => $PersonId,
            'StudentTransferTypeIdentifier' => $StudentTransferTypeIdentifier,
        ));
        $Pipeline->appendEmitter($Emitter);

        // close Modal
        $Pipeline->appendEmitter((new CloseModal(self::receiverMassModal()))->getEmitter());

        return $Pipeline;
    }

    /**
     * @param string $Header
     * @param string $Footer
     *
     * @return ModalReceiver
     */
    public static function receiverMassModal($Header = '', $Footer = '')
    {
        return (new ModalReceiver($Header, $Footer))->setIdentifier('MassModalReceiver');
    }

    /**
     * @param string $Content
     * @param        $Name
     *
     * @return BlockReceiver
     */
    public static function receiverForm($Content = '', $Name = '')
    {
        return (new BlockReceiver($Content))->setIdentifier('FormReceiver'.$Name);
    }

    /**
     * @param string $Name
     * @param string $Label
     * @param null   $PersonId
     * @param string $StudentTransferTypeIdentifier
     *
     * @return Layout|string
     */
    public static function showModal($Name, $Label, $PersonId = null, $StudentTransferTypeIdentifier)
    {

        $SelectBox = self::getFormContent($Name, $Label);
        $SelectBox->ajaxPipelineOnSubmit(self::pipelineModalService($Name, $Label, $PersonId,
            $StudentTransferTypeIdentifier));

        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        $SelectBox
                    ),
                ))
            )
        );
    }

    /**
     * @param string $Name
     * @param string $Label
     * @param null   $PersonId
     * @param string $StudentTransferTypeIdentifier
     * @param null   $Meta
     */
    public static function serviceApi($Name, $Label, $PersonId = null, $StudentTransferTypeIdentifier, $Meta = null)
    {

        $formSelectBox = self::getFormContent($Name, $Label);

        self::useService()->createTransfer($formSelectBox, $Meta, $PersonId, $StudentTransferTypeIdentifier);

    }

    /**
     * @return CloseModal
     */
    public static function closeModal()
    {
        return new CloseModal(self::receiverMassModal());
    }

    /**
     * @param string $Name
     * @param string $Label
     * @param null   $PersonId
     * @param string $StudentTransferTypeIdentifier
     *
     * @return SelectBox|TextField
     */
    public static function formSchoolSelectBox(
        $Name = '',
        $Label = '',
        $PersonId = null,
        $StudentTransferTypeIdentifier = ''
    ) {

        $Button = (new Standard('', ApiTransfer::getEndpoint(), new Book())
            )->ajaxPipelineOnClick(ApiTransfer::pipelineOpenModal($Name, $Label, $PersonId,
                $StudentTransferTypeIdentifier)).' ';

        if ($Label == 'Aktuelle Schule') {
            $list = Group::useService()->getCompanyAllByGroup(
                Group::useService()->getGroupByMetaTable('SCHOOL')
            );
            $Field = new SelectBox($Name, $Button.' '.$Label, array('{{ Name }} {{ Description }}' => $list),
                new Education());
        } elseif ($Label == 'Aktuelle Schulart') {
            $list = Type::useService()->getTypeAll();
            $Field = new SelectBox($Name, $Button.' '.$Label, array('{{ Name }} {{ Description }}' => $list),
                new Education());
        } elseif ($Label == 'Aktueller Bildungsgang') {
            $list = Type::useService()->getTypeAll();
            $Field = new SelectBox($Name, $Button.' '.$Label, array('{{ Name }} {{ Description }}' => $list),
                new Education());
        } else {
            $Field = new TextField($Name, '', $Button.' '.$Label);
        }

        return $Field;
    }

    /**
     * @param string $Name
     * @param string $Label
     *
     * @return Form
     */
    public static function getFormContent($Name = '', $Label = '')
    {

        if ($Label == 'Aktuelle Schule') {
            $list = Group::useService()->getCompanyAllByGroup(
                Group::useService()->getGroupByMetaTable('SCHOOL')
            );
            $Field = new SelectBox($Name, $Label, array('{{ Name }} {{ Description }}' => $list), new Education());
        } elseif ($Label == 'Aktuelle Schulart') {
            $list = Type::useService()->getTypeAll();
            $Field = new SelectBox($Name, $Label, array('{{ Name }} {{ Description }}' => $list), new Education());
        } elseif ($Label == 'Aktueller Bildungsgang') {
            $list = Type::useService()->getTypeAll();
            $Field = new SelectBox($Name, $Label, array('{{ Name }} {{ Description }}' => $list), new Education());
        } else {
            $Field = new TextField($Name, '', $Label);
        }

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        $Field
                    ),
                    new FormColumn(
                        new Primary('Speichern', new Save())
                    ),
                ))
            )
        );
    }
}