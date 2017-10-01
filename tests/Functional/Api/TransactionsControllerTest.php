<?php
namespace Tests\Functional\Api;

use Tests\Functional\BaseTestCase;
use Tests\Functional\Traits\ApiHelpers;

class TransactionsControllerTest extends BaseTestCase
{
    use ApiHelpers;

    public function testIndexWithInvalidTokenReturns401()
    {
        $response = $this->runApp('GET', '/api/transactions', null, $this->getAuthHeaders(false));

        // assertions
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testIndexWithValidTokenReturns200()
    {
        $response = $this->runApp('GET', '/api/transactions', null, $this->getAuthHeaders(true));

        $json = json_decode((string)$response->getBody(), 1);

        // assertions
        $this->assertEquals(HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->transaction->name, @$json[0]['name']);
    }

    public function testPostWithInvalidTokenReturns401()
    {
        $response = $this->runApp('POST', '/api/transactions/create', static::getTransactionValues(), $this->getAuthHeaders(false));

        // assertions
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testPostWithValidTokenReturns200()
    {
        $response = $this->runApp('POST', '/api/transactions/create', static::getTransactionValues(), $this->getAuthHeaders(true));

        // assertions
        $this->assertEquals(HTTP_OK, $response->getStatusCode());
    }

    /**
     * @dataProvider getInvalidData
     */
    public function testPostTransactionWithInvalidData($name, $currencyId, $amount)
    {
        $transactionValues = [
            'name' => $name,
            'currency_id' => $currencyId,
            'amount' => $amount,
        ];

        $response = $this->runApp('POST', '/api/transactions/create', $transactionValues, $this->getAuthHeaders(true));

        $json = json_decode((string)$response->getBody(), 1);

        // assertions
        $this->assertEquals(HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertTrue( isset($json['errors']) ); // has form
    }

    public function testPutWithInvalidTokenReturns401()
    {
        $response = $this->runApp('PUT', '/api/transactions/1/edit', static::getTransactionValues(), $this->getAuthHeaders(false));

        // assertions
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testPutWithValidTokenReturns200()
    {
        $response = $this->runApp('PUT', '/api/transactions/1/edit', static::getTransactionValues(), $this->getAuthHeaders(true));

        // assertions
        $this->assertEquals(HTTP_OK, $response->getStatusCode());
    }

    /**
     * @dataProvider getInvalidData
     */
    public function testPutWithInvalidData($name, $currencyId, $amount)
    {
        $transactionValues = [
            'name' => $name,
            'currency_id' => $currencyId,
            'amount' => $amount,
        ];

        $response = $this->runApp('PUT', '/api/transactions/1/edit', $transactionValues, $this->getAuthHeaders(true));

        $json = json_decode((string)$response->getBody(), 1);

        // assertions
        $this->assertEquals(HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertTrue( isset($json['errors']) ); // has form
    }

    public function testDeleteWithInvalidTokenReturns401()
    {
        $response = $this->runApp('DELETE', '/api/transactions/1/delete', null, $this->getAuthHeaders(false));

        // assertions
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testDeleteWithValidTokenReturns200()
    {
        $response = $this->runApp('DELETE', '/api/transactions/1/delete', null, $this->getAuthHeaders(true));

        // assertions
        $this->assertEquals(HTTP_OK, $response->getStatusCode());
    }



    private static function getTransactionValues($values=array())
    {
        return array_merge([
            'description' => 'Sandwich',
            'amount' => '12.50',
            'purchased_at' => '2017-05-01',
            'category' => 'Groceries',
            'fund_id' => '1',
            'tags' => '',
        ], $values);
    }

    public function getInvalidData()
    {
        return [
            static::getTransactionValues(['description' => '']),
            static::getTransactionValues(['amount' => '']),
            static::getTransactionValues(['purchased_at' => '']),
        ];
    }
}
