<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\tests\unit;

use anginger\components\AuthService;
use anginger\models\User;
use PHPUnit\Framework\TestCase;

final class AuthServiceTest extends TestCase
{
    private $tx;

    protected function setUp(): void
    {
        $this->tx = \Yii::$app->db->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->tx->rollBack();
    }

    public function testAttemptWithWrongPasswordIsInvalid(): void
    {
        [$user, $error] = AuthService::attempt('admin', 'definitely-wrong');
        $this->assertNull($user);
        $this->assertSame(AuthService::ERROR_INVALID, $error);
    }

    public function testAttemptWithLocalPasswordSucceedsAndUpdatesLastLogin(): void
    {
        [$user, $error] = AuthService::attempt('admin', 'admin');
        $this->assertNull($error);
        $this->assertNotNull($user);
        $this->assertSame('admin', $user->username);
        $this->assertNotNull($user->last_login_time);
    }

    public function testArchivedAccountIsBlocked(): void
    {
        $user = User::findOne(['username' => 'admin']);
        $user->archived = 1;
        $user->save(false, ['archived']);

        [$u, $error] = AuthService::attempt('admin', 'admin');
        $this->assertNull($u);
        $this->assertSame(AuthService::ERROR_ARCHIVED, $error);
    }

    public function testLdapAccountHasNoLocalPassword(): void
    {
        $user = new User();
        $user->uuid = \Ramsey\Uuid\Uuid::uuid7()->toString();
        $user->username = 'ldap-only-user';
        $user->password = null;
        $user->auth_source = 'ldap';
        $user->level = 1;
        $user->display_name = 'LDAP Only';
        $user->address = '';
        $user->email = '';
        $user->phone = '';
        $user->archived = 0;
        $user->save(false);

        [$u, $error] = AuthService::attempt('ldap-only-user', 'whatever');
        $this->assertNull($u);
        $this->assertSame(AuthService::ERROR_INVALID, $error);
    }
}
