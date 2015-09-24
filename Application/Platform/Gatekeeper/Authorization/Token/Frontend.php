<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Token;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service\Entity\TblToken;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\PasswordField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Token
 */
class Frontend
{

    /**
     * @param null|string $CredentialKey
     *
     * @return Stage
     */
    public function frontendYubiKey($CredentialKey)
    {

        $Stage = new Stage('Hardware-Schlüssel', 'YubiKey');
        $tblTokenAll = Token::useService()->getTokenAll();
        if ($tblTokenAll) {
            array_walk($tblTokenAll, function (TblToken &$tblToken) {

                /** @noinspection PhpUndefinedFieldInspection */
                $tblToken->Name = strtoupper($tblToken->getIdentifier());
                strtoupper($tblToken->getIdentifier());
                if ($tblToken->getSerial() % 2 != 0) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $tblToken->Number = '0'.$tblToken->getSerial();
                } else {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $tblToken->Number = $tblToken->getSerial();
                }
                /** @noinspection PhpUndefinedFieldInspection */
                $tblToken->Number = substr($tblToken->Number, 0, 4).' '.substr($tblToken->Number, 4, 4);
                /** @noinspection PhpUndefinedFieldInspection */
                $tblToken->Option = new Danger('Löschen',
                    '/Platform/Gatekeeper/Authorization/Access/PrivilegeGrantRight',
                    new Remove(), array('Id' => $tblToken->getId()), 'Löschen'
                );
            });
        }
        $Stage->setContent(
            ( $tblTokenAll
                ? new TableData($tblTokenAll, new Title('Bestehende Hardware-Schlüssel'), array(
                    'Name'   => 'Name',
                    'Number' => 'Seriennummer',
//                    'Option' => 'Optionen'
                ))
                : new Warning('Keine Hardware-Schlüssel vorhanden')
            )
            .Token::useService()->createToken(
                new Form(new FormGroup(
                        new FormRow(
                            new FormColumn(
                                new PasswordField('CredentialKey', 'YubiKey', 'YubiKey')
                            )
                        ), new \SPHERE\Common\Frontend\Form\Repository\Title('Hardware-Schlüssel anlegen'))
                    , new Primary('Hinzufügen')
                ), $CredentialKey
            )
        );
        return $Stage;
    }
}
