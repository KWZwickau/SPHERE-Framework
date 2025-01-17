<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Data;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumerLogin;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Cache\Handler\MemoryHandler;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Consumer
 */
class Service extends AbstractService
{

    /** @var TblConsumer[] $ConsumerByIdCache */
    private static $ConsumerByIdCache = array();
    /** @var TblConsumer[] $ConsumerByAcronymCache */
    private static $ConsumerByAcronymCache = array();

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
     * @param integer $Id
     *
     * @return bool|TblConsumer
     */
    public function getConsumerById($Id)
    {

        if (is_numeric($Id)) {
            if (array_key_exists($Id, self::$ConsumerByIdCache)) {
                return self::$ConsumerByIdCache[$Id];
            }
            self::$ConsumerByIdCache[$Id] = (new Data($this->getBinding()))->getConsumerById($Id);
            return self::$ConsumerByIdCache[$Id];
        } else {
            return false;
        }
    }

    /**
     * @param $Id
     *
     * @return bool|TblConsumer
     */
    public function getConsumerLoginById($Id)
    {

        return (new Data($this->getBinding()))->getConsumerLoginById($Id);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblConsumer
     */
    public function getConsumerByName($Name)
    {

        return (new Data($this->getBinding()))->getConsumerByName($Name);
    }

    /**
     * @param TblConsumer $tblConsumer
     *
     * @return TblConsumerLogin[]|false
     */
    public function getConsumerLoginListByConsumer(TblConsumer $tblConsumer)
    {

        return (new Data($this->getBinding()))->getConsumerLoginListByConsumer($tblConsumer);
    }

    /**
     * @param TblConsumer $tblConsumer
     * @param string      $SystemName
     *
     * @return TblConsumerLogin|false
     */
    public function getConsumerLoginByConsumerAndSystem(TblConsumer $tblConsumer, string $SystemName = '')
    {

        return (new Data($this->getBinding()))->getConsumerLoginByConsumerAndSystem($tblConsumer, $SystemName);
    }

    /**
     * @param null|string $Session
     *
     * @return bool|TblConsumer
     */
    public function getConsumerBySession($Session = null)
    {

        $Cache = $this->getCache(new MemoryHandler());
        if (null === ( $tblConsumer = $Cache->getValue($Session, __METHOD__) )) {
            $tblConsumer = (new Data($this->getBinding()))->getConsumerBySession($Session);
            if ($tblConsumer) {
                $Cache->setValue($Session, $tblConsumer, 0, __METHOD__);
            } else {
                $tblConsumer = (new Data($this->getBinding()))->getConsumerById(1);
                $Cache->setValue($Session, $tblConsumer, 0, __METHOD__);
            }
            return $tblConsumer;
        } else {
            return $tblConsumer;
        }
    }

    /**
     * @param string $Type
     * @param string $Acronym
     * @param string|null $Session
     *
     * @return bool
     */
    public function getConsumerBySessionIsConsumer(string $Type, string $Acronym, ?string $Session = null): bool
    {
        if(($tblConsumer = $this->getConsumerBySession($Session))) {
            return $tblConsumer->isConsumer($Type, $Acronym);
        }

        return false;
    }

    /**
     * @param string $Type
     * @param string|null $Session
     *
     * @return bool
     */
    public function getConsumerBySessionIsConsumerType(string $Type, ?string $Session = null): bool
    {
        if(($tblConsumer = $this->getConsumerBySession($Session))) {
            return $tblConsumer->getType() == $Type;
        }

        return false;
    }

    /**
     * @return bool|TblConsumer[]
     */
    public function getConsumerAll()
    {

        return (new Data($this->getBinding()))->getConsumerAll();
    }

    /**
     * @param IFormInterface $Form
     * @param string $ConsumerAcronym
     * @param string $ConsumerName
     * @param $ConsumerAlias
     *
     * @return IFormInterface|Redirect
     */
    public function createConsumer(
        IFormInterface &$Form,
        $ConsumerAcronym,
        $ConsumerName,
        $ConsumerAlias
    ) {

        if (null === $ConsumerName
            && null === $ConsumerAcronym
        ) {
            return $Form;
        }

        $Error = false;
        if (null !== $ConsumerAcronym && empty( $ConsumerAcronym )) {
            $Form->setError('ConsumerAcronym', 'Bitte geben Sie ein Mandantenkürzel an');
            $Error = true;
        }
        if ($this->getConsumerByAcronym($ConsumerAcronym)) {
            $Form->setError('ConsumerAcronym', 'Das Mandantenkürzel muss einzigartig sein');
            $Error = true;
        }
        if (null !== $ConsumerName && empty( $ConsumerName )) {
            $Form->setError('ConsumerName', 'Bitte geben Sie einen gültigen Mandantenname ein');
            $Error = true;
        }

        if ($Error) {
            return $Form;
        } else {
            $ConsumerType = $this->getConsumerTypeFromServerHost();

            (new Data($this->getBinding()))->createConsumer($ConsumerAcronym, $ConsumerName, $ConsumerType, $ConsumerAlias);
            return new Redirect('/Platform/Gatekeeper/Authorization/Consumer', 0);
        }
    }

    /**
     * @return void
     */
    public function updateConsumer(): void {
        if(($tblConsumer = $this->getConsumerBySession())){
            (new Data($this->getBinding()))->updateConsumer($tblConsumer);
        }
    }

    /**
     * @param string $Acronym
     *
     * @return bool|TblConsumer
     */
    public function getConsumerByAcronym($Acronym)
    {

        if (array_key_exists($Acronym, self::$ConsumerByAcronymCache)) {
            return self::$ConsumerByAcronymCache[$Acronym];
        }
        self::$ConsumerByAcronymCache[$Acronym] = (new Data($this->getBinding()))->getConsumerByAcronym($Acronym);
        return self::$ConsumerByAcronymCache[$Acronym];
    }

    /**
     * @return string
     */
    public function getConsumerTypeFromServerHost(): string
    {
        if (strpos(strtolower($_SERVER['HTTP_HOST']), 'ekbo') !== false) {
            return TblConsumer::TYPE_BERLIN;
        } else {
            return TblConsumer::TYPE_SACHSEN;
        }
    }
}
