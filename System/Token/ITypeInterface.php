<?php
namespace SPHERE\System\Token;

/**
 * Interface ITypeInterface
 *
 * @package SPHERE\System\Token
 */
interface ITypeInterface
{

    /**
     * @return string
     */
    public function getConfiguration();

    /**
     * @param array $Configuration
     */
    public function setConfiguration($Configuration);
}
