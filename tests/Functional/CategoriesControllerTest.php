<?php
namespace Tests\Functional;

class CategoriesControllerTest extends BaseTestCase
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
    //     $response = $this->runApp('GET', '/categories/create');
    //
    //     // assertions
    //     $this->assertEquals(200, $response->getStatusCode());
    //     $this->assertQuery('form#category_form', (string)$response->getBody()); // has form
    // }
    //
    // public function testPostCategoryWithValidData()
    // {
    //     $this->login( $this->user );
    //     $response = $this->runApp('POST', '/categories', static::getCategoryValues());
    //
    //     // assertions
    //     $this->assertEquals(302, $response->getStatusCode());
    // }
    //
    // /**
    //  * @dataProvider getInvalidData
    //  */
    // public function testPostCategoryWithInvalidData($description, $amount, $purchasedAt, $categoryId)
    // {
    //     $userValues = [
    //         'description' => $description,
    //         'amount' => $amount,
    //         'purchased_at' => $purchasedAt,
    //         'category_id' => $categoryId,
    //     ];
    //
    //     $this->login( $this->user );
    //     $response = $this->runApp('POST', '/categories/1', $userValues);
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
    //     $response = $this->runApp('GET', '/categories/1/edit');
    //
    //     // assertions
    //     $this->assertEquals(200, $response->getStatusCode());
    //     $this->assertQuery('form#category_form', (string)$response->getBody()); // has form
    // }
    //
    // /**
    //  * @dataProvider getInvalidData
    //  */
    // public function testPutCategoryWithInvalidData($description, $amount, $purchasedAt, $categoryId)
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
    //     $response = $this->runApp('POST', '/categories/1', $userValues);
    //
    //     // assertions
    //     $this->assertEquals(200, $response->getStatusCode());
    //     $this->assertQuery('form#category_form', (string)$response->getBody()); // has form
    //     $this->assertQuery('.callout.alert', (string)$response->getBody()); // showing errors
    // }
    //
    // public function testDeleteCategory()
    // {
    //     $userValues = [
    //         '_METHOD' => 'DELETE',
    //     ];
    //
    //     $this->login( $this->user );
    //     $response = $this->runApp('DELETE', '/categories/1');
    //
    //     // assertions
    //     $this->assertEquals(302, $response->getStatusCode());
    // }



    private static function getCategoryValues($values=array())
    {
        return array_merge([
            'name' => 'Eating out',
            'category_group_id' => '1',
        ], $values);
    }

    public function getInvalidData()
    {
        return [
            static::getCategoryValues(['name' => '']),
            static::getCategoryValues(['category_group_id' => '']),
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
