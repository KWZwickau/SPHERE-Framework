<?php
namespace SPHERE\Application\Manual\Support;

use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Mail;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Manual\Support
 */
class Frontend implements IFrontendInterface
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
                    new LayoutRow(
                        new LayoutColumn(
                            Support::useService()->createTicket(
                                $this->formTicket()
                                , $Ticket, $Attachment)
                            , 6)
                    )
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

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Feedback oder Support-Anfrage', array(
                            new TextField('Ticket[Mail]', 'meine@email.de', 'Ihre Email-Adresse'.new Danger(' *')),
                            new TextField('Ticket[Subject]', 'Thema', 'Betreff der Anfrage'.new Danger(' *')),
                            new TextArea('Ticket[Body]', 'Meine Frage oder mein Problem',
                                'Inhalt der Nachricht'.new Danger(' *')),
                            new FileUpload('Attachment', 'z.B. ein Screenshot', 'Optionaler Datei-Anhang'),
                        ), Panel::PANEL_TYPE_INFO, new Primary('Absenden', new Mail()).new Danger(' (* Pflichtfeld)'))),
                ))
            )
        );
    }
}
