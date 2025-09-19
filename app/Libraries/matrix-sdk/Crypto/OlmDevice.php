<?php

namespace MatrixSdk\Crypto;

use MatrixSdk\MatrixHttpApi;

/**
 * OlmDevice stub for typehinting
 *
 * @package MatrixSdk\Crypto
 */
class OlmDevice
{

    public function __construct(
        protected MatrixHttpApi $client,
        protected string $userId,
        protected ?string $deviceId,
        protected array &$encryptionConf,
    ) {}

    public function uploadIdentityKeys() {}

    public function uploadOneTimeKeys() {}

    public function updateOneTimeKeysCounts($device_one_time_keys_count) {}
}
