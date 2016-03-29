<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service\Entity\TblToken;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $this->createToken('ccccccdilkui');
        $this->createToken('ccccccectjge');
        $this->createToken('ccccccectjgt');
        $this->createToken('ccccccectjgr');
    }

    /**
     * @param string      $Identifier
     * @param TblConsumer $tblConsumer
     *
     * @return TblToken
     */
    public function createToken($Identifier, TblConsumer $tblConsumer = null)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblToken')->findOneBy(array(TblToken::ATTR_IDENTIFIER => $Identifier));
        if (null === $Entity) {
            $Entity = new TblToken($Identifier);
            $Entity->setSerial($this->getModHex($Identifier)->getSerialNumber());
            $Entity->setServiceTblConsumer($tblConsumer);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblToken
     */
    public function getTokenByIdentifier($Identifier)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblToken')
            ->findOneBy(array(TblToken::ATTR_IDENTIFIER => $Identifier));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return TblToken[]|bool
     */
    public function getTokenAll()
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblToken')->findAll();
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToken
     */
    public function getTokenById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblToken', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblConsumer $tblConsumer
     *
     * @return bool|TblToken[]
     */
    public function getTokenAllByConsumer(TblConsumer $tblConsumer)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblToken')->findBy(array(
            TblToken::SERVICE_TBL_CONSUMER => $tblConsumer->getId()
        ));
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblToken $tblToken
     *
     * @return bool
     */
    public function destroyToken(TblToken $tblToken)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblToken', $tblToken->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            return true;
        }
        return false;
    }
}
