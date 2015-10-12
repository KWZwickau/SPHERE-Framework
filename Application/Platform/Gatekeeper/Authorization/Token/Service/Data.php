<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
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

        // Remove
//        $this->createToken('ccccccectjkd');

        $this->createToken('ccccccectjiu', Consumer::useService()->getConsumerById(1));
        $this->createToken('ccccccectjiv', Consumer::useService()->getConsumerById(1));
        $this->createToken('ccccccectjjc', Consumer::useService()->getConsumerById(4));
        $this->createToken('ccccccectjjb', Consumer::useService()->getConsumerById(5));
        $this->createToken('ccccccectjjd', Consumer::useService()->getConsumerById(6));
        $this->createToken('ccccccectjje', Consumer::useService()->getConsumerById(2));
        $this->createToken('ccccccectjjf', Consumer::useService()->getConsumerById(7));
        $this->createToken('ccccccectjjg', Consumer::useService()->getConsumerById(7));
        $this->createToken('ccccccectjjh', Consumer::useService()->getConsumerById(8));
        $this->createToken('ccccccdtrire', Consumer::useService()->getConsumerById(3));
        $this->createToken('ccccccdjclnc', Consumer::useService()->getConsumerById(3));
        $this->createToken('ccccccectjji', Consumer::useService()->getConsumerById(9));
        $this->createToken('ccccccectjjj', Consumer::useService()->getConsumerById(1));
        $this->createToken('ccccccectjjk', Consumer::useService()->getConsumerById(1));
        $this->createToken('ccccccectjjl', Consumer::useService()->getConsumerById(1));

        $this->createToken('ccccccectjgt');
        $this->createToken('ccccccectjgr');

        $this->createToken('ccccccectjjn', Consumer::useService()->getConsumerById(10));

        // Remove
//        $this->createToken('ccccccectjke');

        $this->createToken('ccccccectjgl', Consumer::useService()->getConsumerById(6));
        $this->createToken('ccccccectjjr', Consumer::useService()->getConsumerById(1));
        $this->createToken('ccccccectjkg', Consumer::useService()->getConsumerById(1));
        $this->createToken('ccccccectjkf', Consumer::useService()->getConsumerById(1));
        $this->createToken('ccccccectjgn', Consumer::useService()->getConsumerById(9));
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
