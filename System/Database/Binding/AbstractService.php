<?php
namespace SPHERE\System\Database\Binding;

use SPHERE\Application\IServiceInterface;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\ColumnHydrator;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

/**
 * Class AbstractService
 *
 * @package SPHERE\System\Database\Binding
 */
abstract class AbstractService extends Extension implements IServiceInterface
{

    /** @var null|Binding */
    private $Binding = null;
    /** @var null|Structure */
    private $Structure = null;

    /**
     * Define Database Connection
     *
     * @param Identifier $Identifier
     * @param string     $EntityPath
     * @param string     $EntityNamespace
     */
    final public function __construct(Identifier $Identifier, $EntityPath, $EntityNamespace)
    {

        $this->Binding = new Binding($Identifier, $EntityPath, $EntityNamespace);
        $this->Structure = new Structure($Identifier);
    }

    /**
     * @return Structure
     */
    final public function getStructure()
    {

        return $this->Structure;
    }

    /**
     * @return Binding
     */
    final public function getBinding()
    {

        return $this->Binding;
    }

    /**
     * @param Element[] $EntityList
     *
     * @return int
     */
    final protected function countEntityList($EntityList)
    {

        if (empty( $EntityList )) {
            return 0;
        }
        return count(array_filter($EntityList, function (Element $Element) {

            return !$Element->getEntityRemove();
        }));
    }

    /**
     * Get Distinct List of Property Values
     *
     * @param Element $Entity
     * @param string $PropertyName
     * @param array $ConditionList e.g. array( 'ColumnName1' => null, 'ColumnName2' => 'Value', ... )
     * @return array
     */
    final public function getPropertyList( Element $Entity, $PropertyName, $ConditionList = array() )
    {
        $Manager = $this->Binding->getEntityManager();
        $Builder = $Manager->getQueryBuilder();

        $Builder->select( 'Entity.'.$PropertyName )
            ->distinct(true)
            ->from( get_class($Entity), 'Entity' )
            ->where(
                $Builder->expr()->isNull( 'Entity.'.Element::ENTITY_REMOVE )
            );

        if( !empty( $ConditionList ) ) {
            $ParameterList = array();
            foreach ( $ConditionList as $Column => $Match ) {
                if( $Match === null ) {
                    $Builder->andWhere(
                        $Builder->expr()->isNull( 'Entity.'.$Column )
                    );
                } else {
                    $Builder->andWhere(
                        $Builder->expr()->eq( 'Entity.'.$Column, ':'.$Column )
                    );
                    $ParameterList[':'.$Column] = $Match;
                }
            }
            foreach ( $ParameterList as $Parameter => $Value ) {
                $Builder->setParameter( $Parameter, $Value );
            }
        }

        $Query = $Builder->getQuery();
        $Query->useQueryCache(true);
        return $Query->getResult( ColumnHydrator::HYDRATION_MODE );
    }
}
