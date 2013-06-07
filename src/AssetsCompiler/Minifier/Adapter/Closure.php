<?php

namespace AssetsCompiler\Minifier\Adapter;

/**
 * Minifier adapter interface
 *
 * @author Wiktor Obrębski
 */
class Closure implements JsAdapterInterface
{
    protected $exec;
    protected $js_flags;

    public function __construct( $options = null )
    {
        $this->exec = $options['exec'];
        $this->js_flags = $options['js'];
    }

    public function compileJs( $files_pathes, $output_file )
    {
        $cmds = array_map( function( $file ){
            return sprintf('--js %s', $file );
        }, $files_pathes );
        $cmd = sprintf('java -jar %s %s', $this->exec,
                 implode( ' ', $cmds ) );
        foreach ($this->js_flags as $key => $value_pack) {
            if( is_scalar( $value_pack ) ) $value_pack = array( $value_pack );
            foreach ($value_pack as $value) {
                $cmd .= ' ' . $key . ' ' . $value;
            }
        }
        $cmd .= ' --js_output_file ' . $output_file;
        exec( $cmd );
        return true;
    }
}