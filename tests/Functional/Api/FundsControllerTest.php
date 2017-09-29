<?php
namespace Tests\Functional\Api;

use Tests\Functional\BaseTestCase;
use Tests\Functional\Traits\ApiHelpers;

class FundsControllerTest extends BaseTestCase
{
    use ApiHelpers;

    public function testIndexWithInvalidTokenReturns401()
    {
        $response = $this->runApp('GET', '/api/funds', null, $this->getAuthHeaders(false));

        // assertions
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testIndexWithValidTokenReturns200()
    {
        $response = $this->runApp('GET', '/api/funds', null, $this->getAuthHeaders(true));

        $json = json_decode((string)$response->getBody(), 1);

        // assertions
        $this->assertEquals(HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->fund->name, @$json[0]['name']);
    }

    public function testPostWithInvalidTokenReturns401()
    {
        $response = $this->runApp('POST', '/api/funds/create', static::getFundValues(), $this->getAuthHeaders(false));

        // assertions
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testPostWithValidTokenReturns200()
    {
        $response = $this->runApp('POST', '/api/funds/create', static::getFundValues(), $this->getAuthHeaders(true));

        // assertions
        $this->assertEquals(HTTP_OK, $response->getStatusCode());
    }

    /**
     * @dataProvider getInvalidData
     */
    public function testPostFundWithInvalidData($name, $currencyId, $amount)
    {
        $fundValues = [
            'name' => $name,
            'currency_id' => $currencyId,
            'amount' => $amount,
        ];

        $response = $this->runApp('POST', '/api/funds/create', $fundValues, $this->getAuthHeaders(true));

        $json = json_decode((string)$response->getBody(), 1);

        // assertions
        $this->assertEquals(HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertTrue( isset($json['errors']) ); // has form
    }

    public function testPutWithInvalidTokenReturns401()
    {
        $response = $this->runApp('PUT', '/api/funds/1/edit', static::getFundValues(), $this->getAuthHeaders(false));

        // assertions
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testPutWithValidTokenReturns200()
    {
        $response = $this->runApp('PUT', '/api/funds/1/edit', static::getFundValues(), $this->getAuthHeaders(true));

        // assertions
        $this->assertEquals(HTTP_OK, $response->getStatusCode());
    }

    /**
     * @dataProvider getInvalidData
     */
    public function testPutWithInvalidData($name, $currencyId, $amount)
    {
        $fundValues = [
            'name' => $name,
            'currency_id' => $currencyId,
            'amount' => $amount,
        ];

        $response = $this->runApp('PUT', '/api/funds/1/edit', $fundValues, $this->getAuthHeaders(true));

        $json = json_decode((string)$response->getBody(), 1);

        // assertions
        $this->assertEquals(HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertTrue( isset($json['errors']) ); // has form
    }

    public function testDeleteWithInvalidTokenReturns401()
    {
        $response = $this->runApp('DELETE', '/api/funds/1/delete', null, $this->getAuthHeaders(false));

        // assertions
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testDeleteWithValidTokenReturns200()
    {
        $response = $this->runApp('DELETE', '/api/funds/1/delete', null, $this->getAuthHeaders(true));

        // assertions
        $this->assertEquals(HTTP_OK, $response->getStatusCode());
    }



    private static function getFundValues($values=array())
    {
        return array_merge([
            'name' => 'Bank of Scotland',
            'amount' => '100.10',
            'currency_id' => '1',
        ], $values);
    }

    public function getInvalidData()
    {
        return [
            static::getFundValues(['name' => '']),
            static::getFundValues(['amount' => '']),
            static::getFundValues(['currency_id' => '']),
        ];
    }
}
