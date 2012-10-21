<?php

namespace Minifier\Adapter;

/**
 * Minifier adapter interface
 */
interface JsAdapterInterface
{
    function compileJs( $files_pathes, $output_file );
}