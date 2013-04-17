<?php

namespace AssetsCompiler\Minifier\Adapter;

use MatthiasMullie\Minify\CSS as MinifyCSS;
use MatthiasMullie\Minify\JS as MinifyJS;

/**
 * This is a default minifier adapter interface, it use php only so not needed
 * any additional library downloads and env configurations.
 *
 * @author Wiktor Obrębski
 */
class Minify implements JsAdapterInterface, CssAdapterInterface
{
    protected $cssOptions = MinifyCSS::ALL;
    protected $jsOptions = MinifyJS::ALL;

    public function __construct( $options = null )
    {
        if( !empty( $options['css'] ) ) $this->cssOptions = $options['css'];
        if( !empty( $options['js'] ) ) $this->cssOptions = $options['js'];
    }

    private function generalCompile( $minifier, $files_pathes, $output_file, $options )
    {
        if( empty( $files_pathes ) || empty( $output_file ) ) return $this;
        foreach( $files_pathes as $file ) {
            $minifier->add( $file );
        }
        $minifier->minify( $output_file, $options );
        return true;
    }

    public function compileJs( $files_pathes, $output_file )
    {
        return $this->generalCompile( new MinifyJS(), $files_pathes, $output_file, $this->jsOptions );
    }

    public function compileCss( $files_pathes, $output_file )
    {
        return $this->generalCompile( new MinifyCSS(), $files_pathes, $output_file, $this->cssOptions );
    }

}