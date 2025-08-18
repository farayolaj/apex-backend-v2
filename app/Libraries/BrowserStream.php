<?php

namespace App\Libraries;

class BrowserStream
{
    public static function enable(){
        @ini_set('zlib.output_compression', 'Off');
        @ini_set('output_buffering', 'Off');
        @ini_set('output_handler', '');
        if( function_exists('apache_setenv') ) @apache_setenv('no-gzip', 1);
        header("Content-Type: text/event-stream");
        header("Cache-Control: no-cache");
        header("Connection: keep-alive");
    }

    public static function startPing(){
        $curDate = date(DATE_ISO8601);
        echo "event: ping\n", 'data: '. $curDate, "\n\n";
    }

    public static function linePing(){
        echo "event: lineSpace\n", 'data: ---------------------------------' , "\n\n";
    }

    public static function endPing(){
        $curDate = date(DATE_ISO8601);
        echo "event: done\n", 'data: '. $curDate, "\n\n";
        self::flushBuffer();
    }

    public static function put(string $str){
        $curDate = date(DATE_ISO8601);
        echo "event: ping\n", 'data: '. $str, "\n\n";
    }

    public static function flushFewBuffer(){
        @ob_end_flush();
        @flush();
    }

    public static function flushBuffer(){
        // flush the output buffer and send echoed messages to the browser
        while(ob_get_level() > 0) {
            @ob_end_flush();
        }
        // break the loop if the client aborted the connection (closed the page)
        if(connection_aborted()) {
            exit;
        }
        @flush();
    }
}