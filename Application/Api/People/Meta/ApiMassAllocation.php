<?php

namespace SPHERE\Application\Api\People\Meta;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Book;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiPerson
 *
 * @package SPHERE\Application\Api\People\Meta
 */
class ApiMassAllocation extends Extension implements IApiInterface
{
    use ApiTrait;


    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('ShowModal');
        $Dispatcher->registerMethod('serviceApi');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param $Name
     * @param $Label
     *
     * @return Pipeline
     */
    public static function pipelineModal($Name, $Label)
    {

        $Pipeline = new Pipeline();

        // get Modal
        $Emitter = new ServerEmitter(self::receiverMassModal(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'ShowModal',
            'Name'           => $Name,
            'Label'          => $Label
        ));
        $Pipeline->appendEmitter($Emitter);

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
     * @param $Name
     * @param $Label
     *
     * @return Layout
     */
    public static function ShowModal($Name, $Label)
    {

        $SelectBox = self::getFormContent($Name, $Label);
        $form = new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        $SelectBox
                    ),
                    new FormColumn(
//                        (new Primary('', '#', new Save()))->ajaxPipelineOnClick()
                        new Panel('Button Bereich', '')
                    )
                ))
            )
        );

        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        $form
                    )
                ))
            )
        );
    }

    public static function serviceApi($FieldName = null)
    {

//        $tblComany = Company::useService()->getCompanyById($Id);
//        if($tblComany){
//
//        }

    }

    /**
     * @param string $Content
     * @param        $Name
     *
     * @return BlockReceiver
     */
    public static function receiverForm($Content = '', $Name)
    {
        return (new BlockReceiver($Content))->setIdentifier('FormReceiver'.$Name);
    }

    /**
     * @param string $Name
     * @param string $Label
     * @param array  $List
     * @param bool   $showButton
     *
     * @return SelectBox
     */
    private static function formSchool($Name = '', $Label = '', $List = array(), $showButton = false)
    {
        if ($showButton) {
            $Button = (new Standard('', ApiMassAllocation::getEndpoint(), new Book())
                )->ajaxPipelineOnClick(ApiMassAllocation::pipelineModal($Name, $Label)).' ';
        } else {
            $Button = '';
        }

        return new SelectBox($Name, $Button.$Label, array('{{ Name }} {{ Description }}' => $List), new Education());
    }

    /**
     * @param string $Name
     * @param string $Label
     * @param null   $PersonId
     * @param bool   $showButton
     *
     * @return SelectBox
     */
    public static function getFormContent($Name = '', $Label = '', $PersonId = null, $showButton = false)
    {

        $list = array();
        if ($Label == 'Aktuelle Schule') {
            $list = Group::useService()->getCompanyAllByGroup(
                Group::useService()->getGroupByMetaTable('SCHOOL')
            );
        }
//        $tblPerson = ($PersonId != null ? Person::useService()->getPersonById($PersonId) : false);
//
//        if ($tblPerson) {
//
//        }
        return self::formSchool($Name, $Label, $list, $showButton);
    }
}