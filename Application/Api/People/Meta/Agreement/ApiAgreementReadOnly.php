<?php
namespace SPHERE\Application\Api\People\Meta\Agreement;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementCategory;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Form\Repository\Aspect;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Extension\Extension;

class ApiAgreementReadOnly extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('openOverViewModal');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverOverViewModal()
    {

        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalAgreementOverViewReciever');
    }


    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenOverViewModal($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverOverViewModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiAgreementReadOnly::API_TARGET => 'openOverViewModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function openOverViewModal($PersonId)
    {

        $tblPerson = Person::useService()->getPersonById($PersonId);
        if(!$tblPerson){
            $HeadPanel = new Warning('Person wurde nicht gefunden');
            $WellAgreement = '';
        } else {
            $HeadPanel = new Panel('Schüler', $tblPerson->getLastFirstName(), Panel::PANEL_TYPE_INFO);
            $AgreementNameList = array();
            $AgreementPictureList = array();
            $Global = $this->getGlobal();
            if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                if(($tblStudentAgreementList = Student::useService()->getStudentAgreementAllByStudent($tblStudent))){
                    foreach($tblStudentAgreementList as $tblStudentAgreement){
                        if(($tblStudentAgreementType = $tblStudentAgreement->getTblStudentAgreementType())){
                            $Global->POST['Meta']['Agreement']
                            [$tblStudentAgreement->getTblStudentAgreementType()->getTblStudentAgreementCategory()->getId()]
                            [$tblStudentAgreement->getTblStudentAgreementType()->getId()] = 1;
                        }
                    }
                }
                $Global->savePost();

                $tblAgreementCategoryAll = Student::useService()->getStudentAgreementCategoryAll();
                array_walk($tblAgreementCategoryAll,
                    function (TblStudentAgreementCategory $tblStudentAgreementCategory) use (&$AgreementNameList, &$AgreementPictureList) {

                        if($tblStudentAgreementCategory->getName() == 'Namentliche Erwähnung des Schülers'){
                            $AgreementNameList[] = new Aspect(new Bold($tblStudentAgreementCategory->getName()));
                            $tblAgreementTypeAll = Student::useService()->getStudentAgreementTypeAllByCategory($tblStudentAgreementCategory);
                            if ($tblAgreementTypeAll) {
                                $tblAgreementTypeAll = $this->getSorter($tblAgreementTypeAll)->sortObjectBy('Name');
                            }
                            array_walk($tblAgreementTypeAll,
                                function (TblStudentAgreementType $tblStudentAgreementType) use (
                                    &$AgreementNameList,
                                    $tblStudentAgreementCategory
                                ) {

                                    $Row = new Layout(new LayoutGroup(new LayoutRow(array(
                                        new LayoutColumn((new CheckBox('Meta[Agreement]['.$tblStudentAgreementCategory->getId().']['.$tblStudentAgreementType->getId().']',
                                            ' ', 1))->setDisabled()
                                            , 1),
                                        new LayoutColumn($tblStudentAgreementType->getName()
                                            , 11),
                                    ))));
                                    array_push($AgreementNameList, $Row);
                                }
                            );
                        } else {
                            $AgreementPictureList[] = new Aspect(new Bold($tblStudentAgreementCategory->getName()));
                            $tblAgreementTypeAll = Student::useService()->getStudentAgreementTypeAllByCategory($tblStudentAgreementCategory);
                            if ($tblAgreementTypeAll) {
                                $tblAgreementTypeAll = $this->getSorter($tblAgreementTypeAll)->sortObjectBy('Name');
                            }
                            array_walk($tblAgreementTypeAll,
                                function (TblStudentAgreementType $tblStudentAgreementType) use (
                                    &$AgreementPictureList,
                                    $tblStudentAgreementCategory
                                ) {
                                    $Row = new Layout(new LayoutGroup(new LayoutRow(array(
                                        new LayoutColumn((new CheckBox('Meta[Agreement]['.$tblStudentAgreementCategory->getId().']['.$tblStudentAgreementType->getId().']',
                                                ' ', 1))->setDisabled()
                                            , 1),
                                        new LayoutColumn($tblStudentAgreementType->getName()
                                            , 11),
                                    ))));
                                    array_push($AgreementPictureList, $Row);
                                }
                            );
                        }
                    }
                );
            }

            $Agreement = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(new Listing($AgreementNameList), 6),
                new LayoutColumn(new Listing($AgreementPictureList), 6),
            ))));
            $WellAgreement = new Well($Agreement);
        }


        return new Title('Einverständniserklärung zur Datennutzung')
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            $HeadPanel
                        , 12),
                        new LayoutColumn(
                            $WellAgreement
                        ),
                    ))
                )
            );
    }
}