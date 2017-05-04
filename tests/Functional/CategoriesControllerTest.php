<?php
namespace Tests\Functional;

class CategoriesControllerTest extends BaseTestCase
{
    // /**
    //  * @dataProvider getProtectedPaths
    //  */
    // public function testRedirectsWhenNotAuthenticated($path, $method)
    // {
    //     $response = $this->runApp($method, $path);
    //
    //     // assertions
    //     $this->assertEquals(302, $response->getStatusCode());
    // }
    //
    // public function testIndexShowsLogoutMenuWhenAuthenticated()
    // {
    //     $this->login( $this->user );
    //
    //     $response = $this->runApp('GET', '/');
    //
    //     // assertions
    //     $this->assertEquals(200, $response->getStatusCode());
    //     $this->assertQuery('form#logout_form', (string)$response->getBody());
    // }

    public function testGetIndex()
    {
        $this->login( $this->user );

        $response = $this->runApp('GET', '/categories');

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('table', (string)$response->getBody()); // has form
    }

    public function testGetCreate()
    {
        $this->login( $this->user );

        $response = $this->runApp('GET', '/categories/create');

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('form#category_form', (string)$response->getBody()); // has form
    }

    // public function testPostTransactionWithValidData()
    // {
    //     $this->login( $this->user );
    //
    //     $response = $this->runApp('POST', '/categories', static::getTransactionValues());
    //
    //     // assertions
    //     $this->assertEquals(302, $response->getStatusCode());
    // }
    //
    // /**
    //  * @dataProvider getInvalidData
    //  */
    // public function testPostTransactionWithInvalidData($description, $amount, $purchasedAt, $categoryId)
    // {
    //     $this->login( $this->user );
    //
    //     $userValues = [
    //         'description' => $description,
    //         'amount' => $amount,
    //         'purchased_at' => $purchasedAt,
    //         'category_id' => $categoryId,
    //     ];
    //
    //     $response = $this->runApp('POST', '/categories', $userValues);
    //
    //     // assertions
    //     $this->assertEquals(200, $response->getStatusCode());
    //     $this->assertQuery('form#category_form', (string)$response->getBody()); // has form
    //     $this->assertQuery('.callout.alert', (string)$response->getBody()); // showing errors
    // }
    //
    // public function testGetEdit()
    // {
    //     $this->login( $this->user );
    //
    //     $response = $this->runApp('GET', '/categories/' . $this->category->id . '/edit');
    //
    //     // assertions
    //     $this->assertEquals(200, $response->getStatusCode());
    //     $this->assertQuery('form#category_form', (string)$response->getBody()); // has form
    // }
    //
    // /**
    //  * @dataProvider getInvalidData
    //  */
    // public function testPutTransactionWithInvalidData($description, $amount, $purchasedAt, $categoryId)
    // {
    //     $this->login( $this->user );
    //
    //     $userValues = [
    //         'description' => $description,
    //         'amount' => $amount,
    //         'purchased_at' => $purchasedAt,
    //         'category_id' => $categoryId,
    //
    //         '_METHOD' => 'PUT',
    //     ];
    //
    //     $response = $this->runApp('POST', '/categories/' . $this->category->id, $userValues);
    //
    //     // assertions
    //     $this->assertEquals(200, $response->getStatusCode());
    //     $this->assertQuery('form#category_form', (string)$response->getBody()); // has form
    //     $this->assertQuery('.callout.alert', (string)$response->getBody()); // showing errors
    // }
    //
    // public function testDeleteTransaction()
    // {
    //     $this->login( $this->user );
    //
    //     $userValues = [
    //         '_METHOD' => 'DELETE',
    //     ];
    //
    //     $this->login( $this->user );
    //     $response = $this->runApp('DELETE', '/categories/' . $this->category->id);
    //
    //     // assertions
    //     $this->assertEquals(302, $response->getStatusCode());
    // }



    private static function getTransactionValues($values=array())
    {
        return array_merge([
            'name' => 'Restaurant',
            'category_group_id' => $this->category_group->id,
        ], $values);
    }

    public function getInvalidData()
    {
        return [
            static::getTransactionValues(['name' => '']),
        ];
    }

    /**
     * This is an array of paths to check redirects are in place
     */
    public static function getProtectedPaths()
    {
        return [
            ['path' => '/categories', 'method' => 'GET'],
            ['path' => '/categories/create', 'method' => 'GET'],
            ['path' => '/categories', 'method' => 'POST'],
            ['path' => '/categories/1/edit', 'method' => 'GET'],
            ['path' => '/categories/1', 'method' => 'PUT'],
            ['path' => '/categories/1', 'method' => 'DELETE'],
        ];
    }
}
