# DX Unanswered Comments

Filter your admin comments that have not received a reply by internal user yet.

## Project Notes
This is a WordPress plugin that will have a separate/hooked links on the top of Comments page in the Administator menu. It will sort the comments that have not been replied by the site member yet.

### Build steps

There's no need for build step and automation. This plugin has single CSS and JS script file and only enqueued in the backend, however we may consider automation in the future.

## Project Documentation
TBD

### Git Branching
We are using different branches when it comes to building new features. However, you should keep in mind:
* `master` branch is the current representation of the production website

### Localhost Project URL
Since this is a WordPress plugin, we don't need a custom project url. Just clone on the plugins directory and you should good to go

### Localhost Debugging
You should have enabled the `WP_DEBUG` set to `TRUE` in your `wp-config.php` file. There are a few other useful debugging options which you can enable:
```
/* Debug Config */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
error_reporting( E_ALL );
ini_set( 'display_errors', 'yes' );
define( 'FS_METHOD', 'direct' ); // Allows you to upload/update themes/plugins/core from your localhost
```

This is the best practice when it comes to localhost work - having the debug turned on. However, there might be some rare cases where the debug notes are so many, that you might need to turn off the debugging for a while. Again, this should be something temporary, as you should have the debugging turned on on your localhost by default.