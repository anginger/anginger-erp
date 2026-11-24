<?php
/**
 * Copy this file to config.local.php (gitignored) and fill in your values.
 *
 *   cp config.local.sample.php config.local.php
 *
 * Every LDAP_* environment variable can be used instead of this file.
 */

return [
    'params' => [
        'ldap' => [
            // master switch: when true, built-in registration is disabled and
            // logins also verify against the LDAP/AD directory
            'enabled' => false,
            'host' => 'ad.company.com',        // or 127.0.0.1 for OpenLDAP
            'port' => 389,                     // 636 for LDAPS
            'base_dn' => 'DC=company,DC=com',
            // service account used to search for users (leave empty to use
            // user_dn_pattern direct bind instead)
            'bind_dn' => 'CN=svc-anginger,OU=Service,DC=company,DC=com',
            'bind_password' => 'change-me',
            // Active Directory:  'user_filter' => '(sAMAccountName=%s)',
            //                     'username_attribute' => 'sAMAccountName',
            // OpenLDAP:           'user_filter' => '(uid=%s)',
            'user_filter' => '(sAMAccountName=%s)',
            'username_attribute' => 'sAMAccountName',
            'display_name_attribute' => 'displayName', // AD; OpenLDAP: cn
            'email_attribute' => 'mail',
            // or direct-bind strategy without a service account:
            // 'user_dn_pattern' => 'uid=%s,OU=users,DC=company,DC=com',
            'start_tls' => false,
            'timeout' => 5,
        ],
    ],
];
