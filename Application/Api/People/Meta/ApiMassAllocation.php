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
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
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
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenModal($Name, $Label, $PersonId)
    {

        $Pipeline = new Pipeline();

        // get Modal
        $Emitter = new ServerEmitter(self::receiverMassModal(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'ShowModal',
            'Name'           => $Name,
            'Label'          => $Label,
            'PersonId'       => $PersonId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param $Name
     * @param $Label
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineModalService($Name, $Label, $PersonId)
    {

        $Pipeline = new Pipeline();

        // get Modal
        $Emitter = new ServerEmitter(self::receiverMassModal(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'serviceApi',
            'Name'           => $Name,
            'Label'          => $Label,
            'PersonId'       => $PersonId
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
     * @param string $Content
     * @param        $Name
     *
     * @return BlockReceiver
     */
    public static function receiverForm($Content = '', $Name)   // ToDO später erneurn
    {
        return (new BlockReceiver($Content))->setIdentifier('FormReceiver'.$Name);
    }

    /**
     * @param      $Name
     * @param      $Label
     * @param null $PersonId
     *
     * @return Layout|string
     */
    public static function ShowModal($Name, $Label, $PersonId = null)
    {

        $SelectBox = self::getFormContent($Name, $Label);
        $SelectBox->ajaxPipelineOnSubmit(self::pipelineModalService($Name, $Label, $PersonId));

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
     * @param null   $Meta
     * @param null   $Group
     */
    public static function serviceApi($Name = '', $Label = '', $PersonId = null, $Meta = null, $Group = null)
    {

//        $SelectBox = self::getFormContent($Name, $Label, $PersonId);

//        $tblPerson = Person::useService()->getPersonById($PersonId);
//        if($tblPerson){
//            $Form = ( new Form(array(
//                new FormGroup(
//                    new FormRow(array(
//                        new FormColumn(
//                            new Panel('Identifikation', array(
//                                new TextField('Meta[Student][Identifier]', 'Schülernummer',
//                                    'Schülernummer')
//                            ), Panel::PANEL_TYPE_INFO)
//                            , 4),
//                        new FormColumn(
//                            new Panel('Schulpflicht', array(
//                                new DatePicker('Meta[Student][SchoolAttendanceStartDate]', '', 'Beginnt am', new Calendar())
//                            ), Panel::PANEL_TYPE_INFO)
//                            , 4),
//                    ))),
//                Student::useFrontend()->formGroupTransfer($tblPerson),
//                Student::useFrontend()->formGroupGeneral($tblPerson),
//                Student::useFrontend()->formGroupSubject($tblPerson),
//                Student::useFrontend()->formGroupIntegration($tblPerson),
//            ), new Primary('Speichern', new Save())));
//            Student::useService()->createMeta($Form, $tblPerson, $Meta, $Group);
//        } else {
////            $Form = null;
//        }
//
////        Student::useService()->createMeta($Form, $tblPerson, $Meta, $Group);
    }

    /**
     * @param string $Name
     * @param string $Label
     * @param null   $PersonId
     *
     * @return SelectBox
     */
    public static function formSchoolSelectBox($Name = '', $Label = '', $PersonId = null)
    {

        $list = array();
        if ($Label == 'Aktuelle Schule') {
            $list = Group::useService()->getCompanyAllByGroup(
                Group::useService()->getGroupByMetaTable('SCHOOL')
            );
        }
        $Button = (new Standard('', ApiMassAllocation::getEndpoint(), new Book())
            )->ajaxPipelineOnClick(ApiMassAllocation::pipelineOpenModal($Name, $Label, $PersonId)).' ';

        return new SelectBox($Name, $Button.$Label, array('{{ Name }} {{ Description }}' => $list), new Education());
    }

    /**
     * @param string $Name
     * @param string $Label
     *
     * @return Form
     */
    public static function getFormContent($Name = '', $Label = '')
    {

        $list = array();
        if ($Label == 'Aktuelle Schule') {
            $list = Group::useService()->getCompanyAllByGroup(
                Group::useService()->getGroupByMetaTable('SCHOOL')
            );
        }
        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new SelectBox($Name, $Label, array('{{ Name }} {{ Description }}' => $list), new Education())
                    ),
                    new FormColumn(
                        new Primary('Speichern', new Save())
                    ),
                ))
            )
        );
    }
}