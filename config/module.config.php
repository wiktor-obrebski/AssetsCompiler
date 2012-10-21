<?php
return array(
    'minifier'    => array(
        'js_adapter'           => array(
            'class'   => 'Minifier\Adapter\Minify',
            'options' => array(
            ),
        ),
        'css_adapter'           => array(
            'class'   => 'Minifier\Adapter\Minify',
            'options' => array(
            ),
        ),
        'development_mode'       => true,
        //persistent file, some data will be stored there in
        //xml format, need have write access
        'persistent_file'   => './data/minifier/config.xml',
        'public_dir'        => './public',
        'bundles'   => array(
            'js'    => array(),
            'css'    => array(),
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
