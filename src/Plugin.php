<?php

namespace GlueAgency\VarnishPurge;

use GlueAgency\VarnishPurge\Controllers\VarnishPurgeController;
use GlueAgency\VarnishPurge\Models\Settings;
use craft\events\RegisterCpNavItemsEvent;
use craft\web\twig\variables\Cp;
use yii\base\Event;
use craft\helpers\UrlHelper;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;
use yii\web\NotFoundHttpException;
use VarnishAdmin;
use Craft;

class Plugin extends \craft\base\Plugin
{
    public $controllerNamespace = '\GlueAgency\VarnishPurge\Controllers';
    public $hasCpSettings = true;
    public $hasCpSection = true;
    public $controllerMap = [
        'varnishPurge' => VarnishPurgeController::class,
    ];

    public function init()
    {
        Craft::setAlias('@/GlueAgency/VarnishPurge', __DIR__);

        parent::init();

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['varnishpurge/purge'] = 'varnishpurge/varnish-purge';
            }
        );
    }

    protected function createSettingsModel()
    {
        return new Settings();
    }

    protected function settingsHtml()
    {
        return \Craft::$app->getView()->renderTemplate('varnishpurge/settings', [
            'settings' => $this->getSettings()
        ]);
    }
}
