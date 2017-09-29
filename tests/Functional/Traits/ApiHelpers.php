<?php
namespace Tests\Functional\Traits;

trait ApiHelpers {

    /**
     * Will create api auth headers authenticated or not
     * @param {boolean}
     */
    protected function getAuthHeaders($authenticated = true, $additionalHeaders=[]) {

        if ($authenticated) {

            return array_merge([
                'Authorization' => 'Bearer ' . $this->api_token->value,
                'Content-Type' => 'application/json',
            ], $additionalHeaders);

        } else {

            return array_merge([
                'Authorization' => 'invalid-token123',
                'Content-Type' => 'application/json',
            ], $additionalHeaders);

        }
    }
}
