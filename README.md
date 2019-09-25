# WP Theme Framework

## 1. Install
- Add to `composer.php`:<br>
```
{
   "repositories": [
     {
       "type": "vcs",
       "url": "git@bitbucket.org:simple-solution/wp-interaction-framework.git"
     }
   ],
   "require": {
     "simple-solution/wp-interaction-framework": ">=1.0",
   },
   "autoload": {
     "psr-4": {
       "ssoFramework\\": "vendor/simple-solution/wp-interaction-framework/"
     }
   }
 }
```
- Add `autoload.php` to `wp-content/theme/{YOUR_THEME}/function.php`
```
require_once realpath( dirname( __FILE__, 4 ) ) . '/vendor/autoload.php';
```
