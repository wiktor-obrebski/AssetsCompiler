<?php
return array(
    'minifier'    => array(
        'adapter'           => 'Minifier\Adapter\Minify',
        //adapter constructor will be called with this array
        'options'   => array(
        ),
        'development_mode'       => true,
        //persistent file, some data will be stored there in
        //xml format, need have write access
        'persistent_file'   => __DIR__ . '/../../../data/minifier/config.xml',
        'public_dir'        => __DIR__ . '/../../../public',
        'bundles'   => array(
            'js'    => array(
                //relative to /public dir
                'output_dir'     => '/js/bundles',
                'list'              => array(
                    //keys will be used to generate file name,
                    //in this case:
                    //public/js/bundles/js_bundle-{md5-hash}.js
                    'js_bundle' => array(
                        //how output file name should be generated,
                        //first arg is 'name', second is file md5,
                        //default is '%s-%s', so this is only sample.
                        //extension will be added automatically
                        'filename'  => 'bundle_%s-%s',
                        //if only one element at list - can be give as string,
                        'sources' => array(
                            '/js/test.js',
                            '/js/compiled_coffee/test321.js',
                            '/js/compiled_coffee/my_dir/in_test.js',
                        ),
                    ),
                ),
            ),
            'css'    => array(
                'output_dir' => '/css/bundles',
                'list'       => array(
                    'css_bundle' => array(
                        'sources' => '/css/style.css',
                    ),
                ),
            ),
        ),
    ),

    'service_manager' => array(
        'factories' => array(
            'minifier'       =>  'Minifier\Factory',
        ),
    ),

    'controllers' => array(
        'invokables' => array(
            'Minifier\Controller\MinifierController' => 'Minifier\Controller\MinifierController',
        ),
    ),

    'view_helpers' => array(
        'invokables' => array(
            //label, that support automatic translation
            'headScript' => 'Minifier\View\Helper\HeadScript',
            'headLink'   => 'Minifier\View\Helper\HeadLink',
        ),
        'factories' => array(
            'prependJsBundle'  => array( 'Minifier\Factory', 'createJsHelper' ),
            'prependCssBundle' => array( 'Minifier\Factory', 'createCssHelper' ),
        ),
    ),

    'console' => array(
        'router' => array(
            'routes' => array(
                'minify' => array(
                    'options' => array(
                        'route'    => 'minify [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'Minifier\Controller\MinifierController',
                            'action'     => 'minify',
                        ),
                    ),
                ),
            ),
        ),
    ),
);
