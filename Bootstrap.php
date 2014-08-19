<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dektrium\user;

use yii\base\BootstrapInterface;
use yii\web\GroupUrlRule;

/**
 * Bootstrap class registers module and user application component. It also creates some url rules which will be applied
 * when UrlManager.enablePrettyUrl is enabled.
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        if ($app->hasModule('user')) {
            $identityClass = $app->getModule('user')->manager->userClass;
        } else {
            $app->setModule('user', [
                'class' => 'dektrium\user\Module'
            ]);
            $identityClass = 'dektrium\user\models\User';
        }

        if ($app instanceof \yii\console\Application) {
            $app->getModule('user')->controllerNamespace = 'dektrium\user\commands';
        } else {
            $app->set('user', [
                'class'           => $app->getModule('user')->webUserClass,
                'enableAutoLogin' => true,
                'loginUrl'        => ['/user/security/login'],
                'identityClass'   => $identityClass
            ]);

            $app->get('urlManager')->rules[] = new GroupUrlRule([
                'prefix' => 'user',
                'rules' => [
                    '<id:\d+>'                     => 'profile/show',
                    '<action:(login|logout)>'      => 'security/<action>',
                    '<action:(register|resend)>'   => 'registration/<action>',
                    'confirm/<id:\d+>/<token:\w+>' => 'registration/confirm',
                    'forgot'                       => 'recovery/request',
                    'recover/<id:\d+>/<token:\w+>' => 'recovery/reset',
                    'settings/<action:\w+>'        => 'settings/<action>'
                ]
            ]);

            if (!$app->has('authClientCollection')) {
                $app->set('authClientCollection', [
                    'class' => 'yii\authclient\Collection',
                ]);
            }
        }

        $app->get('i18n')->translations['user*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'basePath' => __DIR__ . '/messages',
        ];
    }
}