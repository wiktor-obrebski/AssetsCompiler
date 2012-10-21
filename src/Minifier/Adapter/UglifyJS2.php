<?php

namespace Minifier\Adapter;

/**
 * Minifier adapter interface
 *
 * @author Wiktor Obrębski
 */
class UglifyJS2 implements JsAdapterInterface
{
    protected $compiler_path;
    protected $flags;

    public function __construct( $options = null )
    {
        $this->compiler_path =
            isset( $options['exec'] ) ? $options['exec'] : 'uglifyjs2';

        $this->flags = $options['flags'];
    }

    public function compileJs( $files_pathes, $output_file )
    {
        $cmd = sprintf('%s %s', $this->compiler_path,
                 implode( ' ', $files_pathes ) );
        foreach ($this->flags as $key => $value) {
            $cmd .= ' ' . $key . ' ' . $value;
        }
        $cmd .= '-o ' . $output_file;
        exec( $cmd );
        return true;
    }
}