<?php

namespace GlueAgency\VarnishPurge;

use Craft;
use craft\helpers\App;
use craft\services\Elements;
use craft\elements\Entry;
use craft\events\RegisterUserPermissionsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\ElementHelper;
use craft\services\UserPermissions;
use craft\web\UrlManager;
use Exception;
use GlueAgency\VarnishPurge\Controllers\PurgeController;
use GlueAgency\VarnishPurge\Models\Settings;
use VarnishAdmin\VarnishAdminSocket;
use yii\base\Event;

class Plugin extends \craft\base\Plugin
{

    protected $varnish;

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

        Event::on(
            Elements::class,
            Elements::EVENT_AFTER_SAVE_ELEMENT,
            function (Event $event) {
                if ($event->element instanceof Entry) {
                    $entry = $event->element;

                    if (
                        !ElementHelper::isDraftOrRevision($entry) &&
                        App::parseEnv(Plugin::getInstance()->settings->ip) &&
                        App::parseEnv(Plugin::getInstance()->settings->port) &&
                        App::parseEnv(Plugin::getInstance()->settings->version)
                    ) {
                        $sectionId = $entry->sectionId;
                        $sectionHandle = Craft::$app->sections->getSectionById($sectionId)->handle;

                        $sectionsString = Craft::parseEnv(Plugin::getInstance()->settings->sections);
                        $sectionsArray = explode(',', $sectionsString);
                        $sections = array_map('trim', $sectionsArray);

                        if (
                            !empty($entry->url) &&
                            in_array($sectionHandle, $sections)
                        ) {
                            $url = $entry->url . "$";
                            $path = parse_url($url, PHP_URL_PATH);

                            if($path === null) {
                                $path = "/";
                            }

                            try {
                                try {
                                    $this->varnish = new VarnishAdminSocket(
                                        App::parseEnv(Plugin::getInstance()->settings->ip),
                                        App::parseEnv(Plugin::getInstance()->settings->port),
                                        App::parseEnv(Plugin::getInstance()->settings->version)
                                    );

                                    if(! empty($secret = App::env(Plugin::getInstance()->settings->secret))) {
                                        $this->varnish->setSecret($secret);
                                    }

                                } catch (Exception $e) {}

                                if (!empty($this->varnish)) {
                                    $this->varnish->connect();
                                    $this->varnish->purgeUrl($path);
                                    $this->varnish->quit();
                                    Craft::info('URL purge succeeded for '. $entry->url, __METHOD__);
                                }
                            } catch(Exception $e) {
                                Craft::error('URL purge failed for '. $entry->url, __METHOD__);
                            }
                        }
                    }
                }
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
