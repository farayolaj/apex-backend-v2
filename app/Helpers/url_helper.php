<?php
if(!function_exists('generateBaseUrl')){
    function generateBaseUrl(string $url): string
    {
        return base_url("v1/web/$url");
    }
}

function encodeRoomId(string $roomId): string
{
    return base64_encode(encryptData($roomId));
}

function decodeRoomId(string $hash): string
{
    return decryptData(base64_decode($hash));
}

function getMeetingEndedUrl(string $encodedRoomId): string
{
    return base_url('/v1/webinars/' . $encodedRoomId . '/end');
}

function getRecordingReadyUrl(): string
{
    return base_url('/v1/webinars/recordings');
}
