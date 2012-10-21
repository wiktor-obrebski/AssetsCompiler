#Minifier

Minifier is a moudle that is responsible for combine and minifying CSS and Javascript files.
It provides zend console route script to compile selected files as bundles and view helpers
to automatically include bundles in your views.
It can add files md5 to bundle file names - to avoid browser file caching after changes.

## Installation

 1. Add `"minifier/minifier": "dev-master"` to your `composer.json` file and run `php composer.phar update`.