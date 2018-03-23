<?php
namespace SPHERE\Application;

/**
 * Interface IApiInterface
 *
 * @package SPHERE\Application
 */
interface IApiInterface
{
    const API_TARGET = 'Method';

    /**
     *
     */
    public static function registerApi();

    /**
     * @param string $Method
     * @return string
     */
    public function exportApi($Method = '');
}
