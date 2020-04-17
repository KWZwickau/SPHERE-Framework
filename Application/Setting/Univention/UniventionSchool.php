<?php
namespace SPHERE\Application\Setting\Univention;

use SPHERE\Application\Setting\Univention\Service\Entity\TblUnivention;

class UniventionSchool
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

    public function getAllSchools()
    {

        curl_reset($this->curlhandle);

        curl_setopt_array($this->curlhandle, array(
            CURLOPT_URL => 'https://'.$this->server.'/v1/schools/',
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
          -administrative_servers
          -class_share_file_server
          -dc_name
          -dc_name_administrative
          -display_name
          -dn
          -educational_servers
          -home_share_file_server
          -name
          -ucsschool_roles
          -url
         **/

        $Json = curl_exec($this->curlhandle);
        $StdClassArray = json_decode($Json);

        $schoolList = array();
        if(is_array($StdClassArray) && !empty($StdClassArray)){
            foreach($StdClassArray as $StdClass){
                $schoolList[$StdClass->name] = $StdClass->url;
            }
        }
        return (!empty($schoolList) ? $schoolList : false);
    }

    function __destruct()
    {

        curl_close($this->curlhandle);
    }
}