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

    public function testPostCategoryWithValidData()
    {
        $this->login( $this->user );

        $response = $this->runApp('POST', '/categories', static::getCategoryValues());

        // assertions
        $this->assertEquals(302, $response->getStatusCode());
    }

    /**
     * @dataProvider getInvalidData
     */
    public function testPostCategoryWithInvalidData($name)
    {
        $this->login( $this->user );

        $response = $this->runApp('POST', '/categories', [
            'name' => $name,
        ]);

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('form#category_form', (string)$response->getBody()); // has form
        $this->assertQuery('.callout.alert', (string)$response->getBody()); // showing errors
    }

    public function testGetEdit()
    {
        $this->login( $this->user );

        $response = $this->runApp('GET', '/categories/' . $this->category->id . '/edit');

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('form#category_form', (string)$response->getBody()); // has form
    }

    public function testPutCategoryWithValidData()
    {
        $this->login( $this->user );

        $response = $this->runApp('POST', '/categories/' . $this->category->id, static::getCategoryValues([
            '_METHOD' => 'PUT',
        ]));

        // assertions
        $this->assertEquals(302, $response->getStatusCode());
    }

    /**
     * @dataProvider getInvalidData
     */
    public function testPutCategoryWithInvalidData($name)
    {
        $this->login( $this->user );

        $values = [
            'name' => $name,

            '_METHOD' => 'PUT',
        ];

        $response = $this->runApp('POST', '/categories/' . $this->category->id, $values);

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('form#category_form', (string)$response->getBody()); // has form
        $this->assertQuery('.callout.alert', (string)$response->getBody()); // showing errors
    }

    public function testDeleteCategory()
    {
        $this->login( $this->user );

        $values = [
            '_METHOD' => 'DELETE',
        ];

        $this->login( $this->user );
        $response = $this->runApp('DELETE', '/categories/' . $this->category->id);

        // assertions
        $this->assertEquals(302, $response->getStatusCode());
    }



    private static function getCategoryValues($values=array())
    {
        return array_merge([
            'name' => 'Restaurant',
            'group' => 'Food',
        ], $values);
    }

    public function getInvalidData()
    {
        return [
            static::getCategoryValues(['name' => '']),
            static::getCategoryValues(['name' => 'Groceries']), // duplicate
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
