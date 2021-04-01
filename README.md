# Craft VarnishPurge

Allow purging of urls and tags from the Craft CMS interface.

By default only url purging is enabled for non-admins. Enabling tag purging for other users or groups can be done on the permissions page.

## Configuration

You can either use a dedicated config file, or use environment variables to keep secrets out of version control.

### Config file

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

### Env variables

Define your environment variables in the plugins settings page & in your .env file
