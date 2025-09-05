<?php
if(!function_exists('generateBaseUrl')){
    function generateBaseUrl(string $url): string
    {
        return base_url("v1/web/$url");
    }
}
