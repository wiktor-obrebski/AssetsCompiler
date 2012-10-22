<?php

namespace AssetsCompiler\Minifier\Adapter;

/**
 * Minifier adapter interface
 *
 * @author Wiktor Obrębski
 */
class UglifyJS2 implements JsAdapterInterface
{
    protected $exec;
    protected $js_flags;

    public function __construct( $options = null )
    {
        $this->exec =
            empty( $options['exec'] ) ? 'uglifyjs2' : $options['exec'];
        $this->js_flags = $options['js'];
    }

    public function compileJs( $files_pathes, $output_file )
    {
        $cmd = sprintf('%s %s', $this->exec,
                 implode( ' ', $files_pathes ) );
        foreach ($this->js_flags as $key => $value) {
            $cmd .= ' ' . $key . ' ' . $value;
        }
        $cmd .= ' -o ' . $output_file;
        exec( $cmd );
        return true;
    }
}