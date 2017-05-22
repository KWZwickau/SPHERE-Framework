<?php

namespace SPHERE\Application\Api\People\Meta;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Icon\Repository\Book;
use SPHERE\Common\Frontend\Icon\Repository\Education;
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

    const API_DISPATCHER = 'MethodName';

    /**
     * @param string $MethodName Callable Method
     *
     * @return string
     */
    public function exportApi($MethodName = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('pipelineModal');

        return $Dispatcher->callMethod($MethodName);
    }

    /**
     * @param string $Content
     *
     * @return ModalReceiver
     */
    public static function receiverMassModal($Content = '')
    {
        return (new ModalReceiver($Content))->setIdentifier('MassModalReceiver');
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
     * @param array  $list
     *
     * @return SelectBox
     */
    private static function formSchool($Name = '', $Label = '', $list = array())
    {
        $Label = (new Standard('', '', new Book()))->ajaxPipelineOnClick(ApiMassAllocation::pipelineModal()).' '.$Label;

        return new SelectBox($Name, $Label, array('{{ Name }} {{ Description }}' => $list), new Education());
    }


    public static function getFormContent($Name = '', $Label = '', $PersonId = null)
    {

        $list = array();
        if ($Label == 'Aktuelle Schule') {
            $list = Group::useService()->getCompanyAllByGroup(
                Group::useService()->getGroupByMetaTable('SCHOOL')
            );
        }
        $tblPerson = ($PersonId != null ? Person::useService()->getPersonById($PersonId) : false);

        if ($tblPerson) {

        }
        return self::formSchool($Name, $Label, $list);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineModal()
    {

        $Pipeline = new Pipeline();

//        // execute Service
//        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
//        $Emitter->setPostPayload(array(
//            self::API_TARGET => 'serviceRemoveSubject',
//            'Id'             => $Id,
//            'DivisionId'     => $DivisionId
//        ));
//        $Pipeline->appendEmitter($Emitter);

        // get Modal
        $Emitter = new ServerEmitter(self::receiverMassModal(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'EmptyModal'
        ));
        $Pipeline->appendEmitter($Emitter);
//
//        // refresh Table
//        $Emitter = new ServerEmitter(self::receiverAvailable(), self::getEndpoint());
//        $Emitter->setPostPayload(array(
//            self::API_TARGET => 'tableAvailableSubject',
//            'DivisionId'     => $DivisionId
//        ));
//        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    public function EmptyModal()
    {
        return 'Test';
    }
}