<?php

namespace GlueAgency\VarnishPurge\Models;

use craft\base\Model;

class Settings extends Model
{
    public $ip = '127.0.0.1';
    public $port = 6082;
    public $version = '5.0.0';
    public $secret = '';
    public $sections = '';

    public function rules(): array
    {
        return [
            [['ip', 'port', 'version'], 'required'],
        ];
    }
}
