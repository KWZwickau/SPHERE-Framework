<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authentication\Saml;

class SamlPlaceholder
{

    /**
     * @return array
     */
    public static function getSAML(){
        return[
            // If 'strict' is True, then the PHP Toolkit will reject unsigned
            // or unencrypted messages if it expects them signed or encrypted
            // Also will reject the messages if not strictly follow the SAML
            // standard: Destination, NameId, Conditions ... are validated too.
            'strict' => true,

            // Enable debug mode (to print errors)
            'debug' => true,

            // Set a BaseURL to be used instead of try to guess
            // the BaseURL of the view that process the SAML Message.
            // Ex. http://sp.example.com/
            //     http://example.com/sp/
            'baseurl' => null,

            // Service Provider Data that we are deploying
            'sp' => array(
                // Identifier of the SP entity  (must be a URI) // ToDO
                'entityId' => 'https://www.schulsoftware.schule/Platform/Gatekeeper/Saml/Placeholder/MetaData',
                // Specifies info about where and how the <AuthnResponse> message MUST be
                // returned to the requester, in this case our SP.
                'assertionConsumerService' => array(
                    // URL Location where the <Response> from the IdP will be returned // ToDO
                    'url' => 'https://www.schulsoftware.schule/Platform/Gatekeeper/Authentication/Saml/Placeholder',
                    // SAML protocol binding to be used when returning the <Response>
                    // message.  Onelogin Toolkit supports for this endpoint the
                    // HTTP-POST binding only
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                ),
//        // If you need to specify requested attributes, set a
//        // attributeConsumingService. nameFormat, attributeValue and
//        // friendlyName can be omitted. Otherwise remove this section.
//        "attributeConsumingService"=> array(
//                "serviceName" => "Schulsoftware",
//                "serviceDescription" => "SSO Schulsoftware",
//                "requestedAttributes" => array(
//                    array(
//                        "name" => "record_uid",
//                        "isRequired" => true,
//                        "nameFormat" => "",
//                        "friendlyName" => "",
//                        "attributeValue" => ""
//                    )
//                )
//        ),
                // Specifies info about where and how the <Logout Response> message MUST be
                // returned to the requester, in this case our SP.
                'singleLogoutService' => array(
                    // URL Location where the <Response> from the IdP will be returned
                    'url' => 'https://schulsoftware.schule/Platform/Gatekeeper/Authentication/SLO',
                    // SAML protocol binding to be used when returning the <Response>
                    // message.  Onelogin Toolkit supports for this endpoint the
                    // HTTP-Redirect binding only
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                // Specifies constraints on the name identifier to be used to
                // represent the requested subject.
                // Take a look on lib/Saml2/Constants.php to see the NameIdFormat supported
                'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',

                // Usually x509cert and privateKey of the SP are provided by files placed at
                // the certs folder. But we can also provide them with the following parameters
                'x509cert' => '',
                'privateKey' => '',

                /*
                 * Key rollover
                 * If you plan to update the SP x509cert and privateKey
                 * you can define here the new x509cert and it will be
                 * published on the SP metadata so Identity Providers can
                 * read them and get ready for rollover.
                 */
                // 'x509certNew' => '',
            ),

            // Identity Provider Data that we want connect with our SP
            'idp' => array(
                // Identifier of the IdP entity  (must be a URI) // ToDO
                'entityId' => 'https://sso.dllp.schule/simplesamlphp/saml2/idp/metadata.php',
                // SSO endpoint info of the IdP. (Authentication Request protocol)
                'singleSignOnService' => array(
                    // URL Target of the IdP where the SP will send the Authentication Request Message // ToDO
                    'url' => 'https://sso.dllp.schule/simplesamlphp/saml2/idp/SSOService.php',
                    // SAML protocol binding to be used when returning the <Response>
                    // message.  Onelogin Toolkit supports for this endpoint the
                    // HTTP-Redirect binding only
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                // SLO endpoint info of the IdP.
                'singleLogoutService' => array(
                    // URL Location of the IdP where the SP will send the SLO Request // ToDO
                    'url' => 'https://sso.dllp.schule/simplesamlphp/saml2/idp/SingleLogoutService.php',
                    // URL location of the IdP where the SP will send the SLO Response (ResponseLocation)
                    // if not set, url for the SLO Request will be used
                    'responseUrl' => '',
                    // SAML protocol binding to be used when returning the <Response>
                    // message.  Onelogin Toolkit supports for this endpoint the
                    // HTTP-Redirect binding only
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                // Public x509 certificate of the IdP // ToDO
                'x509cert' => 'MIIFhjCCBG6gAwIBAgIBAjANBgkqhkiG9w0BAQsFADCB0zELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAlVTMQswCQYDVQQHEwJVUzEhMB8GA1UEChMYRXYuIFNjaHVsdmVyZWluIFJhZGViZXVsMSQwIgYDVQQLExtVbml2ZW50aW9uIENvcnBvcmF0ZSBTZXJ2ZXIxOjA4BgNVBAMTMVVuaXZlbnRpb24gQ29ycG9yYXRlIFNlcnZlciBSb290IENBIChJRD1yMTBJZklQaikxJTAjBgkqhkiG9w0BCQEWFnNzbEBjb25uZXhpb24uZXZzc24uZGUwHhcNMjEwODA1MTQyMzI2WhcNMjYwODA0MTQyMzI2WjCBvDELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAlVTMQswCQYDVQQHEwJVUzEhMB8GA1UEChMYRXYuIFNjaHVsdmVyZWluIFJhZGViZXVsMSQwIgYDVQQLExtVbml2ZW50aW9uIENvcnBvcmF0ZSBTZXJ2ZXIxIzAhBgNVBAMTGnVjcy1zc28uY29ubmV4aW9uLmV2c3NuLmRlMSUwIwYJKoZIhvcNAQkBFhZzc2xAY29ubmV4aW9uLmV2c3NuLmRlMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA5evDqJXB1W6P8ndbg79nuH92M2XaonznvlHCLphYOcl5OX6culhNmZC4c6ImFYwzbZUotFOAdzvhw7Ole0UEUMjd53EYdHhzVtuEKh/Gfav0X8Cj/6h6kjfaxpP8QedODifV50doUiQ7qOlM3Rw22nAJ7XVpCxC21tz2NzruTn2mIk2PARXSxOTcXM464CherP0vZcnhNqbnpd7VOlmifcwRNFu7uFf1BThq5XqmbwNL1Kn7lffg7TMy6jQ4TGkxyHJbTGNr5BYcSnr5PKZKuSrcJUWoYxI/iGgDVWdQ+23zx2GM1paRK9ObN4wIQrkVKP6fXTM7LMeGYCQKGhZGTwIDAQABo4IBeDCCAXQwCQYDVR0TBAIwADAdBgNVHQ4EFgQUimqXlNdUdEfOZPPLK91K4hP4bbUwggEJBgNVHSMEggEAMIH9gBR7kfM8STpNZeh5OHoMw2WSloMt4KGB2aSB1jCB0zELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAlVTMQswCQYDVQQHEwJVUzEhMB8GA1UEChMYRXYuIFNjaHVsdmVyZWluIFJhZGViZXVsMSQwIgYDVQQLExtVbml2ZW50aW9uIENvcnBvcmF0ZSBTZXJ2ZXIxOjA4BgNVBAMTMVVuaXZlbnRpb24gQ29ycG9yYXRlIFNlcnZlciBSb290IENBIChJRD1yMTBJZklQaikxJTAjBgkqhkiG9w0BCQEWFnNzbEBjb25uZXhpb24uZXZzc24uZGWCCQCybuWbR3dhIDALBgNVHQ8EBAMCBeAwLgYDVR0RBCcwJYIadWNzLXNzby5jb25uZXhpb24uZXZzc24uZGWCB3Vjcy1zc28wDQYJKoZIhvcNAQELBQADggEBAFvqnWq8+jm0o7NWgqfUExJJyPpEzMy9mHr0MQhHqFLsfmavylsbVkFmu14/wpEFjmOnM4jHRCNZjdYPx8PGpr4A0jJwY58eExEAw1hyZwBopBEfATzdVQNCzLhUHY2cXLuBbx0P1BLQ+bnMiDoLf1OWTbPwn8m9SDh7krk45JZERAoOvLebe7k7PjAy09fhUskkv7E/GHytXMCOilkxHHEKN0V1omw/AZ4sVOdqFRHwTH2lEwnPaxwRnzz8Q9IaQlxVe4ywkQqkRFsP3uxWvUeEZTJscvkEDDv4ybNdzlxn/vD85yExkEtJhImeyDFZztoTh1MM+K4nNKmhYg9Gm9w=',
                /*
                 *  Instead of use the whole x509cert you can use a fingerprint in
                 *  order to validate the SAMLResponse, but we don't recommend to use
                 *  that method on production since is exploitable by a collision
                 *  attack.
                 *  (openssl x509 -noout -fingerprint -in "idp.crt" to generate it,
                 *   or add for example the -sha256 , -sha384 or -sha512 parameter)
                 *
                 *  If a fingerprint is provided, then the certFingerprintAlgorithm is required in order to
                 *  let the toolkit know which Algorithm was used. Possible values: sha1, sha256, sha384 or sha512
                 *  'sha1' is the default value.
                 */
                // 'certFingerprint' => '',
                // 'certFingerprintAlgorithm' => 'sha1',

                /* In some scenarios the IdP uses different certificates for
                 * signing/encryption, or is under key rollover phase and more
                 * than one certificate is published on IdP metadata.
                 * In order to handle that the toolkit offers that parameter.
                 * (when used, 'x509cert' and 'certFingerprint' values are
                 * ignored).
                 */
                // 'x509certMulti' => array(
                //      'signing' => array(
                //          0 => '<cert1-string>',
                //      ),
                //      'encryption' => array(
                //          0 => '<cert2-string>',
                //      )
                // ),
            ),
        ];
    }
}