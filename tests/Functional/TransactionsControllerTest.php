<?php
namespace Tests\Functional;

class TransactionsControllerTest extends BaseTestCase
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

    public function testGetIndex()
    {
        $this->login( $this->user );

        $response = $this->runApp('GET', '/transactions');

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('table', (string)$response->getBody()); // has form
    }

    public function testGetCreate()
    {
        $this->login( $this->user );

        $response = $this->runApp('GET', '/transactions/create');

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('form#transaction_form', (string)$response->getBody()); // has form
    }

    public function testPostTransactionWithValidData()
    {
        $this->login( $this->user );

        $response = $this->runApp('POST', '/transactions', static::getTransactionValues());

        // assertions
        $this->assertEquals(302, $response->getStatusCode());
    }

    /**
     * @dataProvider getInvalidData
     */
    public function testPostTransactionWithInvalidData($description, $amount, $purchasedAt, $category)
    {
        $this->login( $this->user );

        $response = $this->runApp('POST', '/transactions', [
            'description' => $description,
            'amount' => $amount,
            'purchased_at' => $purchasedAt,
            'category' => $category,
        ]);

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('form#transaction_form', (string)$response->getBody()); // has form
        $this->assertQuery('.callout.alert', (string)$response->getBody()); // showing errors
    }

    public function testGetEdit()
    {
        $this->login( $this->user );

        $response = $this->runApp('GET', '/transactions/' . $this->transaction->id . '/edit');

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('form#transaction_form', (string)$response->getBody()); // has form
    }

    public function testPutTransactionWithValidData()
    {
        $this->login( $this->user );

        $response = $this->runApp('POST', '/transactions/' . $this->transaction->id, static::getTransactionValues([
            '_METHOD' => 'PUT',
        ]));

        // assertions
        $this->assertEquals(302, $response->getStatusCode());
    }

    /**
     * @dataProvider getInvalidData
     */
    public function testPutTransactionWithInvalidData($description, $amount, $purchasedAt, $category)
    {
        $this->login( $this->user );

        $response = $this->runApp('POST', '/transactions/' . $this->transaction->id, [
            'description' => $description,
            'amount' => $amount,
            'purchased_at' => $purchasedAt,
            'category' => $category,

            '_METHOD' => 'PUT',
        ]);

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('form#transaction_form', (string)$response->getBody()); // has form
        $this->assertQuery('.callout.alert', (string)$response->getBody()); // showing errors
    }

    public function testDeleteTransaction()
    {
        $this->login( $this->user );

        $values = [
            '_METHOD' => 'DELETE',
        ];

        $this->login( $this->user );
        $response = $this->runApp('DELETE', '/transactions/' . $this->transaction->id);

        // assertions
        $this->assertEquals(302, $response->getStatusCode());
    }



    private static function getTransactionValues($values=array())
    {
        return array_merge([
            'description' => 'Sandwich',
            'amount' => '12.50',
            'purchased_at' => '2017-05-01 00:00:05',
            'category' => 'Groceries',
        ], $values);
    }

    public function getInvalidData()
    {
        return [
            static::getTransactionValues(['description' => '']),
            static::getTransactionValues(['amount' => '']),
            static::getTransactionValues(['purchased_at' => '']),
            // static::getTransactionValues(['category' => '']),
        ];
    }

    /**
     * This is an array of paths to check redirects are in place
     */
    public static function getProtectedPaths()
    {
        return [
            ['path' => '/transactions', 'method' => 'GET'],
            ['path' => '/transactions/create', 'method' => 'GET'],
            ['path' => '/transactions', 'method' => 'POST'],
            ['path' => '/transactions/1/edit', 'method' => 'GET'],
            ['path' => '/transactions/1', 'method' => 'PUT'],
            ['path' => '/transactions/1', 'method' => 'DELETE'],
        ];
    }
}
