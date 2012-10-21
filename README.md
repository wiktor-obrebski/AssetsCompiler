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
Easiest way is copy `.../Minifier/config/minifier.local.php.dist` file to
`config/autoload/minifier.local.php`. Next you need proper fill configuration data
inside `minifier` key. Most options have default values, you can found their in
`.../Minifier/config/module.config.php` file.

 - *adapter* - adapter class using to combine and minify files, can be ommit
 - *options* - array of options that will be send to adapter class constructor
 - *development_mode* - boolean flag, if true our view helper will attach all files included
                        in bundles, one by one. If setted to true - it will attach one combined
                        file per bundle
 - *persistent_file*  - bundles need store some data, it is done in xml file, you can specify
                        where this file will be save, relative to project root
 - *public_dir*       - you can define your public directory here, relative to project root
 - *bundles*          - bundles configuration, can has *js* and *css* keys


 Both, *js* and *css* configurations you can define by using this options:

 - *output_dir*     - output directory where bundles of this type will be saved, relative to
                        project public directory
 - *list*           - list of bundles

List of bundles is a array where key is the bundle name, value is options array:

 - *filename*       - the bundle output filename pattern - by default it is '%s-%s'. First argument
                       is the bundle name, second - bundle included files md5 value
 - *sources*        - array of the bundle included files, pathes relative to project public directory