<?php
namespace Tests\Functional;

class TagsControllerTest extends BaseTestCase
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

        $response = $this->runApp('GET', '/tags');

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('table', (string)$response->getBody()); // has form
    }

    public function testGetCreate()
    {
        $this->login( $this->user );

        $response = $this->runApp('GET', '/tags/create');

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('form#tag_form', (string)$response->getBody()); // has form
    }

    public function testPostCategoryWithValidData()
    {
        $this->login( $this->user );

        $response = $this->runApp('POST', '/tags', static::getCategoryValues());

        // assertions
        $this->assertEquals(302, $response->getStatusCode());
    }

    /**
     * @dataProvider getInvalidData
     */
    public function testPostCategoryWithInvalidData($name)
    {
        $this->login( $this->user );

        $response = $this->runApp('POST', '/tags', [
            'name' => $name,
        ]);

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('form#tag_form', (string)$response->getBody()); // has form
        $this->assertQuery('.callout.alert', (string)$response->getBody()); // showing errors
    }

    public function testGetEdit()
    {
        $this->login( $this->user );

        $response = $this->runApp('GET', '/tags/' . $this->tag->id . '/edit');

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('form#tag_form', (string)$response->getBody()); // has form
    }

    public function testPutCategoryWithValidData()
    {
        $this->login( $this->user );

        $response = $this->runApp('POST', '/tags/' . $this->tag->id, static::getCategoryValues([
            '_METHOD' => 'PUT',
        ]));

        // assertions
        $this->assertEquals(302, $response->getStatusCode());
    }

    /**
     * @dataProvider getInvalidData
     */
    public function testPutCategoryWithInvalidData($name, $group)
    {
        $this->login( $this->user );

        $values = [
            'name' => $name,
            'group' => $group,

            '_METHOD' => 'PUT',
        ];

        $response = $this->runApp('POST', '/tags/' . $this->tag->id, $values);

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('form#tag_form', (string)$response->getBody()); // has form
        $this->assertQuery('.callout.alert', (string)$response->getBody()); // showing errors
    }

    public function testDeleteCategory()
    {
        $this->login( $this->user );

        $values = [
            '_METHOD' => 'DELETE',
        ];

        $this->login( $this->user );
        $response = $this->runApp('DELETE', '/tags/' . $this->tag->id);

        // assertions
        $this->assertEquals(302, $response->getStatusCode());
    }



    private static function getCategoryValues($values=array())
    {
        return array_merge([
            'name' => 'Holidays',
            'budget' => '100',
        ], $values);
    }

    public function getInvalidData()
    {
        return [
            static::getCategoryValues(['name' => '']),
            static::getCategoryValues(['name' => 'Tag2']), // duplicate
        ];
    }

    /**
     * This is an array of paths to check redirects are in place
     */
    public static function getProtectedPaths()
    {
        return [
            ['path' => '/tags', 'method' => 'GET'],
            ['path' => '/tags/create', 'method' => 'GET'],
            ['path' => '/tags', 'method' => 'POST'],
            ['path' => '/tags/1/edit', 'method' => 'GET'],
            ['path' => '/tags/1', 'method' => 'PUT'],
            ['path' => '/tags/1', 'method' => 'DELETE'],
        ];
    }
}
