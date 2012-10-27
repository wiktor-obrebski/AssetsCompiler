<?php
return array(
    'minifier'    => array(
        'js_adapter'           => array(
            'class'   => 'AssetsCompiler\Minifier\Adapter\Minify',
            'options' => array(
            ),
        ),
        'css_adapter'           => array(
            'class'   => 'AssetsCompiler\Minifier\Adapter\Minify',
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
            'AssetsCompiler\Controller\AssetsCompilerController' => 'AssetsCompiler\Controller\AssetsCompilerController',
        ),
    ),

    'view_helpers' => array(
        'invokables' => array(
            //label, that support automatic translation
            'headScript' => 'AssetsCompiler\Minifier\View\Helper\HeadScript',
            'headLink'   => 'AssetsCompiler\Minifier\View\Helper\HeadLink',
        ),
        'factories' => array(
            'prependJsBundle'  => array( 'AssetsCompiler\Minifier\Factory', 'createJsHelper' ),
            'prependCssBundle' => array( 'AssetsCompiler\Minifier\Factory', 'createCssHelper' ),
        ),
    ),

    'console' => array(
        'router' => array(
            'routes' => array(
                'minify' => array(
                    'options' => array(
                        'route'    => 'minify [--force|-f]',
                        'defaults' => array(
                            'controller' => 'AssetsCompiler\Controller\AssetsCompilerController',
                            'action'     => 'minify',
                        ),
                    ),
                ),
            ),
        ),
    ),
);
