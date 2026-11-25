<?php
/**
 * Copyright 2026 Anginger
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\controllers;

use anginger\components\AuthService;
use anginger\components\LdapClient;
use anginger\models\User;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * Session authentication.
 *
 *   GET    /api/authentic/session -> current user (200) or 401
 *   POST   /api/authentic/session -> login, 201 / 401 / 423 (archived)
 *   DELETE /api/authentic/session -> logout, 204 or 401
 *   GET    /api/authentic/ldap    -> {"enabled": bool} LDAP/AD status (public, for the login page)
 */
class AuthenticController extends BaseController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'session' => ['GET', 'POST', 'DELETE'],
                    'ldap' => ['GET'],
                ],
            ],
        ]);
    }

    /**
     * Public LDAP status so the login page can show an "LDAP/AD 登入已啟用" hint.
     * Never exposes bind credentials.
     */
    public function actionLdap(): Response
    {
        $c = LdapClient::config();
        unset($c); // never expose internal host/bind details
        return $this->asJson(['enabled' => LdapClient::enabled()]);
    }

    public function actionSession(): Response
    {
        $request = Yii::$app->request;
        switch ($request->method) {
            case 'GET':
                $identity = Yii::$app->user->identity;
                if ($identity instanceof User) {
                    return $this->asJson($identity);
                }
                return $this->unauthorized();

            case 'POST':
                $form = $request->getBodyParams();
                if (!isset($form['username']) || !isset($form['password'])
                    || !is_scalar($form['username']) || !is_scalar($form['password'])) {
                    return $this->badRequest(); // arrays/objects would TypeError/warn on strval()
                }
                $cache = Yii::$app->cache;
                $failKey = 'login-fail.' . preg_replace('/[^a-zA-Z0-9_.]/', '_', $request->userIP . '.' . strtolower(strval($form['username'])));
                // per-IP global cap too, so attackers cannot spray many usernames from one IP
                $ipKey = 'login-fail-ip.' . preg_replace('/[^a-zA-Z0-9_.]/', '_', (string)$request->userIP);
                if ($cache !== null && (int)$cache->get($ipKey) >= 20) {
                    Yii::$app->response->setStatusCode(429);
                    return $this->asJson(['message' => '嘗試次數過多，請 15 分鐘後再試']);
                }
                if ($cache !== null && (int)$cache->get($failKey) >= 5) {
                    Yii::$app->response->setStatusCode(429);
                    return $this->asJson(['message' => '嘗試次數過多，請 15 分鐘後再試']);
                }
                [$user, $error] = AuthService::attempt(strval($form['username']), strval($form['password']));
                if ($user === null && $cache !== null) {
                    $cache->set($failKey, ((int)$cache->get($failKey)) + 1, 900);
                    $cache->set($ipKey, ((int)$cache->get($ipKey)) + 1, 900);
                } elseif ($cache !== null) {
                    $cache->delete($failKey);
                }
                if ($user === null) {
                    if ($error === AuthService::ERROR_ARCHIVED) {
                        Yii::$app->response->setStatusCode(423);
                        return $this->asJson(['message' => '帳號已封存，請聯繫管理員']);
                    }
                    Yii::$app->response->setStatusCode(401);
                    return $this->asJson(['message' => '帳號或密碼錯誤']);
                }
                Yii::$app->user->login($user);
                return $this->emptyResponse(201);

            case 'DELETE':
                if (Yii::$app->user->isGuest) {
                    return $this->unauthorized();
                }
                Yii::$app->user->logout();
                return $this->emptyResponse(204);
        }
        return $this->badRequest();
    }
}
