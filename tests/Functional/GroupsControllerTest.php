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

    public function testIndexShowsTableMenuWhenAuthenticated()
    {
        $this->login( $this->user );

        $response = $this->runApp('GET', '/groups');

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

    public function testPostGroupWithValidData()
    {
        $this->login( $this->user );

        $response = $this->runApp('POST', '/groups', static::getGroupValues());

        // assertions
        $this->assertEquals(302, $response->getStatusCode());
    }

    /**
     * @dataProvider getInvalidData
     */
    public function testPostGroupWithInvalidData($name)
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
    public function testPutGroupWithInvalidData($name)
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

    public function testDeleteGroup()
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



    private static function getGroupValues($values=array())
    {
        return array_merge([
            'name' => 'Restaurant',
            'parent_id' => 1,
        ], $values);
    }

    public function getInvalidData()
    {
        return [
            static::getGroupValues(['name' => '']),
            static::getGroupValues(['name' => 'Food2']), // duplicate
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
