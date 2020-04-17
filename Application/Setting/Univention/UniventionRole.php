<?php
namespace SPHERE\Application\Setting\Univention;

use SPHERE\Application\Setting\Univention\Service\Entity\TblUnivention;

class UniventionRole
{
    private $curlhandle;
    private $server;
    private $token;

    public function __construct() {

        if(($tblUnivention = Univention::useService()->getUnivention(TblUnivention::TYPE_VALUE_SERVER))){
            $this->server = $tblUnivention->getValue();
        }
        if(($tblUnivention = Univention::useService()->getUnivention(TblUnivention::TYPE_VALUE_TOKEN))){
            $this->token = $tblUnivention->getValue();
        }

        $this->curlhandle = curl_init();
    }

    public function getAllRoles()
    {

        curl_reset($this->curlhandle);

        curl_setopt_array($this->curlhandle, array(
            CURLOPT_URL => 'https://'.$this->server.'/v1/roles/',
            CURLOPT_HTTPGET => TRUE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_HTTPHEADER => array('accept: application/json',
                'Authorization: bearer '.$this->token),
            //return the transfer as a string
            CURLOPT_RETURNTRANSFER => TRUE
//            CURLOPT_POSTFIELDS => $Content
        ));

        /**
         * possible field's
          -display_name
          -name
          -url
         **/
        $Json = curl_exec($this->curlhandle);
        $StdClassArray = json_decode($Json);
        $roleList = array();
        if(is_array($StdClassArray) && !empty($StdClassArray)){
            foreach($StdClassArray as $StdClass){
                $roleList[$StdClass->name] = $StdClass->url;
            }
        }
        return (!empty($roleList) ? $roleList : false);
    }

    function __destruct()
    {

        curl_close($this->curlhandle);
    }
}