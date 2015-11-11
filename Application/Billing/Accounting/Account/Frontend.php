<?php

namespace SPHERE\Application\Billing\Accounting\Account;

use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\BarCode;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendAccountFibu()
    {

        $Stage = new Stage();
        $Stage->setTitle('FIBU-Konten');
        $Stage->setDescription('Übersicht');
        $Stage->setMessage('Zeigt die verfügbaren Finanzbuchhaltungskonten an');
        $Stage->addButton(
            new Standard('FIBU-Konto anlegen', '/Billing/Accounting/Account/Create', new Plus())
        );

        $tblAccountAll = Account::useService()->getAccountAll();

        if (!empty( $tblAccountAll )) {
            array_walk($tblAccountAll, function (TblAccount $tblAccount) {

                $tblAccount->Taxes = $tblAccount->getTblAccountKey()->getValue();
                $tblAccount->Code = $tblAccount->getTblAccountKey()->getCode();
                $tblAccount->Typ = $tblAccount->getTblAccountType()->getName();
                if ($tblAccount->getIsActive() === true) {
                    $tblAccount->Option =
                        (new Standard('', '/Billing/Accounting/Account/Deactivate',
                            new Remove(), array(
                                'Id' => $tblAccount->getId()
                            ), 'Deaktivieren'))->__toString();
                } else {
                    $tblAccount->Option =
                        (new Standard('', '/Billing/Accounting/Account/Activate',
                            new Ok(), array(
                                'Id' => $tblAccount->getId()
                            ), 'Aktivieren'))->__toString();
                }
            });
        }
        $Stage->setContent(
            new TableData($tblAccountAll, null,
                array(
                    'Number'      => 'Kennziffer',
                    'Description' => 'Beschreibung',
                    'Typ'         => 'Konto',
                    'Taxes'       => 'MwSt.',
                    'Code'        => 'Code',
                    'Option'      => 'Optionen'
                )
            )
        );

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendAccountFibuActivate($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Aktivierung');

        if (Account::useService()->changeFibuActivate($Id)) {

            $Stage->setContent(new Redirect('/Billing/Accounting/Account', 0));

        } else {

            $Stage->setContent(new Warning('Ihr Konto konnte nicht Aktiviert werden'));
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendAccountFibuDeactivate($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Deaktivierung');

        if (Account::useService()->changeFibuDeactivate($Id)) {
            $Stage->setContent(new Redirect('/Billing/Accounting/Account', 0));
        } else {
            $Stage->setContent(new Warning('Ihr Konto konnte nicht Deaktiviert werden'));
        }

        return $Stage;
    }

    /**
     * @param $Account
     *
     * @return Stage
     */
    public function frontendAccountCreate($Account)
    {

        $Stage = new Stage();
        $Stage->setTitle('FIBU-Konto');
        $Stage->setDescription('Hinzufügen');
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Account', new ChevronLeft()));

        $tblAccountKey = Account::useService()->getKeyValueAll();
        $tblAccountType = Account::useService()->getTypeValueAll();

        $Form = new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new TextField('Account[Number]', 'Kennziffer', 'Kennziffer', new BarCode()
                        ), 6),
                    new FormColumn(
                        new TextField('Account[Description]', 'Beschreibung', 'Beschreibung', new Conversation()
                        ), 6
                    )
                )),
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('Account[Key]', 'Mehrwertsteuer',
                            array('Value' => $tblAccountKey)
                        ), 6
                    ),
                    new FormColumn(
                        new SelectBox('Account[Type]', 'Typ',
                            array('Name' => $tblAccountType)
                        ), 6
                    )
                ))
            ))
        ));
        $Form->appendFormButton(new Primary('Hinzufügen'));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(Account::useService()->createAccount(
            $Form, $Account)
        );

        return $Stage;
    }
}
