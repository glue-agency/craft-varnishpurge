# Craft VarnishPurge

## Configuration

Create a `varnishpurge.php` file in your config folder.

```
<?php

return [
    'ip' => '127.0.0.1',
    'port' => '6082',
    'version' => '5.0.0',
    'secret' => '[YOUR-PRE-SHARED-SECRET-AS-SETUP-ON-VARNISH-SERVER]',
];
```
