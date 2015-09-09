<?php
namespace SPHERE\Application\Setting\Authorization\Token;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service\Entity\TblToken;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\PasswordField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Key;
use SPHERE\Common\Frontend\Icon\Repository\PersonKey;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Authorization\Token
 */
class Frontend implements IFrontendInterface
{

    /**
     * @param null|string $CredentialKey
     *
     * @return Stage
     */
    public function frontendYubiKey($CredentialKey = null)
    {

        $Stage = new Stage('Hardware-Token', 'YubiKey');
        $Stage->setMessage('Bestehende Hardware-Token');

        $tblTokenAll = Token::useService()->getTokenAllByConsumer(Consumer::useService()->getConsumerBySession());
        if ($tblTokenAll) {
            array_walk($tblTokenAll, function (TblToken &$tblToken) {

                $Serial = $tblToken->getSerial();
                $Serial = substr($Serial, 0, 4).' '.substr($Serial, 4, 4);

                $Content = array();

                $tblAccountAll = Account::useService()->getAccountAllByToken($tblToken);
                array_walk($tblAccountAll, function (TblAccount &$tblAccount) {

                    $tblAccount = new PersonKey().' '.$tblAccount->getUsername();
                });
                $Content = array_merge($Content, $tblAccountAll);

                $Content = array_filter($Content);
                $Footer = new PullLeft(
                    new Standard('',
                        '/Setting/Authorization/Token/Destroy',
                        new Remove(), array('Id' => $tblToken->getId()),
                        'Schlüssel '.$Serial.' löschen'
                    )
                );
                $tblToken = new LayoutColumn(
                    new Panel(new Key().' '.$Serial, $Content, Panel::PANEL_TYPE_INFO, new PullClear($Footer))
                    , 3);
            });
            $tblTokenAll = array_filter($tblTokenAll);
        }
        if ($tblTokenAll) {
            $LayoutRowList = array();
            $LayoutRowCount = 0;
            $LayoutRow = null;
            /**
             * @var LayoutColumn $tblAccount
             */
            foreach ($tblTokenAll as $tblAccount) {
                if ($LayoutRowCount % 4 == 0) {
                    $LayoutRow = new LayoutRow(array());
                    $LayoutRowList[] = $LayoutRow;
                }
                $LayoutRow->addColumn($tblAccount);
                $LayoutRowCount++;
            }
        } else {
            $LayoutRowList = new LayoutRow(
                new LayoutColumn(
                    new Warning('Keine Hardware-Token vorhanden')
                )
            );
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    $LayoutRowList
                    , new Title('Hardware-Token')
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            Token::useService()->createToken(
                                $this->formYubiKey()
                                    ->appendFormButton(new Primary('Hardware-Token hinzufügen'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $CredentialKey, Consumer::useService()->getConsumerBySession())
                        )
                    ), new Title('Hardware-Token hinzufügen')
                ),
            ))
        );
        return $Stage;
    }

    /**
     * @return Form
     */
    private function formYubiKey()
    {

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel('Hardware-Token hinzufügen', array(
                            new PasswordField('CredentialKey', 'YubiKey', 'YubiKey'),
                        ), Panel::PANEL_TYPE_INFO)
                    )),
                )),
            ))
        );
    }

}
