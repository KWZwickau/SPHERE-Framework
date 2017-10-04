<?php
/**
 * Created by PhpStorm.
 * User: rackel
 * Date: 02.10.2017
 * Time: 10:59
 */

namespace SPHERE\Application\Document\Standard\StudentTransfer;


use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson as TblToPersonPhone;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class StudentTransfer extends Extension
{

    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Schülerüberweisung'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendSelectPerson'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Form', __CLASS__.'::frontendForm'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @return Stage
     */
    public static function frontendSelectPerson()
    {

        $Stage = new Stage('Schulbescheinigung', 'Schüler auswählen');

        $dataList = array();
        if (($tblGroup = Group::useService()->getGroupByMetaTable('STUDENT'))) {
            if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$dataList) {
                    $Data['PersonId'] = $tblPerson->getId();

                    $tblAddress = $tblPerson->fetchMainAddress();
                    $dataList[] = array(
                        'Name'     => $tblPerson->getLastFirstName(),
                        'Address'  => $tblAddress ? $tblAddress->getGuiString() : '',
                        'Division' => Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson),
                        'Option'   => new Standard('Ausfüllen', __NAMESPACE__.'/Form', null,
                                array('Id' => $tblPerson->getId()))
                            .new External('Herunterladen',
                                'SPHERE\Application\Api\Document\Standard\StudentTransfer\Create',
                                new Download(), array('Data' => $Data),
                                'Schulbescheinigung herunterladen')
                    );
                });
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData(
                                $dataList,
                                null,
                                array(
                                    'Name'     => 'Name',
                                    'Address'  => 'Adresse',
                                    'Division' => 'Klasse',
                                    'Option'   => ''
                                )
                            )
                        )),
                    ))
                )),
            ))
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendForm($Id = null, $Data = null)
    {

        $Stage = new Stage('Form');
        $tblPerson = Person::useService()->getPersonById($Id);
        $Global = $this->getGlobal();
        if ($tblPerson) {
            $Global->POST['Data']['FirstName'] = $tblPerson->getFirstName();
            $Global->POST['Data']['LastName'] = $tblPerson->getLastName();
            $Global->POST['Data']['Date'] = (new \DateTime())->format('d.m.Y');
            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
            if ($tblStudent) {
                $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                    $tblStudentTransferType);
                if ($tblStudentTransfer) {
                    $tblCompanySchool = $tblStudentTransfer->getServiceTblCompany();
                    if ($tblCompanySchool) {
                        $Global->POST['Data']['LeaveSchool'] = $tblCompanySchool->getDisplayName();
                        $tblAddressSchool = Address::useService()->getAddressByCompany($tblCompanySchool);
                        if ($tblAddressSchool) {
                            $Global->POST['Data']['AddressStreet'] = $tblAddressSchool->getStreetName().', '.$tblAddressSchool->getStreetNumber();
                            $tblCitySchool = $tblAddressSchool->getTblCity();
                            if ($tblCitySchool) {
                                $Global->POST['Data']['AddressCity'] = $tblCitySchool->getCode().', '.$tblCitySchool->getName();
                            }
                        }
                        $tblToPersonList = Phone::useService()->getPhoneAllByCompany($tblCompanySchool);
                        $tblToPersonPhoneList = array();
                        $tblToPersonFaxList = array();
                        if ($tblToPersonList) {
                            foreach ($tblToPersonList as $tblToPerson) {
                                if ($tblType = $tblToPerson->getTblType()) {
                                    $TypeName = $tblType->getName();
                                    $TypeDescription = $tblType->getDescription();
                                    if (($TypeName == 'Privat' || $TypeName == 'Geschäftlich') && $TypeDescription == 'Festnetz') {
                                        $tblToPersonPhoneList[] = $tblToPerson;
                                    }
                                    if ($TypeName == 'Fax') {
                                        $tblToPersonFaxList[] = $tblToPerson;
                                    }
                                }
                            }
                            if (!empty($tblToPersonPhoneList)) {
                                /** @var TblToPersonPhone $tblPersonToPhone */
                                $tblPersonToPhone = current($tblToPersonPhoneList);
                                $tblPhone = $tblPersonToPhone->getTblPhone();
                                if ($tblPhone) {
                                    $Global->POST['Data']['Phone'] = $tblPhone->getNumber();
                                }
                            }
                            if (!empty($tblToPersonFaxList)) {
                                /** @var TblToPersonPhone $tblPersonToFax */
                                $tblPersonToFax = current($tblToPersonFaxList);
                                $tblPhoneFax = $tblPersonToFax->getTblPhone();
                                if ($tblPhoneFax) {
                                    $Global->POST['Data']['Fax'] = $tblPhoneFax->getNumber();
                                }
                            }
                        }

                    }
                }
            }
        }
        $Global->savePost();

        $form = $this->formStudentTransfer();
        $form->appendFormButton(new \SPHERE\Common\Frontend\Form\Repository\Button\Standard('PDF Download'));

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Success($tblPerson->getLastFirstName())
                            .$this->downloadStudentTransfer($form, $Data, $Id)
                            , 8),
                        new LayoutColumn(
                            new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/Document/StudentTransfer.png')
                                , ''
                            )
                            , 4),
                    ))
                )
            )
        );

        return $Stage;
    }

    private function formStudentTransfer()
    {
        return new Form(
            new FormGroup(array(
                new FormRow(
                    new FormColumn(
                        new TextField('Data[LeaveSchool]', 'Abgebende Schule', 'Abgebende Schule')
                    )
                ),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[ContactPerson]', 'Ansprechpartner', 'Ansprechpartner')
                        , 3),
                    new FormColumn(
                        new TextField('Data[DocumentNumber]', 'Aktenzeichen', 'Aktenzeichen')
                        , 3),
                    new FormColumn(
                        new TextField('Data[Phone]', 'Telefon', 'Telefon')
                        , 3),
                    new FormColumn(
                        new TextField('Data[Fax]', 'Telefax', 'Telefax')
                        , 3),
                )),
                new FormRow(
                    new FormColumn(
                        new TextField('Data[AddressStreet]', 'Straße, Nummer', 'Straße, Nummer')
                    )
                ),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[AddressCity]', 'PLZ, Ort', 'PLZ, Ort')
                        , 9),
                    new FormColumn(
                        new DatePicker('Data[Date]', 'Datum', 'Datum')
                        , 3),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[FirstName]', 'Vorname', 'Vorname')
                        , 3),
                    new FormColumn(
                        new TextField('Data[LastName]', 'Nachname', 'Nachname')
                        , 3),
                ))
            ))
        );
    }

    public function downloadStudentTransfer(IFrontendInterface $form, $Data, $Id)
    {
        if ($Data != null) {
//            $Redirect = new Redirect('SPHERE\Application\Api\Document\Standard/StudentTransfer/Create', 0, array(
//                'Data' => $Data,
//                'Id' => $Id,
//            ));
            return new Success('PDF wird generiert');
        }
        return $form;
    }
}