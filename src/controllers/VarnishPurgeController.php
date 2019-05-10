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
        $varnish->connect();
        $varnish->purgeUrl($_POST['url']);
        $varnish->quit();

        Craft::$app->session->setFlash('notice',"URL purge complete: ".$_POST['url']);

        $this->redirect(Craft::$app->getRequest()->referrer);
    }
}