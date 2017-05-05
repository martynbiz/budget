<?php
namespace Tests\Functional;

class GroupsControllerTest extends BaseTestCase
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

        $response = $this->runApp('GET', '/groups');

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('table', (string)$response->getBody()); // has form
    }

    public function testGetCreate()
    {
        $this->login( $this->user );

        $response = $this->runApp('GET', '/groups/create');

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('form#group_form', (string)$response->getBody()); // has form
    }

    public function testPostCategoryWithValidData()
    {
        $this->login( $this->user );

        $response = $this->runApp('POST', '/groups', static::getCategoryValues());

        // assertions
        $this->assertEquals(302, $response->getStatusCode());
    }

    /**
     * @dataProvider getInvalidData
     */
    public function testPostCategoryWithInvalidData($name)
    {
        $this->login( $this->user );

        $response = $this->runApp('POST', '/groups', [
            'name' => $name,
        ]);

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('form#group_form', (string)$response->getBody()); // has form
        $this->assertQuery('.callout.alert', (string)$response->getBody()); // showing errors
    }

    public function testGetEdit()
    {
        $this->login( $this->user );

        $response = $this->runApp('GET', '/groups/' . $this->group->id . '/edit');

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('form#group_form', (string)$response->getBody()); // has form
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

        $response = $this->runApp('POST', '/groups/' . $this->group->id, $values);

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('form#group_form', (string)$response->getBody()); // has form
        $this->assertQuery('.callout.alert', (string)$response->getBody()); // showing errors
    }

    public function testDeleteCategory()
    {
        $this->login( $this->user );

        $values = [
            '_METHOD' => 'DELETE',
        ];

        $this->login( $this->user );
        $response = $this->runApp('DELETE', '/groups/' . $this->group->id);

        // assertions
        $this->assertEquals(302, $response->getStatusCode());
    }



    private static function getCategoryValues($values=array())
    {
        return array_merge([
            'name' => 'Restaurant',
            'parent_id' => 1,
        ], $values);
    }

    public function getInvalidData()
    {
        return [
            static::getCategoryValues(['name' => '']),
        ];
    }

    /**
     * This is an array of paths to check redirects are in place
     */
    public static function getProtectedPaths()
    {
        return [
            ['path' => '/groups', 'method' => 'GET'],
            ['path' => '/groups/create', 'method' => 'GET'],
            ['path' => '/groups', 'method' => 'POST'],
            ['path' => '/groups/1/edit', 'method' => 'GET'],
            ['path' => '/groups/1', 'method' => 'PUT'],
            ['path' => '/groups/1', 'method' => 'DELETE'],
        ];
    }
}
