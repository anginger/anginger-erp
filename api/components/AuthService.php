<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\components;

use anginger\models\User;
use Ramsey\Uuid\Uuid;
use Yii;

/**
 * Login flow shared by every Anginger app:
 *
 *  1. archived accounts are always rejected (both local and LDAP)
 *  2. built-in (auth_source=local) accounts authenticate with their local password —
 *     they keep working even when LDAP is enabled
 *  3. when LDAP/AD is enabled, any other username/password is verified against the
 *     directory; on success a local user record is provisioned/extended on the fly
 *     (auth_source=ldap, ldap_dn, display_name, email synced from the directory)
 *
 * Built-in registration (manually creating local accounts) is disabled while LDAP
 * is enabled — see the user/account ApiController::validateCreate().
 */
class AuthService
{
    public const ERROR_INVALID = 'invalid';
    public const ERROR_ARCHIVED = 'archived';

    /**
     * @return array{0: ?User, 1: ?string} [user, error] — exactly one is set.
     */
    public static function attempt(string $username, string $password): array
    {
        $user = User::findOne(['username' => $username]);

        // archived = blocked: no local password, no LDAP, no existing session
        if ($user !== null && $user->archived) {
            return [null, self::ERROR_ARCHIVED];
        }

        // 1. built-in account with a local password
        if ($user !== null && $user->auth_source === 'local'
            && $user->password !== null && $user->password === hash('sha256', $password)) {
            $user->last_login_time = time();
            $user->save(false, ['last_login_time']);
            return [$user, null];
        }

        // 2. LDAP / AD
        if (LdapClient::enabled()) {
            $result = LdapClient::authenticate($username, $password);
            if ($result !== null) {
                $user = self::provisionFromLdap($user, $username, $result);
                if ($user->archived) {
                    return [null, self::ERROR_ARCHIVED];
                }
                $user->last_login_time = time();
                $user->save(false, ['last_login_time', 'ldap_dn', 'display_name', 'email']);
                return [$user, null];
            }
        }

        return [null, self::ERROR_INVALID];
    }

    /**
     * Find-or-create the local record for a successfully LDAP-authenticated user,
     * syncing profile attributes from the directory (the "extension" of the
     * built-in user functionality when LDAP is enabled).
     */
    private static function provisionFromLdap(?User $user, string $username, array $ldap): User
    {
        $c = LdapClient::config();
        $attrs = $ldap['attributes'] ?? [];
        $displayName = $attrs[strtolower(strval($c['display_name_attribute'] ?? 'cn'))] ?? null;
        $email = $attrs[strtolower(strval($c['email_attribute'] ?? 'mail'))] ?? null;

        if ($user === null) {
            $user = new User();
            $user->uuid = Uuid::uuid7()->toString();
            $user->username = $username;
            $user->password = null; // LDAP accounts have no local password
            $user->auth_source = 'ldap';
            $user->created_time = time();
            $user->archived = 0;
        } else {
            $user->auth_source = 'ldap';
        }
        $user->ldap_dn = $ldap['dn'] ?? null;
        // profile attributes synced from the directory (per-app columns are
        // detected with canSetProperty so the same code serves every app)
        if ($user->canSetProperty('display_name')) {
            $user->display_name = ($displayName !== null && $displayName !== '') ? $displayName : $username;
        }
        if ($user->canSetProperty('email')) {
            $user->email = $email ?? '';
        }
        // app-specific required columns get safe defaults for LDAP accounts
        if ($user->canSetProperty('role') && empty($user->role)) {
            $user->role = 'user'; // anginger-ai: never provision admins from LDAP
        }
        if ($user->canSetProperty('level') && empty($user->level)) {
            $user->level = 1;
        }
        foreach (['address', 'phone'] as $f) {
            if ($user->canSetProperty($f) && $user->$f === null) {
                $user->$f = '';
            }
        }
        $user->save(false);
        return $user;
    }
}
