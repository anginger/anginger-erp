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

final class UserTest extends TestCase
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

    public function testSeededAdminAuthenticatesWithLegacySha256AndIsRehashed(): void
    {
        $user = User::findOne(['username' => 'admin']);
        $this->assertNotNull($user);
        // seed ships a legacy unsalted sha256 hash; it must still verify...
        $this->assertTrue(AuthService::validatePassword($user, 'admin'));
        // ...and be transparently upgraded to bcrypt
        $this->assertStringStartsWith('$2', $user->password);
        $this->assertTrue(AuthService::validatePassword($user, 'admin'));
        $this->assertFalse(AuthService::validatePassword($user, 'wrong-password'));
    }

    public function testArchivedUserLosesIdentity(): void
    {
        $user = User::findOne(['username' => 'admin']);
        $this->assertNotNull(User::findIdentity($user->uuid));
        $user->archived = 1;
        $user->save(false, ['archived']);
        $this->assertNull(User::findIdentity($user->uuid));
    }

    public function testFieldsNeverLeakSecrets(): void
    {
        $user = User::findOne(['username' => 'admin']);
        $fields = $user->fields();
        $this->assertArrayNotHasKey('password', $fields);
        $this->assertArrayNotHasKey('ldap_dn', $fields);
        $json = json_encode($user);
        $this->assertStringNotContainsString('8c6976e5', $json);
        $this->assertStringNotContainsString('"password"', $json);
    }

    public function testIsAdminFollowsLevel(): void
    {
        $user = User::findOne(['username' => 'admin']);
        $this->assertTrue($user->isAdmin()); // seeded admin points at the Admin level
        $user->level = 9999; // unknown level -> not admin
        $this->assertFalse($user->isAdmin());
    }
}
