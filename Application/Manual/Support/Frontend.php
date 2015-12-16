<?php
namespace SPHERE\Application\Manual\Support;

use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Mail;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
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
     *
     * @return Stage
     */
    public function frontendSupport($Ticket = null)
    {

        $Stage = new Stage('Support', 'Ticket');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            $this->formTicket()
                                ->appendFormButton(new Primary('Absenden', new Mail()))
                        )
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
                            new HiddenField('Placeholder[1]', '', ''), 1
                        ),
                        new FormColumn(
                            new TextField('Ticket[Head]', '', 'Betreff'), 6
                        ),
                        new LayoutColumn(
                            new TextArea('Ticket[Body]', '', 'Inhalt')
                        )
                    )
                )
            )
        );
    }
}
