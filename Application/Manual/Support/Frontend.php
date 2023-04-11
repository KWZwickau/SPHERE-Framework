<?php
namespace SPHERE\Application\Manual\Support;

use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblUser;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\MailField;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Flash;
use SPHERE\Common\Frontend\Icon\Repository\Mail as MailIcon;
use SPHERE\Common\Frontend\Icon\Repository\Phone as PhoneIcon;
use SPHERE\Common\Frontend\Icon\Repository\Quote;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Manual\Support
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $Ticket
     * @param null $Attachment
     *
     * @return Stage
     */
    public function frontendSupport($Ticket = null, $Attachment = null)
    {

        $Stage = new Stage('Feedback & Support', 'Ticket eröffnen');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            Support::useService()->createTicket(
                                $this->formTicket()
                                , $Ticket, $Attachment)
                            , 6),
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    public function formTicket()
    {

        $tblMailList = array();
        $tblPhoneList = array();
        if(($tblAccount = Account::useService()->getAccountBySession())
        && ($tblUserList = Account::useService()->getUserAllByAccount($tblAccount))){
            /** @var TblUser $tblUser */
            $tblUser = current($tblUserList);
            if(($tblPerson = $tblUser->getServiceTblPerson())){
                if(($tblToPersonMList = Mail::useService()->getMailAllByPerson($tblPerson))){
                    foreach($tblToPersonMList as $tblToPersonM){
                        $tblMailList[] = $tblToPersonM->getTblMail();
                    }
                }
                if(($tblToPersonPList = Phone::useService()->getPhoneAllByPerson($tblPerson))){
                    foreach($tblToPersonPList as $tblToPersonP){
                        $tblPhoneList[] = $tblToPersonP->getTblPhone();
                    }
                }
                if(!$tblMailList){
                    $tblMailList = array();
                }
                if(!$tblPhoneList){
                    $tblPhoneList = array();
                }
            }
        }
        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Feedback oder Support-Anfrage', array(
                            (new AutoCompleter('Ticket[Mail]', 'Ihre Email-Adresse', 'meine@email.de', array('Address' => $tblMailList), new MailIcon()))->setRequired(),
                            (new TextField('Ticket[Subject]', 'Thema', 'Betreff der Anfrage', new Flash()))->setRequired(),
                            (new TextArea('Ticket[Body]', 'Meine Frage oder mein Problem',
                                'Inhalt der Nachricht', new Quote()))->setRequired(),
                            new AutoCompleter('Ticket[CallBackNumber]', 'Rückrufnummer', 'Vorwahl/Telefonnummer', array('Number' => $tblPhoneList), new PhoneIcon()),
                            new FileUpload('Attachment', 'z.B. ein Screenshot', 'Optionaler Datei-Anhang
                            (max: '.ini_get('upload_max_filesize').'B)', null, array(
                                'showPreview' => false
                            )),
                        ), Panel::PANEL_TYPE_INFO,
                            new Primary('Absenden', new MailIcon()).new Danger(new Small(' (* Pflichtfeld)')))),
                ))
            )
        );
    }

    /**
     * @param null $Ticket
     *
     * @return Stage
     */
    public function frontendRequest($Ticket = null)
    {

        $Stage = new Stage('Source-Code', 'Anfrage erstellen');
        $Stage->addButton(new Standard('Zurück', '/Document/License', new ChevronLeft()));

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            Support::useService()->createRequest(
                                $this->formRequest()
                                , $Ticket)
                            , 6),
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    public function formRequest()
    {

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Anfrage', array(
                            (new MailField('Ticket[Mail]', 'meine@email.de', 'Ihre Email-Adresse', new MailIcon()))->setRequired(),
                            (new TextArea('Ticket[Body]', 'Meine Frage oder mein Problem',
                                'Inhalt der Anfrage', new Quote()))->setRequired(),
                            new TextField('Ticket[CallBackNumber]', 'Vorwahl/Telefonnummer', 'Rückrufnummer', new PhoneIcon()),
                        ), Panel::PANEL_TYPE_INFO,
                            new Primary('Absenden', new MailIcon()).new Danger(new Small(' (* Pflichtfeld)')))),
                ))
            )
        );
    }
}
