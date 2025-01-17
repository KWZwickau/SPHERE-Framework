<?php
namespace SPHERE\Application\Setting\Consumer\School;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as ConsumerGatekeeper;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumerLogin;
use SPHERE\Application\Setting\Consumer\Responsibility\Responsibility;
use SPHERE\Application\Setting\Consumer\Responsibility\Service\Entity\TblResponsibility;
use SPHERE\Application\Setting\Consumer\School\Service\Entity\TblSchool;
use SPHERE\Common\Frontend\Form\Repository\Button\Danger;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Link;
use SPHERE\Common\Frontend\Icon\Repository\Mail as MailIcon;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Phone as PhoneIcon;
use SPHERE\Common\Frontend\Icon\Repository\PhoneFax;
use SPHERE\Common\Frontend\Icon\Repository\PhoneMobil;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\TagList;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Repository\WellReadOnly;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Consumer\School
 */
class Frontend extends Extension implements IFrontendInterface
{


    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Schulen', 'Übersicht');
        $Stage->addButton(new Standard('Schule hinzufügen', '/Setting/Consumer/School/Create'));

        $IsUCSMandant = false;
        if(($tblConsumer = ConsumerGatekeeper::useService()->getConsumerBySession())
        && ConsumerGatekeeper::useService()->getConsumerLoginByConsumerAndSystem($tblConsumer, TblConsumerLogin::VALUE_SYSTEM_UCS)){
            $IsUCSMandant = true;
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Warning('Es ist noch keine Schule eingetragen')
                        )
                    ), new Title('')
                )
            )
        );

        if (( $tblSchoolAll = School::useService()->getSchoolAll() )) {
            $Stage->addButton(new Standard('Schule entfernen', '/Setting/Consumer/School/Delete'));
            $Form = null;
            foreach ($tblSchoolAll as $tblSchool) {
                $tblCompany = $tblSchool->getServiceTblCompany();
                $CompanyNumber = $tblSchool->getCompanyNumber();
                $SchoolCode = $tblSchool->getSchoolCode();
                $CompanyNumberStandard = '';
                if ($CompanyNumber == '') {
                    $tblResponsibilityList = Responsibility::useService()->getResponsibilityAll();
                    if ($tblResponsibilityList) {
                        /** @var TblResponsibility $tblResponsibility */
                        $tblResponsibility = current($tblResponsibilityList);
                        $CompanyNumberStandard = $tblResponsibility->getCompanyNumber();
                    }
                }

                $CompanyNumberPanel = new Panel(new PullClear('Unternehmensnr. des Unfallversicherungsträgers'
                        .new PullRight(($CompanyNumber == '' ? '(leer)' : '')))
                    , ($CompanyNumber != ''
                        ? $CompanyNumber
                        : ($CompanyNumberStandard != ''
                            ? 'Schulträger: '.$CompanyNumberStandard.' '.
                            new ToolTip(new Info(),
                                'Es wird diese Unternehmensnr. des Schulträgers verwendet, wenn bei der Schule keine hinterlegt ist.')
                            : '')),
                    ($CompanyNumber != '' ? Panel::PANEL_TYPE_SUCCESS : Panel::PANEL_TYPE_WARNING));

                $StudentCodePanel = new Panel('Dienststellenschlüssel (DISCH)',
                    ($SchoolCode ?: ($IsUCSMandant ? 'Pflichtfeld im UCS' : '')),
                    ($SchoolCode != ''
                        ? Panel::PANEL_TYPE_SUCCESS
                        : ($IsUCSMandant
                            ? Panel::PANEL_TYPE_DANGER
                            : Panel::PANEL_TYPE_WARNING))
                );

                if ($tblCompany) {
                    $Form .= new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(
                                self::frontendLayoutCombine($tblCompany)
                            )),
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new WellReadOnly(
                                        // Bearbeiten
                                        new Layout(new LayoutGroup(new LayoutRow(array(
                                            new LayoutColumn(
                                                $CompanyNumberPanel->setMarginBottom('7'), 6
                                            ),
                                            new LayoutColumn(
                                                $StudentCodePanel->setMarginBottom('7'), 6
                                            ),
                                            new LayoutColumn(new PullRight(
                                                new Standard('Bearbeiten','/Setting/Consumer/School/Edit', new Edit(),
                                                    array('Id' => $tblSchool->getId()), 'Bearbeiten')
                                            ))
                                        ))))
                                    )
                                , 6),
                            ))
                        ),
                            (new Title(new TagList().' '.
                                new \SPHERE\Common\Frontend\Text\Repository\Warning($tblSchool->getServiceTblType()
                                    ? $tblSchool->getServiceTblType()->getName() : ' ').' '
                                .$tblCompany->getDisplayName(), ' Kontaktdaten'
                            ))
                        ),
                    ));
                }
            }

            $Stage->setContent(
                $Form
            );
        }
        return $Stage;
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return Layout
     */
    public function frontendLayoutCombine(TblCompany $tblCompany)
    {

        $tblAddressAllUnsorted = Address::useService()->getAddressAllByCompany($tblCompany);

        $tblAddressAllMain = array();
        $tblAddressAllNotMain = array();
        if ($tblAddressAllUnsorted) {
            foreach ($tblAddressAllUnsorted as $tblAddress) {
                if (Address::useService()->getTypeById(1) == $tblAddress->getTblType()) {
                    $tblAddressAllMain[] = $tblAddress;
                } else {
                    $tblAddressAllNotMain[] = $tblAddress;
                }
            }
        }
        $tblAddressAll = array();
        if (!empty($tblAddressAllMain)) {
            $tblAddressAll = array_merge($tblAddressAll, $tblAddressAllMain);
        }
        if (!empty($tblAddressAllNotMain)) {
            $tblAddressAll = array_merge($tblAddressAll, $tblAddressAllNotMain);
        }

        $tblPhoneAll = Phone::useService()->getPhoneAllByCompany($tblCompany);
        $tblMailAll = Mail::useService()->getMailAllByCompany($tblCompany);
        $tblRelationshipAll = Relationship::useService()->getCompanyRelationshipAllByCompany($tblCompany);

        if ($tblAddressAll !== false) {
            array_walk($tblAddressAll, function (TblToCompany &$tblToCompany) {

                $Panel = array($tblToCompany->getTblAddress()->getGuiLayout());
                if ($tblToCompany->getRemark()) {
                    array_push($Panel, new Muted(new Small($tblToCompany->getRemark())));
                }

                $tblToCompany = new LayoutColumn(
                    new Panel(
                        new MapMarker().' '.$tblToCompany->getTblType()->getName(), $Panel, Panel::PANEL_TYPE_SUCCESS)
                    , 3);
            });
        } else {
            $tblAddressAll = array(
                new LayoutColumn(
                    new Warning('Keine Adressen hinterlegt')
                    , 3)
            );
        }

        if ($tblPhoneAll !== false) {
            array_walk($tblPhoneAll,
                function (\SPHERE\Application\Contact\Phone\Service\Entity\TblToCompany &$tblToCompany) {

                    $Panel = array($tblToCompany->getTblPhone()->getNumber());
                    if ($tblToCompany->getRemark()) {
                        array_push($Panel, new Muted(new Small($tblToCompany->getRemark())));
                    }

                    $tblToCompany = new LayoutColumn(
                        new Panel(
                            ( preg_match('!Fax!is',
                                $tblToCompany->getTblType()->getName().' '.$tblToCompany->getTblType()->getDescription())
                                ? new PhoneFax()
                                : ( preg_match('!Mobil!is',
                                    $tblToCompany->getTblType()->getName().' '.$tblToCompany->getTblType()->getDescription())
                                    ? new PhoneMobil()
                                    : new PhoneIcon()
                                )
                            ).' '.$tblToCompany->getTblType()->getName().' '.$tblToCompany->getTblType()->getDescription(),
                            $Panel,
                            ( $tblToCompany->getIsEmergencyContact()
                                ? Panel::PANEL_TYPE_DANGER
                                : Panel::PANEL_TYPE_SUCCESS
                            ))
                        , 3);
                });
        } else {
            $tblPhoneAll = array(
                new LayoutColumn(
                    new Warning('Keine Telefonnummern hinterlegt')
                    , 3)
            );
        }

        if ($tblMailAll !== false) {
            array_walk($tblMailAll,
                function (\SPHERE\Application\Contact\Mail\Service\Entity\TblToCompany &$tblToCompany) {

                    $Panel = array($tblToCompany->getTblMail()->getAddress());
                    if ($tblToCompany->getRemark()) {
                        array_push($Panel, new Muted(new Small($tblToCompany->getRemark())));
                    }

                    $tblToCompany = new LayoutColumn(
                        new Panel(
                            new MailIcon().' '.$tblToCompany->getTblType()->getName(), $Panel,
                            Panel::PANEL_TYPE_SUCCESS)
                        , 3);
                });
        } else {
            $tblMailAll = array(
                new LayoutColumn(
                    new Warning('Keine E-Mail Adressen hinterlegt')
                    , 3)
            );
        }
        if ($tblRelationshipAll !== false) {
            array_walk($tblRelationshipAll, function (
                \SPHERE\Application\People\Relationship\Service\Entity\TblToCompany &$tblToCompany
            ) {

                if ($tblToCompany->getServiceTblPerson() && $tblToCompany->getServiceTblCompany()) {
                    $Panel = array(
                        $tblToCompany->getServiceTblPerson()->getFullName(),
                        $tblToCompany->getServiceTblCompany()->getName()
                        .new Container($tblToCompany->getServiceTblCompany()->getExtendedName()),
                    );
                    if ($tblToCompany->getRemark()) {
                        array_push($Panel, new Muted(new Small($tblToCompany->getRemark())));
                    }

                    $tblToCompany = new LayoutColumn(
                        new Panel(
                            new Building().' '.new Link().' '.$tblToCompany->getTblType()->getName(), $Panel,
                            Panel::PANEL_TYPE_DEFAULT)
                        , 3);
                }
            }, $tblCompany);
            $tblRelationshipAll = array_filter($tblRelationshipAll);
        } else {
            $tblRelationshipAll = array(
                new LayoutColumn(
                    new Warning('Keine Institutionenbeziehungen hinterlegt')
                    , 3)
            );
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;

        /**
         * @var LayoutColumn $tblAddress
         */
        foreach ($tblAddressAll as $tblAddress) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblAddress);
            $LayoutRowCount++;
        }
        /**
         * @var LayoutColumn $tblPhone
         */
        foreach ($tblPhoneAll as $tblPhone) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblPhone);
            $LayoutRowCount++;
        }
        /**
         * @var LayoutColumn $tblMail
         */
        foreach ($tblMailAll as $tblMail) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblMail);
            $LayoutRowCount++;
        }

        /**
         * @var LayoutColumn $tblRelationship
         */
        foreach ($tblRelationshipAll as $tblRelationship) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblRelationship);
            $LayoutRowCount++;
        }

        return new Layout(new LayoutGroup($LayoutRowList));
    }

    /**
     * @param null $School
     * @param null $Type
     *
     * @return Stage
     */
    public function frontendSchoolCreate($School = null, $Type = null)
    {

        $Stage = new Stage('Schule', 'Hinzufügen');
        $Stage->addButton(new Standard('Zurück', '/Setting/Consumer/School', new ChevronLeft()));
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            School::useService()->createSchool(
                                $this->formSchoolCompanyCreate()
                                    ->appendFormButton(new Primary('Speichern', new Save()))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert'),
                                $Type, $School
                            )
                        ))
                    ), new Title(new PlusSign().' Hinzufügen')
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formSchoolCompanyCreate()
    {

        $PanelSelectCompanyTitle = new PullClear(
            'Schule auswählen:'
            .new PullRight(
                new Standard('Neue Institution anlegen', '/Corporation/Company', new Building()
                    , array(), '"Schule hinzufügen" verlassen'
                ))
        );
        $tblTypeAll = Type::useService()->getTypeAll();
        $tblCompanyAll = Company::useService()->getCompanyAll();
        $TableContent = array();
        if ($tblCompanyAll) {
            array_walk($tblCompanyAll, function (TblCompany $tblCompany) use (&$TableContent) {
                $temp['Select'] = new RadioBox('School', '&nbsp;', $tblCompany->getId());
                $temp['Content'] = $tblCompany->getName()
                    .new Container($tblCompany->getExtendedName())
                    .new Container(new Muted($tblCompany->getDescription()));
                array_push($TableContent, $temp);
            });
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel('Schulart',
                            array(
                                new SelectBox('Type[Type]', '',
                                    array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()
                                ),
                            ), Panel::PANEL_TYPE_INFO
                        ),
                    ), 4),
                    new FormColumn(array(
                        !empty( $TableContent ) ?
                            new Panel($PanelSelectCompanyTitle,
                                new TableData($TableContent, null, array(
                                    'Select'  => 'Auswahl',
                                    'Content' => 'Institution',
                                ), array(
                                    'columnDefs' => array(
                                        array('width' => '1%', 'targets' => array(0))
                                    ),
                                    'order' => array(
                                        array(1, 'asc'),
                                    ),
                                ))
                                , Panel::PANEL_TYPE_INFO, null)
                            : new Panel($PanelSelectCompanyTitle,
                            new Warning('Es ist keine Institution vorhanden die als Schule ausgewählt werden kann')
                            , Panel::PANEL_TYPE_INFO)
                    ,
                    ), 8),
                )),
            ))
        );
    }

    /**
     * @param null $Id
     * @param null $Data
     * @param null $School
     *
     * @return Stage
     */
    public function frontendSchoolEdit($Id = null, $Data = null, $School = null)
    {

        $Stage = new Stage('Schulinformationen', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Setting/Consumer/School', new ChevronLeft()));

        $IsUCSMandant = false;
        if(($tblConsumer = ConsumerGatekeeper::useService()->getConsumerBySession())
            && ConsumerGatekeeper::useService()->getConsumerLoginByConsumerAndSystem($tblConsumer, TblConsumerLogin::VALUE_SYSTEM_UCS)){
            $IsUCSMandant = true;
        }

        $tblSchool = School::useService()->getSchoolById($Id);
        $Type = '';
        $tblType = $tblSchool->getServiceTblType();
        if ($tblType) {
            $Type = $tblType->getName();
        }
        if (!$tblSchool) {
            return $Stage->setContent(new Warning('Diese Schule wurde nicht gefunden.')
                .new Redirect('/Setting/Consumer/School', Redirect::TIMEOUT_ERROR));
        }
        $Form = new Form(new FormGroup(new FormRow(array(
            new FormColumn(
                new Panel('Unternehmensnummer '.new ToolTip(new Info(),'des Unfallversicherungsträgers'),
                    new TextField('Data[CompanyNumber]', '', ''), Panel::PANEL_TYPE_SUCCESS)
            , 6),
            new FormColumn(
                new Panel('Dienststellenschlüssel (DISCH) '.
                    ($IsUCSMandant
                        ? new ToolTip(new Info(), 'Pflichtfeld im UCS')
                        : ''),
                    new TextField('Data[SchoolCode]', '', ''),
                    Panel::PANEL_TYPE_SUCCESS)
            , 6),
            new FormColumn(new HiddenField('School[IsSubmit]'))
        ))));
        $Form->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $tblCompany = $tblSchool->getServiceTblCompany();
        if ($tblCompany) {
            $PanelHead = new Panel($tblCompany->getDisplayName().' '.new Small(new Muted('('.$Type.')')), '', Panel::PANEL_TYPE_INFO);
        } else {
            $PanelHead = new Panel('Institution wird nicht mehr gefunden!', '', Panel::PANEL_TYPE_DANGER);
        }


        $Global = $this->getGlobal();
        if ($Data == null) {
            $Global->POST['Data']['CompanyNumber'] = $tblSchool->getCompanyNumber();
            $Global->POST['Data']['SchoolCode'] = $tblSchool->getSchoolCode();
            $Global->savePost();
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn($PanelHead, 6),)
                    ),
                    new LayoutRow(new LayoutColumn(
                        new Well(School::useService()->updateSchool(
                            $Form, $tblSchool, $Data, $School
                        ))
                    ))
                ))
            )
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendSchoolDelete()
    {

        $Stage = new Stage('Schule', 'Entfernen');
        $Stage->addButton(new Standard('Zurück', '/Setting/Consumer/School', new ChevronLeft()));
        $tblSchoolAll = School::useService()->getSchoolAll();
        if ($tblSchoolAll) {
            array_walk($tblSchoolAll, function (TblSchool &$tblSchool) {

                $tblCompany = $tblSchool->getServiceTblCompany();
                if ($tblCompany) {
                    $Address = array();
                    $Address[] = $tblCompany->getName().new Container($tblCompany->getExtendedName());
                    $tblAddressAll = Address::useService()->getAddressAllByCompany($tblCompany);
                    if ($tblAddressAll) {
                        foreach ($tblAddressAll as $tblAddress) {
                            $Address[] = new Muted(new Small($tblAddress->getTblAddress()->getStreetName().' '
                                .$tblAddress->getTblAddress()->getStreetNumber().' '
                                .$tblAddress->getTblAddress()->getTblCity()->getName()));
                        }
                    }
                    $Address[] = (new Standard('', '/Setting/Consumer/School/Destroy', new Remove(),
                        array('Id' => $tblSchool->getId())));
                    $Content = array_filter($Address);
                    $Type = Panel::PANEL_TYPE_WARNING;
                    $tblSchool = new LayoutColumn(
                        new Panel($tblSchool->getServiceTblType()
                            ? $tblSchool->getServiceTblType()->getName() : '', $Content, $Type)
                        , 6);
                } else {
                    $tblSchool = false;
                }
            });
            $tblSchoolAll = array_filter($tblSchoolAll);

            $LayoutRowList = array();
            $LayoutRowCount = 0;
            $LayoutRow = null;
            /**
             * @var LayoutColumn $tblSchool
             */
            foreach ($tblSchoolAll as $tblSchool) {
                if ($LayoutRowCount % 3 == 0) {
                    $LayoutRow = new LayoutRow(array());
                    $LayoutRowList[] = $LayoutRow;
                }
                $LayoutRow->addColumn($tblSchool);
                $LayoutRowCount++;
            }
        } else {
            $LayoutRowList = false;
        }
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    $LayoutRowList
                ),
            ))
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param bool $Confirm
     *
     * @return Stage|string
     */
    public function frontendSchoolDestroy($Id = null, $Confirm = false)
    {

        $Stage = new Stage('Schule', 'Löschen');
        if ($Id) {
            $tblSchool = School::useService()->getSchoolById($Id);
            if (!$tblSchool) {
                return $Stage.new Danger('Die Schule konnte nicht gefunden werden', new Ban())
                .new Redirect('/Setting/Consumer/School', Redirect::TIMEOUT_ERROR);
            }

            if (!$Confirm) {

                $Address = array();

                if ($tblSchool->getServiceTblCompany()) {
                    $Address[] = $tblSchool->getServiceTblCompany()->getName()
                        .new Container($tblSchool->getServiceTblCompany()->getExtendedName())
                        .new Container(new Muted($tblSchool->getServiceTblCompany()->getDescription()));

                    $tblAddressAll = Address::useService()->getAddressAllByCompany($tblSchool->getServiceTblCompany());
                    if ($tblAddressAll) {
                        foreach ($tblAddressAll as $tblAddress) {
                            $Address[] = new Muted(new Small($tblAddress->getTblAddress()->getStreetName().' '
                                .$tblAddress->getTblAddress()->getStreetNumber().' '
                                .$tblAddress->getTblAddress()->getTblCity()->getName()));
                        }
                    }
                }
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new Panel(new Question().' Diese Schule mit der Schulart "'
                            .( $tblSchool->getServiceTblType() ? $tblSchool->getServiceTblType()->getName() : '' )
                            .'" wirklich löschen?', $Address,
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Setting/Consumer/School/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            .new Standard(
                                'Nein', '/Setting/Consumer/School', new Disable()
                            )
                        )
                    ))))
                );
            } else {

                // Destroy Group
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ( School::useService()->destroySchool($tblSchool)
                                ? new Success('Die Schule wurde gelöscht')
                                .new Redirect('/Setting/Consumer/School', Redirect::TIMEOUT_SUCCESS)
                                : new Danger('Die Schule konnte nicht gelöscht werden')
                                .new Redirect('/Setting/Consumer/School', Redirect::TIMEOUT_ERROR)
                            )
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger('Die Schule konnte nicht gefunden werden'),
                        new Redirect('/Setting/Consumer/School', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }

        return $Stage;
    }

}
