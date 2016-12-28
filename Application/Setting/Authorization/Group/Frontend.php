<?php
namespace SPHERE\Application\Setting\Authorization\Group;

use SPHERE\Common\Frontend\Ajax\Emitter\ClientEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Setup;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Structure\LinkGroup;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Authorization\Group
 */
class Frontend extends Extension implements IFrontendInterface
{

    public function frontendUserGroup()
    {
        $Stage = new Stage('Benutzergruppen');
        $Stage->setMessage('');


        $CreateUserGroupToggle = new Pipeline();
        $CreateUserGroupToggle->addEmitter(new ClientEmitter($CreateUserGroupReceiver = new ModalReceiver(
                new PlusSign() . ' Neue Benutzergruppe anlegen', new Close()
            ), $this->layoutCreateUserGroup()
            )
        );

        $Stage->addButton((new Standard('Neue Benutzergruppe anlegen', '#',
            new PlusSign()))->ajaxPipelineOnClick($CreateUserGroupToggle));

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            $CreateUserGroupReceiver
                        )
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            $this->layoutUserGroup()
                        )
                    ),

                ))
            )
        );

        return $Stage;
    }

    private function layoutCreateUserGroup()
    {
        return new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Well(
                            $this->formUserGroup()
                                ->appendFormButton(
                                    new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Speichern', new Save())
                                )
                        )
                    )
                )
            )
        );
    }

    private function formUserGroup()
    {
        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new TextField('Group[Name]', 'Gruppenname', 'Gruppenname')
                    ),
                    new FormColumn(
                        (new TextArea('Group[Description]', 'Gruppenbeschreibung',
                            'Gruppenbeschreibung'))->setMaxLengthValue(200)
                    ),
                ))
            )
        );
    }

    private function layoutUserGroup()
    {
        return new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new TableData(array(
                            array(
                                'Name' => 'Gruppenname',
                                'Description' => 'Gruppenbeschreibung',
                                'Role' => 'Zugriffsrechte',
                                'Member' => 'Benutzer',
                                'Option' => (new LinkGroup())
                                        ->addLink(new Standard('', '#', new Edit()))
                                        ->addLink(new Standard('', '#', new Remove()))
                                    . new Standard('', '#', new Setup())
                            )
                        ), null, array(
                            'Name' => 'Gruppenname',
                            'Description' => 'Gruppenbeschreibung',
                            'Role' => 'Zugriffsrechte',
                            'Member' => 'Benutzer',
                            'Option' => ''
                        ))
                    )
                ), new Title(new PersonGroup() . ' Bestehende Benutzergruppen')
            )
        );
    }
}
