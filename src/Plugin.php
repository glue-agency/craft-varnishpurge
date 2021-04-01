<?php

namespace GlueAgency\VarnishPurge;

use craft\events\RegisterUserPermissionsEvent;
use craft\services\UserPermissions;
use GlueAgency\VarnishPurge\Controllers\PurgeController;
use GlueAgency\VarnishPurge\Models\Settings;
use yii\base\Event;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;
use Craft;

class Plugin extends \craft\base\Plugin
{
    public $controllerNamespace = '\GlueAgency\VarnishPurge\Controllers';
    public $hasCpSettings = true;
    public $hasCpSection = true;

    public $controllerMap = [
        'purge' => PurgeController::class,
    ];

    public function init()
    {
        Craft::setAlias('@/GlueAgency/VarnishPurge', __DIR__);

        parent::init();

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['varnishpurge/purge'] = 'varnishpurge/purge/url';
                $event->rules['varnishpurge/purge/tags'] = 'varnishpurge/purge/tags';
            }
        );

        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function (RegisterUserPermissionsEvent $event) {
                $event->permissions[Craft::t('varnishpurge', 'VarnishPurge')] = [
                    'varnishpurge:tags' => [
                        'label' => Craft::t('varnishpurge', 'Purge tags'),
                    ],
                ];
            }
        );
    }

    protected function createSettingsModel()
    {
        return new Settings();
    }

    protected function settingsHtml()
    {
        return \Craft::$app->getView()->renderTemplate('varnishpurge/_settings', [
            'settings' => $this->getSettings()
        ]);
    }
}
