<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authentication\Saml;

class SamlDLLPDemo
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
                'entityId' => 'https://www.schulsoftware.schule/Platform/Gatekeeper/Saml/DLLPDemo/MetaData',
                'assertionConsumerService' => array(
                    'url' => 'https://www.schulsoftware.schule/Platform/Gatekeeper/Authentication/Saml/DLLPDemo',
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
                'entityId' => 'https://sso.dllp-test.schule/simplesamlphp/saml2/idp/metadata.php',
                'singleSignOnService' => array(
                    'url' => 'https://sso.dllp-test.schule/simplesamlphp/saml2/idp/SSOService.php',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                'singleLogoutService' => array(
                    'url' => 'https://sso.dllp-test.schule/simplesamlphp/saml2/idp/SingleLogoutService.php',
                    'responseUrl' => '',
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                //ToDO
                'x509cert' => 'MIIFhjCCBG6gAwIBAgIBAjANBgkqhkiG9w0BAQsFADCB0zELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAlVTMQswCQYDVQQHEwJVUzEhMB8GA1UEChMYRXYuIFNjaHVsdmVyZWluIFJhZGViZXVsMSQwIgYDVQQLExtVbml2ZW50aW9uIENvcnBvcmF0ZSBTZXJ2ZXIxOjA4BgNVBAMTMVVuaXZlbnRpb24gQ29ycG9yYXRlIFNlcnZlciBSb290IENBIChJRD1yMTBJZklQaikxJTAjBgkqhkiG9w0BCQEWFnNzbEBjb25uZXhpb24uZXZzc24uZGUwHhcNMjEwODA1MTQyMzI2WhcNMjYwODA0MTQyMzI2WjCBvDELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAlVTMQswCQYDVQQHEwJVUzEhMB8GA1UEChMYRXYuIFNjaHVsdmVyZWluIFJhZGViZXVsMSQwIgYDVQQLExtVbml2ZW50aW9uIENvcnBvcmF0ZSBTZXJ2ZXIxIzAhBgNVBAMTGnVjcy1zc28uY29ubmV4aW9uLmV2c3NuLmRlMSUwIwYJKoZIhvcNAQkBFhZzc2xAY29ubmV4aW9uLmV2c3NuLmRlMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA5evDqJXB1W6P8ndbg79nuH92M2XaonznvlHCLphYOcl5OX6culhNmZC4c6ImFYwzbZUotFOAdzvhw7Ole0UEUMjd53EYdHhzVtuEKh/Gfav0X8Cj/6h6kjfaxpP8QedODifV50doUiQ7qOlM3Rw22nAJ7XVpCxC21tz2NzruTn2mIk2PARXSxOTcXM464CherP0vZcnhNqbnpd7VOlmifcwRNFu7uFf1BThq5XqmbwNL1Kn7lffg7TMy6jQ4TGkxyHJbTGNr5BYcSnr5PKZKuSrcJUWoYxI/iGgDVWdQ+23zx2GM1paRK9ObN4wIQrkVKP6fXTM7LMeGYCQKGhZGTwIDAQABo4IBeDCCAXQwCQYDVR0TBAIwADAdBgNVHQ4EFgQUimqXlNdUdEfOZPPLK91K4hP4bbUwggEJBgNVHSMEggEAMIH9gBR7kfM8STpNZeh5OHoMw2WSloMt4KGB2aSB1jCB0zELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAlVTMQswCQYDVQQHEwJVUzEhMB8GA1UEChMYRXYuIFNjaHVsdmVyZWluIFJhZGViZXVsMSQwIgYDVQQLExtVbml2ZW50aW9uIENvcnBvcmF0ZSBTZXJ2ZXIxOjA4BgNVBAMTMVVuaXZlbnRpb24gQ29ycG9yYXRlIFNlcnZlciBSb290IENBIChJRD1yMTBJZklQaikxJTAjBgkqhkiG9w0BCQEWFnNzbEBjb25uZXhpb24uZXZzc24uZGWCCQCybuWbR3dhIDALBgNVHQ8EBAMCBeAwLgYDVR0RBCcwJYIadWNzLXNzby5jb25uZXhpb24uZXZzc24uZGWCB3Vjcy1zc28wDQYJKoZIhvcNAQELBQADggEBAFvqnWq8+jm0o7NWgqfUExJJyPpEzMy9mHr0MQhHqFLsfmavylsbVkFmu14/wpEFjmOnM4jHRCNZjdYPx8PGpr4A0jJwY58eExEAw1hyZwBopBEfATzdVQNCzLhUHY2cXLuBbx0P1BLQ+bnMiDoLf1OWTbPwn8m9SDh7krk45JZERAoOvLebe7k7PjAy09fhUskkv7E/GHytXMCOilkxHHEKN0V1omw/AZ4sVOdqFRHwTH2lEwnPaxwRnzz8Q9IaQlxVe4ywkQqkRFsP3uxWvUeEZTJscvkEDDv4ybNdzlxn/vD85yExkEtJhImeyDFZztoTh1MM+K4nNKmhYg9Gm9w=',
            ),
        ];
    }
}