<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\components;

use Yii;

/**
 * Minimal LDAP / Active Directory client built on ext-ldap.
 *
 * Configuration lives in `params['ldap']` (see config.php); every value can be
 * set through an environment variable (LDAP_*) or config.local.php:
 *
 *   LDAP_ENABLED=true
 *   LDAP_HOST=ad.company.com          LDAP_PORT=389 (or 636 for LDAPS)
 *   LDAP_BASE_DN=DC=company,DC=com
 *   LDAP_BIND_DN=CN=svc-read,OU=svc,DC=company,DC=com   (service account, optional)
 *   LDAP_BIND_PASSWORD=...
 *   LDAP_USER_FILTER=(sAMAccountName=%s)               (AD)  / (uid=%s) (OpenLDAP)
 *   LDAP_USER_DN_PATTERN=uid=%s,OU=users,DC=company,DC=com  (direct bind, when no service account)
 *   LDAP_STARTTLS=false               LDAP_TIMEOUT=5
 *
 * Two strategies are supported:
 *  1. service-account search + user bind (bind_dn + user_filter + base_dn)
 *  2. direct user bind (user_dn_pattern with %s escaped as a DN)
 */
class LdapClient
{
    public static function config(): array
    {
        return Yii::$app->params['ldap'] ?? [];
    }

    public static function enabled(): bool
    {
        $c = self::config();
        return !empty($c['enabled']) && function_exists('ldap_connect');
    }

    /**
     * Verify a username/password against the directory.
     *
     * @return array|null ['dn' => string, 'attributes' => array<string,string>]
     *                    or null when the credentials are rejected / LDAP is unreachable.
     */
    public static function authenticate(string $username, string $password): ?array
    {
        if (!self::enabled() || $username === '' || $password === '') {
            return null;
        }
        $c = self::config();
        $timeout = max(1, (int)($c['timeout'] ?? 5));

        $conn = @ldap_connect(strval($c['host'] ?? '127.0.0.1'), (int)($c['port'] ?? 389));
        if ($conn === false) {
            Yii::warning('LDAP: cannot connect to ' . ($c['host'] ?? '') . ':' . ($c['port'] ?? ''));
            return null;
        }
        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
        if (defined('LDAP_OPT_NETWORK_TIMEOUT')) {
            ldap_set_option($conn, LDAP_OPT_NETWORK_TIMEOUT, $timeout);
        }
        if (!empty($c['start_tls'])) {
            if (!@ldap_start_tls($conn)) {
                Yii::warning('LDAP: start_tls negotiation failed; refusing plaintext credentials');
                ldap_unbind($conn);
                return null;
            }
        }

        $userDn = null;
        $attributes = [];

        if (!empty($c['bind_dn'])) {
            // strategy 1: bind as the service account, search for the user, then re-bind as the user
            if (!@ldap_bind($conn, strval($c['bind_dn']), strval($c['bind_password'] ?? ''))) {
                Yii::warning('LDAP: service account bind failed');
                ldap_unbind($conn);
                return null;
            }
            $filter = sprintf(strval($c['user_filter'] ?? '(uid=%s)'), ldap_escape($username, '', LDAP_ESCAPE_FILTER));
            $attrs = array_values(array_filter([
                $c['username_attribute'] ?? 'uid',
                $c['display_name_attribute'] ?? 'cn',
                $c['email_attribute'] ?? 'mail',
            ]));
            $sr = @ldap_search($conn, strval($c['base_dn'] ?? ''), $filter, $attrs);
            if ($sr === false) {
                ldap_unbind($conn);
                return null;
            }
            $entries = @ldap_get_entries($conn, $sr);
            if ($entries === false || ($entries['count'] ?? 0) < 1) {
                ldap_unbind($conn);
                return null;
            }
            $userDn = $entries[0]['dn'];
            $attributes = self::flatten($entries[0], $attrs);
        } elseif (!empty($c['user_dn_pattern'])) {
            // strategy 2: direct bind with a DN pattern
            $userDn = sprintf(strval($c['user_dn_pattern']), ldap_escape($username, '', LDAP_ESCAPE_DN));
        } else {
            Yii::warning('LDAP: neither bind_dn nor user_dn_pattern configured');
            ldap_unbind($conn);
            return null;
        }

        // the actual credential check: bind as the user with their password
        if (!@ldap_bind($conn, $userDn, $password)) {
            ldap_unbind($conn);
            return null;
        }

        if ($attributes === []) {
            // direct-bind strategy: fetch the profile attributes with the user's own connection
            $attrs = array_values(array_filter([
                $c['display_name_attribute'] ?? 'cn',
                $c['email_attribute'] ?? 'mail',
            ]));
            $sr = @ldap_search($conn, $userDn, '(objectClass=*)', $attrs);
            if ($sr !== false) {
                $entries = @ldap_get_entries($conn, $sr);
                if ($entries !== false && ($entries['count'] ?? 0) > 0) {
                    $attributes = self::flatten($entries[0], $attrs);
                }
            }
        }

        ldap_unbind($conn);
        return ['dn' => $userDn, 'attributes' => $attributes];
    }

    /**
     * ldap_get_entries() wraps every value in an array with a 'count' key — flatten to scalars.
     */
    private static function flatten(array $entry, array $attrs): array
    {
        $out = [];
        foreach ($attrs as $attr) {
            $attr = strtolower($attr);
            if (isset($entry[$attr][0]) && is_string($entry[$attr][0])) {
                $out[$attr] = $entry[$attr][0];
            }
        }
        return $out;
    }
}
