<?php
namespace Tests\Functional;

class FundsControllerTest extends BaseTestCase
{
    /**
     * @dataProvider getProtectedPaths
     */
    public function testRedirectsWhenNotAuthenticated($path, $method)
    {
        $response = $this->runApp($method, $path);

        // assertions
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testIndexShowsLogoutMenuWhenAuthenticated()
    {
        $this->login( $this->user );
        $response = $this->runApp('GET', '/');

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('form#logout_form', (string)$response->getBody());
    }




    // public function testGetCreate()
    // {
    //     $this->login( $this->user );
    //     $response = $this->runApp('GET', '/funds/create');
    //
    //     // assertions
    //     $this->assertEquals(200, $response->getStatusCode());
    //     $this->assertQuery('form#Fund_form', (string)$response->getBody()); // has form
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
    // public function testPostFundWithInvalidData($description, $amount, $purchasedAt, $categoryId)
    // {
    //     $userValues = [
    //         'description' => $description,
    //         'amount' => $amount,
    //         'purchased_at' => $purchasedAt,
    //         'category_id' => $categoryId,
    //
    //         '_METHOD' => 'PUT',
    //     ];
    //
    //     $this->login( $this->user );
    //     $response = $this->runApp('POST', '/funds/1', $userValues);
    //
    //     // assertions
    //     $this->assertEquals(200, $response->getStatusCode());
    //     $this->assertQuery('form#Fund_form', (string)$response->getBody()); // has form
    //     $this->assertQuery('.callout.alert', (string)$response->getBody()); // showing errors
    // }
    //
    // public function testGetEdit()
    // {
    //     $this->login( $this->user );
    //     $response = $this->runApp('GET', '/funds/1/edit');
    //
    //     // assertions
    //     $this->assertEquals(200, $response->getStatusCode());
    //     $this->assertQuery('form#Fund_form', (string)$response->getBody()); // has form
    // }
    //
    // /**
    //  * @dataProvider getInvalidData
    //  */
    // public function testPutFundWithInvalidData($description, $amount, $purchasedAt, $categoryId)
    // {
    //     $userValues = [
    //         'description' => $description,
    //         'amount' => $amount,
    //         'purchased_at' => $purchasedAt,
    //         'category_id' => $categoryId,
    //
    //         '_METHOD' => 'PUT',
    //     ];
    //
    //     $this->login( $this->user );
    //     $response = $this->runApp('PUT', '/funds/1', $userValues);
    //
    //     // assertions
    //     $this->assertEquals(200, $response->getStatusCode());
    //     $this->assertQuery('form#Fund_form', (string)$response->getBody()); // has form
    //     $this->assertQuery('.callout.alert', (string)$response->getBody()); // showing errors
    // }
    //
    // public function testDeleteFund()
    // {
    //     $userValues = [
    //         '_METHOD' => 'DELETE',
    //     ];
    //
    //     $this->login( $this->user );
    //     $response = $this->runApp('DELETE', '/funds/1');
    //
    //     // assertions
    //     $this->assertEquals(302, $response->getStatusCode());
    // }



    private static function getFundValues($values=array())
    {
        return array_merge([
            'name' => 'Bank of Scotland',
            'currency_id' => '1',
            'amount' => '100.10',
        ], $values);
    }

    public function getInvalidData()
    {
        return [
            static::getFundValues(['name' => '']),
            static::getFundValues(['currency_id' => '']),
            static::getFundValues(['amount' => '']),
        ];
    }

    /**
     * This is an array of paths to check redirects are in place
     */
    public static function getProtectedPaths()
    {
        return [
            ['path' => '/funds', 'method' => 'GET'],
            ['path' => '/funds/create', 'method' => 'GET'],
            ['path' => '/funds', 'method' => 'POST'],
            ['path' => '/funds/1/edit', 'method' => 'GET'],
            ['path' => '/funds/1', 'method' => 'PUT'],
            ['path' => '/funds/1', 'method' => 'DELETE'],
        ];
    }
}
