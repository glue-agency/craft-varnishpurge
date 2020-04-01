<?php

namespace GlueAgency\VarnishPurge\Controllers;

use Craft;
use craft\base\Plugin;
use craft\web\Controller;
use VarnishAdmin\VarnishAdminSocket;

class VarnishPurgeController extends Controller
{
    public function actionIndex()
    {
        $ip = \GlueAgency\VarnishPurge\Plugin::getInstance()->settings->ip;
        $port = \GlueAgency\VarnishPurge\Plugin::getInstance()->settings->port;
        $version = \GlueAgency\VarnishPurge\Plugin::getInstance()->settings->version;
        $secret = \GlueAgency\VarnishPurge\Plugin::getInstance()->settings->secret;

        $varnish = new VarnishAdminSocket($ip, $port, $version);
        if(!empty($secret)){
            $varnish->setSecret($secret);
        }

        $url = $_POST['url'];
        $url = trim($url);

        if (strpos($url, '//') === 0) {
            $url = str_replace("//","https://",$url);
        }

        if (strpos($url, 'http') !== 0 && strpos($url, '/') !== 0) {
            $url = "https://".$url;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if($path === NULL){
            $path = "/";
        }

        $varnish->connect();
        $varnish->purgeUrl($path);
        $varnish->quit();

        Craft::$app->session->setFlash('notice',"URL purge complete: ".$_POST['url']);

        $this->redirect("/admin/varnishpurge?notice=".urlencode("URL purge complete: ".$_POST['url']));
    }
}