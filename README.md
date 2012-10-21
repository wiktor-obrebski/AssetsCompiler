#Minifier

Minifier is a moudle that is responsible for combine and minifying CSS and Javascript files.
It provides zend console route script to compile selected files as bundles and view helpers
to automatically attaching bundles in your views.
It can add files md5 to bundle file names - to avoid browser file caching after changes.

## Installation

 1. Add `"minifier/minifier": "dev-master"` to your `composer.json` file and run `php composer.phar update`.
 2. Add Minifier to your `config/application.config.php` file under the modules key.

## Configuration

To make Minifier working you need declare your bundles lists and set your local directories.
Easiest way is copy `.../Minifier/config/minifier.local.php.dist` file
to `config/autoload/minifier.local.php`. Next you need proper fill configuration data
inside `minifier` key. Most options have default values, you can found their
in `.../Minifier/config/module.config.php` file.

 - *adapter* - adapter class using to combine and minify files, can be ommit
 - *options* - array of options that will be send to adapter class constructor
 - *development_mode* - boolean flag, if true our view helper will attach all files included
                        in bundles, one by one. If setted to true - it will attach one combined
                        file per bundle
 - *persistent_file*  - bundles need store some data, it is done in xml file, you can specify
                        where this file will be save, relative to project root
 - *public_dir*       - you can define your public directory here, relative to project root
 - *bundles*          - bundles configuration, can have *js* and *css* keys

Both, *js* and *css* configurations you can define by using this options:

 - *output_dir*     - output directory where bundles of this type will be saved, relative to
                        project public directory
 - *list*           - list of bundles

List of bundles is a array where key is the bundle name, value is options array:

 - *filename*       - the bundle output filename pattern - by default it is '%s-%s'. First argument
                       is the bundle name, second - bundle included files md5 value
 - *sources*        - array of the bundle included files, pathes relative to project public directory

## How to use

If you had configured your bundles and need combine and minify them just
run your *public/index.php* file with cmds:

`php index.php minify`

It will do all job. To make it more easier and more intuitive you can create
*bin* folder in your project root directory. There create `minify` file with
the following content:

```php

#!/usr/bin/php
<?php

$_SERVER['argv'] = $argv = array_merge( $argv, array( 'minify' ) );
$_SERVER['argc'] = $argc = count($argv);

require __DIR__ . '/../public/index.php';

```

Do not forget to give file execute access!
Now, when you are in your project root directory, you can just type:
`./bin/minify`

To make life even easier you should consider use Minifer view helpers.
Minfier provides two view helpers for you. You can use in very similiar way to
adding normal, static js and css files.

```php

<?php echo $this->headLink()
                ->prependStylesheet($this->basePath() . '/css/bootstrap-responsive.min.css')
                ->prependStylesheet($this->basePath() . '/css/bootstrap.min.css')
                ->appendBundle('css_bundle')

echo $this->headScript()->prependFile($this->basePath() . '/js/bootstrap.min.js')
                        ->prependFile($this->basePath() . '/js/jquery.min.js')
                        ->prependBundle('js_bundle');
        ?>
```

Bundle names are keys from above defined configuration,
for sample: *'minifier'->'bundles'->'js'->'list'->'js_bundle'*.

Analogously you can use *prependJsBundle*, *prependCssBundle* methods:

```php
<?php
$this->prependJsBundle('js_bundle');
echo $this->headScript();
?>
```

Remember that entries will be really rendered when you will echo zf2 *headScript* view helper
or *headLink* in *prependCssBundle* case.

Now you can simply control that zend attach js/css files list, or just bundles files - but
changing **development_mode** flat in your *Minifier* configuration.