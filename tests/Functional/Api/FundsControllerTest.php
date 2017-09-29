<?php
namespace Tests\Functional\Api;

use Tests\Functional\BaseTestCase;

class FundsControllerTest extends BaseTestCase
{
    public function testIndexWithInvalidTokenReturns401()
    {
        $response = $this->runApp('GET', '/api/funds', null, [
            'Authorization' => 'Bearer invalid-token123',
            'Content-Type' => 'application/json',
        ]);

        // assertions
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testIndexWithValidTokenReturns200()
    {
        $response = $this->runApp('GET', '/api/funds', null, [
            'Authorization' => 'Bearer ' . $this->api_token->value,
            'Content-Type' => 'application/json',
        ]);

        $json = json_decode((string)$response->getBody(), 1);

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($this->fund->name, @$json[0]['name']);
    }

    // public function testGetCreate()
    // {
    //     $this->login( $this->user );
    //     $response = $this->runApp('GET', '/funds/create');
    //
    //     // assertions
    //     $this->assertEquals(200, $response->getStatusCode());
    //     $this->assertQuery('form#fund_form', (string)$response->getBody()); // has form
    // }
    //
    // public function testPostFundWithValidData()
    // {
    //     $this->login( $this->user );
    //     $response = $this->runApp('POST', '/funds', static::getFundValues());
    //
    //     // assertions
    //     $this->assertEquals(302, $response->getStatusCode());
    // }
    //
    // /**
    //  * @dataProvider getInvalidData
    //  */
    // public function testPostFundWithInvalidData($name, $currencyId, $amount)
    // {
    //     $this->login( $this->user );
    //     $userValues = [
    //         'name' => $name,
    //         'currency_id' => $currencyId,
    //         'amount' => $amount,
    //     ];
    //
    //     $this->login( $this->user );
    //     $response = $this->runApp('POST', '/funds', $userValues);
    //
    //     // assertions
    //     $this->assertEquals(200, $response->getStatusCode());
    //     $this->assertQuery('form#fund_form', (string)$response->getBody()); // has form
    //     $this->assertQuery('.callout.alert', (string)$response->getBody()); // showing errors
    // }
    //
    // public function testGetEdit()
    // {
    //     $this->login( $this->user );
    //     $response = $this->runApp('GET', '/funds/' . $this->fund->id . '/edit');
    //
    //     // assertions
    //     $this->assertEquals(200, $response->getStatusCode());
    //     $this->assertQuery('form#fund_form', (string)$response->getBody()); // has form
    // }
    //
    // /**
    //  * @dataProvider getInvalidData
    //  */
    // public function testPutFundWithInvalidData($name, $currencyId, $amount)
    // {
    //     $this->login( $this->user );
    //     $userValues = [
    //         'name' => $name,
    //         'currency_id' => $currencyId,
    //         'amount' => $amount,
    //
    //         '_METHOD' => 'PUT',
    //     ];
    //
    //     $this->login( $this->user );
    //     $response = $this->runApp('POST', '/funds/' . $this->fund->id, $userValues);
    //
    //     // assertions
    //     $this->assertEquals(200, $response->getStatusCode());
    //     $this->assertQuery('form#fund_form', (string)$response->getBody()); // has form
    //     $this->assertQuery('.callout.alert', (string)$response->getBody()); // showing errors
    // }
    //
    // public function testDeleteFund()
    // {
    //     $this->login( $this->user );
    //     $userValues = [
    //         '_METHOD' => 'DELETE',
    //     ];
    //
    //     $this->login( $this->user );
    //     $response = $this->runApp('DELETE', '/funds/' . $this->fund->id);
    //
    //     // assertions
    //     $this->assertEquals(302, $response->getStatusCode());
    // }
    //
    //
    //
    // private static function getFundValues($values=array())
    // {
    //     return array_merge([
    //         'name' => 'Bank of Scotland',
    //         'currency_id' => '1',
    //         'amount' => '100.10',
    //     ], $values);
    // }
    //
    // public function getInvalidData()
    // {
    //     return [
    //         static::getFundValues(['name' => '']),
    //     ];
    // }
    //
    // /**
    //  * This is an array of paths to check redirects are in place
    //  */
    // public static function getProtectedPaths()
    // {
    //     return [
    //         ['path' => '/funds', 'method' => 'GET'],
    //         ['path' => '/funds/create', 'method' => 'GET'],
    //         ['path' => '/funds', 'method' => 'POST'],
    //         ['path' => '/funds/1/edit', 'method' => 'GET'],
    //         ['path' => '/funds/1', 'method' => 'PUT'],
    //         ['path' => '/funds/1', 'method' => 'DELETE'],
    //     ];
    // }
}
