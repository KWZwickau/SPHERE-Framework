<?php
namespace SPHERE\Application\Billing\Accounting\Account;

use SPHERE\Application\Billing\Accounting\Account\Service\Data;
use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccountKey;
use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccountKeyType;
use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccountType;
use SPHERE\Application\Billing\Accounting\Account\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Accounting\Account
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @return array|bool|TblAccount[]
     */
    public function getAccountAll()
    {

        return (new Data($this->getBinding()))->getAccountAll();
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function changeFibuActivate(TblAccount $tblAccount)
    {

        return (new Data($this->getBinding()))->updateActivateAccount($tblAccount);
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function changeFibuDeactivate(TblAccount $tblAccount)
    {

        return (new Data($this->getBinding()))->updateDeactivateAccount($tblAccount);
    }

    /**
     * @return bool|TblAccountKey[]
     */
    public function getKeyValueAll()
    {

        return (new Data($this->getBinding()))->getKeyValueAll();
    }

    /**
     * @return bool|TblAccountType[]
     */
    public function getTypeValueAll()
    {

        return (new Data($this->getBinding()))->getTypeValueAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblAccountType
     */
    public function getAccountTypeById($Id)
    {

        return (new Data($this->getBinding()))->getAccountTypeById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblAccountKey
     */
    public function getAccountKeyById($Id)
    {

        return (new Data($this->getBinding()))->getAccountKeyById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblAccountKeyType
     */
    public function getAccountKeyTypeById($Id)
    {

        return (new Data($this->getBinding()))->getAccountKeyTypeById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblAccount
     */
    public function getAccountById($Id)
    {

        return (new Data($this->getBinding()))->getAccountById($Id);
    }

    /**
     * @param bool $IsActive
     *
     * @return bool|TblAccount[]
     */
    public function getAccountAllByActiveState($IsActive = true)
    {

        return (new Data($this->getBinding()))->getAccountAllByActiveState($IsActive);
    }

    /**
     * @param $Iban
     *
     * @return bool|null
     *  true = Iban correct;
     *  false = Iban false;
     *  null = Iban incorrect format (preg_match)
     */
    public function getControlIban($Iban)
    {

        /**
         * Zuerst findet eine Verschiebung der Zeichenkette statt. Der Ländercode DE und 00 für den fehlenden Prüfcode rücken an das Ende der Zeichenkette.
         * A	B	C	D	E	F	G	H	I	J	K	L	M	N	O	P	Q	R	S	T	U	V	W	..
         * 10	11	12	13	14	15	16	17	18	19	20	21	22	23	24	25	26	27	28	29	30	31	32	..
         * Aus DE wird demzufolge 1314 +Prüfziffer 00
         */
        $Iban = $Iban.'131400';
        // pregmatch passt nur für deutsche IBAN
        preg_match('!([A-Z]{2})([0-9]{2})([0-9]{9})([0-9]{7})([0-9]{7})([0-9]{1})!', $Iban, $Match);
        if(isset($Match[3])
            && isset($Match[4])
            && isset($Match[5])
            && isset($Match[6])
        ){
            $pre = '';
            for($i = 3; $i<= 6; $i++){
                $pre = $this->calculateModulo($Match[$i], $pre);
            }
            $controlNumber = 98 - $pre;
            if($controlNumber == $Match[2]){
                return true;
            } else {
                return false;
            }
        }
        return null;
    }

    /**
     * @param int $Number
     * @param int|string $pre
     *
     * @return int
     */
    private function calculateModulo($Number, $pre = '')
    {
        $Number = $pre.$Number;
        return $Number % 97;
    }

    /**
     * @param IFormInterface $Form
     * @param                $Account
     *
     * @return IFormInterface|string
     */
    public function createAccount(IFormInterface &$Form = null, $Account)
    {

        /**
         * Skip to Frontend
         */
        if(null === $Account){
            return $Form;
        }
        $Error = false;
        if(isset($Account['Number']) && empty($Account['Number'])){
            $Form->setError('Account[Number]', 'Bitte geben sie die Nummer an');
            $Error = true;
        }
        $Account['IsActive'] = 1;

        if(!$Error){
            (new Data($this->getBinding()))->createAccount(
                $Account['Number'],
                $Account['Description'],
                $Account['IsActive'],
                (new Data($this->getBinding()))->getAccountKeyById($Account['Key']),
                (new Data($this->getBinding()))->getAccountTypeById($Account['Type']));
            return new Success('Die Bankverbindung ist erfasst worden')
                .new Redirect('/Billing/Accounting/Account', Redirect::TIMEOUT_SUCCESS);
        }
        return $Form;
    }

}
