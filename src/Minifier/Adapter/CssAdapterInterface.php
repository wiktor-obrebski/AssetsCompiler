<?php

namespace Minifier\Adapter;

/**
 * Minifier adapter interface
 */
interface CssAdapterInterface
{
    function compileCss( $files_pathes, $output_file );
}