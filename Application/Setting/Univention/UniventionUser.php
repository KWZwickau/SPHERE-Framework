<?php
namespace SPHERE\Application\Setting\Univention;

use SPHERE\Application\Setting\Univention\Service\Entity\TblUnivention;

class UniventionUser
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

    /**
     * @param      $name // Suche nach Mandanten Beispiel: "ref-"
     * @param bool $fromFirstChar
     *
     * @return array|bool
     */
    public function getUserListByName($name, $fromFirstChar = true)
    {

        curl_reset($this->curlhandle);


        if($fromFirstChar){
            $Url = 'https://'.$this->server.'/v1/users/?name='.$name.'%2A';
        } else {
            $Url = 'https://'.$this->server.'/v1/users/?name=%2A'.$name.'%2A';
        }

        curl_setopt_array($this->curlhandle, array(
            CURLOPT_URL => $Url,
            CURLOPT_HTTPGET => TRUE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_HTTPHEADER => array('accept: application/json',
                'Authorization: bearer '.$this->token),
            //return the transfer as a string
            CURLOPT_RETURNTRANSFER => TRUE
//            CURLOPT_POSTFIELDS => $Content
        ));

        $Json = curl_exec($this->curlhandle);
        $StdClassArray = json_decode($Json);

        $UserList = array();
        if(is_array($StdClassArray) && !empty($StdClassArray)){
            foreach($StdClassArray as $StdClass){
                $UserList[] = $StdClass->name;
            }
        }
        return (!empty($UserList) ? $UserList : false);
    }

    public function createUser($name = '', $firstname = '', $lastname = '', $record_uid = '', $roles = array(),
        $schools = array(), $source_uid = '')
    {
        curl_reset($this->curlhandle);

        $PersonContent = array(
            'name' => $name,
            'firstname' => $firstname,
            'lastname' => $lastname,
            // Try AccountId to find Account again?
            'record_uid' => $record_uid,
            'roles' => $roles,
            'schools' => $schools, // test with two array elements
            // Mandant + AccountId to human resolve problems?
            'source_uid' => $source_uid
        );

        $PersonContent = json_encode($PersonContent);

//        $PersonContent = http_build_query($PersonContent);

        curl_setopt_array($this->curlhandle, array(
            CURLOPT_URL => 'https://'.$this->server.'/v1/users/',
            CURLOPT_POST => TRUE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_HTTPHEADER => array('accept: application/json',
                'Content-Type: application/json',
                'Authorization: bearer '.$this->token),
            //return the transfer as a string
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POSTFIELDS => $PersonContent
        ));

        /**
         * possible field's
          - dn
          - url
          - ucsschool_roles
          - name
          - school
          - firstname
          - lastname
          - birthday
          - disabled
          - email
          - record_uid
          - roles
          - schools
          - school_classes
          - source_uid
          - udm_properties
            {
            * description
            * gidNumber
            * employeeType
            * organisation
            * phone
            * title
            * uidNumber
            }
         **/
        $Json = curl_exec($this->curlhandle);
        $StdClass = json_decode($Json);
        echo "<pre>";
        var_dump($StdClass);
        echo "</pre>";
//        exit;

        $Error = null;

        if(isset($StdClass->detail)){
            if(is_string($StdClass->detail)){
                $Error = $StdClass->detail;
            }elseif(is_array($StdClass->detail)){
                $Error = '';
                foreach($StdClass->detail as $Detail){
                    if(is_object($Detail)){
                        $Error .= $name.' - '.$Detail->msg;
                    }
                }
            }
        }

        return $Error;
    }

    public function deleteUser($name)
    {

        curl_reset($this->curlhandle);

        curl_setopt_array($this->curlhandle, array(
            CURLOPT_URL => 'https://'.$this->server.'/v1/users/'.$name,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_HTTPHEADER => array('Authorization: bearer '.$this->token),
            //return the transfer as a string
            CURLOPT_RETURNTRANSFER => TRUE,
        ));

        $Json = curl_exec($this->curlhandle);
        $StdClass = json_decode($Json);
        $Error = null;

        if(isset($StdClass->detail)){
            if(is_string($StdClass->detail)){
                $Error = $StdClass->detail;
            }elseif(is_array($StdClass->detail)){
                $Error = '';
                foreach($StdClass->detail as $Detail){
                    if(is_object($Detail)){
                        $Error .= $name.' - '.$Detail->msg;
                    }
                }
            }
        }

        return $Error;
    }

    function __destruct()
    {

        curl_close($this->curlhandle);
    }
}