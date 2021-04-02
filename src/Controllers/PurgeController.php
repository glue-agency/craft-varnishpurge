<?php

namespace GlueAgency\VarnishPurge\Controllers;

use Craft;
use craft\web\Controller;
use Exception;
use GlueAgency\VarnishPurge\Plugin;
use VarnishAdmin\VarnishAdminSocket;

class PurgeController extends Controller
{

    protected $varnish;

    public function init()
    {
        parent::init();

        $this->varnish = new VarnishAdminSocket(
            Craft::parseEnv(Plugin::getInstance()->settings->ip),
            Craft::parseEnv(Plugin::getInstance()->settings->port),
            Craft::parseEnv(Plugin::getInstance()->settings->version)
        );

        if(! empty($secret = Craft::parseEnv(Plugin::getInstance()->settings->secret))) {
            $this->varnish->setSecret($secret);
        }
    }

    public function actionUrl()
    {
        $this->requirePostRequest();

        $url = Craft::$app->request->getBodyParam('url');
        $url = trim($url);

        if(strpos($url, '//') === 0) {
            $url = str_replace("//", "https://", $url);
        }

        if(strpos($url, 'http') !== 0 && strpos($url, '/') !== 0) {
            $url = "https://" . $url;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if($path === null) {
            $path = "/";
        }

        try {
            $this->varnish->connect();
            $this->varnish->purgeUrl($path);
            $this->varnish->quit();

            Craft::$app->session->setFlash('cp-notice', "{$url} purged");
        } catch(Exception $e) {
            Craft::$app->session->setFlash('cp-error', 'URL purge Failed');
        }

        return $this->redirect('/admin/varnishpurge');
    }

    public function actionTags()
    {
        $this->requirePostRequest();

        $tags = Craft::$app->request->getBodyParam('tags');

        // replace newlines for commas
        $tags = preg_replace('/\r|\n/', ',', $tags);

        // remove spaces
        $tags = preg_replace('/\s+/', '', $tags);

        // remove two commas next to each other
        $tags = preg_replace('/,+/', ',', $tags);

        // convert to array
        $tags = explode(',', $tags);

        try {
            $this->varnish->connect();
            $this->varnish->purge('obj.http.X-Cache-Tags' . ' ~ ' . implode('|', $tags));
            $this->varnish->quit();

            Craft::$app->session->setFlash('cp-notice', count($tags) . ' tags purged.');
        } catch(Exception $e) {
            Craft::$app->session->setFlash('cp-error', 'Tag purge Failed');
        }

        return $this->redirect('/admin/varnishpurge/tags');
    }
}
