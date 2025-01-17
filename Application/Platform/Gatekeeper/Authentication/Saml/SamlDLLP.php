<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authentication\Saml;

class SamlDLLP
{

    /**
     * @return array
     */
    public static function getSAML(){
        return[
            'strict' => true,
            'debug' => true,
            'baseurl' => null,
            'sp' => array(
                'entityId' => 'https://www.schulsoftware.schule/Platform/Gatekeeper/Saml/DLLP/MetaData',
                'assertionConsumerService' => array(
                    'url' => 'https://www.schulsoftware.schule/Platform/Gatekeeper/Authentication/Saml/DLLP',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                ),
                'singleLogoutService' => array(
                    'url' => 'https://schulsoftware.schule/Platform/Gatekeeper/Authentication/SLO',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
                'x509cert' => '',
                'privateKey' => '',
            ),

            'idp' => array(
                'entityId' => 'https://auth.dllp.schule/realms/ucs',
                'singleSignOnService' => array(
                    'url' => 'https://auth.dllp.schule/realms/ucs/protocol/saml',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                'singleLogoutService' => array(
                    'url' => 'https://auth.dllp.schule/realms/ucs/protocol/saml',
                    'responseUrl' => '',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                'x509cert' => 'MIIClTCCAX0CBgGQbaRLzTANBgkqhkiG9w0BAQsFADAOMQwwCgYDVQQDDAN1Y3MwHhcNMjQwNzAxMDkzMTQyWhcNMzQwNzAxMDkzMzIyWjAOMQwwCgYDVQQDDAN1Y3MwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDGjKAXwvnCV8qEyOhDk83LanG09ETDvhxyIT7349Ee8iBP9GlPwra9iChmr21pGgnsPgro5gKisO/m9dtOfHP2S5kW7bRW4I1KGbBCw7hgnrfBLDVkkXkqMQ5mwCpiRMqBsJeBZDBXJF6khbpTf2D0JduTBx7hCeLfjvJuYhh18wpteZCl3CLpNQ3uUUy41G6MIXH3o4KmjJPrD3RuNrBhZJ4Sh8PhC8+IngfrgiWCL0lLk7OMxEjTudwty3YOkTWb9GDgPCU67EwkyjPEtW/K/Co0jA/Zt7GddoGH02DsQ9Dl9nV+R5BmwFhye5BIQTRsUzE/BlCQ3X32EdMoPIPVAgMBAAEwDQYJKoZIhvcNAQELBQADggEBAMTVqdXV+dFonO30W60Da5l+p6e3v5WHfZUSNiZjPRAZ/lIOWK6cnl4LglK996KvsQj486rBO5GTr5u726lAVVzky2rxpKqC1qbZ4zON5vOYYPj7hQkVTlD+4i7N831v/d8Hy6uIR9Uhgkmz5l9p/izsffu02Yiz2dRE+IXem0aMYeSAmHBCGMAD+Ww9ojR2DmL+4+UcHRF9ytPU36hiphfUMfjJCzmNqDyq9ZF+dkE+SdPLktR8piiCqZmaQUmJfx25g676xXOUYjZ1qPgD99aqofZISXVlmgquEl/dz/eH/Qv7//vllKrGxkrN6bfEPM5hceOtt82y95XE7dvzJ/8=',
            ),
        ];
    }
}