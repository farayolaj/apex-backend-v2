<?php

namespace App\Traits;

trait AccountTrait
{
    public function accountProfile()
    {
        if (request()->getMethod() === 'GET') {
            $payload = currentAPIUser()->toArray();
            return sendApiResponse(true, "You've successfully fetched profile", $payload);
        }

        return null;
    }
}