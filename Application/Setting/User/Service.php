<?php
namespace SPHERE\Application\Setting\User;

/**
 * Class Service
 * @package SPHERE\Application\Setting\User
 */
class Service
{

    /**
     * @param int  $completeLength number all filled up with (abcdefghjkmnpqrstuvwxyz)
     * @param int  $specialLength number of (!$%&=?*-:;.,+_)
     * @param int  $numberLength number of (123456789)
     * @param bool $isCapitalLetter true = (ABCDEFGHJKMNPQRSTUVWXYZ)
     *
     * @return string
     */
    public function generatePassword($completeLength = 8, $specialLength = 0, $numberLength = 0, $isCapitalLetter = false)
    {

        $numberChars = '123456789';
        $specialChars = '!$%&=?*-:;.,+_';
        $secureChars = 'abcdefghjkmnpqrstuvwxyz';
        $return = '';

        if ($isCapitalLetter == true) // Add CapitalLetter
        {
            $secureChars .= strtoupper($secureChars);
        }

        $count = $completeLength - $specialLength - $numberLength;
        if ($count > 0) {
            // get normal characters
            $temp = str_shuffle($secureChars);
            $return = substr($temp, 0, $count);
        }
        if ($specialLength > 0) {
            // get special characters
            $temp = str_shuffle($specialChars);
            $return .= substr($temp, 0, $specialLength);
        }
        if ($numberLength > 0) {
            // get numbers
            $temp = str_shuffle($numberChars);
            $return .= substr($temp, 0, $numberLength);
        }
        // Random
        $return = str_shuffle($return);

        return $return;
    }

}