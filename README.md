#AssetsCompiler

AssetsCompiler is module for ZF2 that help you managment your project static files.
For now it is responsible for combine and minifying CSS and Javascript files. It is
in development state and in near future more features will be released.

It provides zend console route script to compile selected files as bundles and view helpers
to automatically attaching bundles in your views. It can add files md5 to bundle file
names - to avoid browser file caching after changes.

## Installation

 1. Add `"psychowico/assets-compiler": "dev-master"` to your `composer.json` file and run `php composer.phar update`.
 2. Add *AssetsCompiler* to your `config/application.config.php` file under the modules key.

## Minifier

Minifier classes provides zend console route script to compile selected files as bundles
and view helpers to automatically attaching bundles in your views. It can add files md5
to bundle file names - to avoid browser file caching after changes.

### Configuration

To make Minifier working you need declare your bundles lists and set your local directories.
Easiest way is copy `.../Minifier/config/minifier.local.php.dist` file
to `config/autoload/minifier.local.php`. Next you need proper fill configuration data
inside `minifier` key. Most options have default values, you can found their
in `.../Minifier/config/module.config.php` file.

 - *js_adapter* - js adapter configuration array
   - *class*    - adapter class (probably from *\AssetsCompiler\Minifier\Adapter\\*)
   - *options*  - array of adapter options, you can read about them in "Adapters" section
 - *css_adapter* - css adapter configuration array, like above

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

### How to use

If you had configured your bundles and need combine and minify them just
run your *public/index.php* file with cmds:

`php index.php minify`

It will do all job. To make it more easier and more intuitive you can create
*bin* folder in your project root directory. There create `minify` file with
the following content:

```php

### Adapters

By default *Minifier* code use *..\Adapter\Minify* adapter. It use
[matthiasmullie/minify](https://github.com/matthiasmullie/minify) php library, so you do not need
install any additional stuff to start working. But if you have more needs you can use others
prepared adapter or write you own.
We provided three aditional adapters:
 1. *\Adapter\Closure* - using google [Closure](https://developers.google.com/closure/) library, can compile only js
 2. *\Adapter\UglifyJS2* - using [UglifyJS2](https://github.com/mishoo/UglifyJS2) library, only for js
 3. *\Adapter\YUI*  - using [YUI Compressor](http://developer.yahoo.com/yui/compressor/) library from yahoo, can be use with js and css

They all external tools and need some configuration to start working with them. All can be configured
in very similar way. In your configuration *'minifier'* array you can add two keys, *js_adapter* for
javascript adapter and css_adapter for css adapter. They both should be arrays like in example:

```php
...
  'minifier'  => array(
    'js_adapter'  => array(
      'class'   => 'AssetsCompiler\Minifier\Adapter\UglifyJS2',
      'options' => array(
        'exec'  => '/uglifyjs2/path/or/link/',
        'flags' => array(
          .. //additional compiler flags
        ),
      ),
    ),
  )
```
In similar way you can configure *css_adapter*, just remember that some adapters support only js!
In exec param you need give your local path to library that you want use (Closure/UglifyJS2/YUI).
In flags you can add additional parameters that will be send to compiler, for sample if you define
*flags* like this:

```php
...
  'flags' => array(
    '--comments' => '',
    '--define'   => 'DEBUG=false',
  ),
...
```

uglify will be called in something like
`uglifyjs2 file1.js file2.js -o output_file.js --comments --define DEBUG=false`.

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