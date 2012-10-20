<?php

namespace Minifier\Adapter;

use Minifier\Adapter\AdapterInterface;
use MinifyCSS;
use MinifyJS;

/**
 * Minifier adapter interface
 *
 * @author Wiktor Obrębski
 */
class Minify implements AdapterInterface
{
    protected $cssOptions = MinifyCSS::ALL;
    protected $jsOptions = MinifyJS::ALL;

    public function __construct( $options = null )
    {
        if( !empty( $options ) ) {
            $this->cssOptions = empty( $options['css_options'] ) ? MinifyCSS::ALL :
                                $options['css_options'];
            $this->jsOptions = empty( $options['js_options'] ) ? MinifyJS::ALL :
                                $options['js_options'];
        }
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
    public function compile( $files_pathes, $output_file, $mode )
    {
        switch ( $mode ) {
            case AdapterInterface::MODE_CSS:
                return $this->generalCompile( new MinifyCSS(), $files_pathes, $output_file, $this->cssOptions );
            case AdapterInterface::MODE_JS:
                return $this->generalCompile( new MinifyJS(), $files_pathes, $output_file, $this->jsOptions );
        }
        return false;
    }
}