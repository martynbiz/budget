<?php
namespace Tests\Functional;

class SessionControllerTest extends BaseTestCase
{
    public function testGetLoginShowsFormWhenNotAuthenticated()
    {
        $response = $this->runApp('GET', '/login');

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('form#login_form', (string)$response->getBody()); // has form
    }

    public function testGetLoginRedirectsWhenAuthenticated()
    {
        $this->login( $this->user );

        $response = $this->runApp('GET', '/login');

        // assertions
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testPostLoginWithValidCredentials()
    {
        $this->login( $this->user );

        $response = $this->runApp('POST', '/login', [
            'email' => 'martyn@example.com',
            'password' => 'password1',
        ]);

        // assertions
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testPostLoginWithInvalidCredentials()
    {
        $response = $this->runApp('POST', '/login', [
            'email' => 'martyn@example.com',
            'password' => 'password1',
        ]);

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('form#login_form', (string)$response->getBody()); // has form
        $this->assertQuery('.callout.alert', (string)$response->getBody()); // showing errors
    }

    public function testGetLogoutShowsFormWhenAuthenticated()
    {
        $this->login( $this->user );

        $response = $this->runApp('GET', '/logout');

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertQuery('.content-wrapper form#logout_form', (string)$response->getBody()); // has form
    }

    public function testGetLogoutRedirectsWhenAuthenticated()
    {
        $response = $this->runApp('GET', '/logout');

        // assertions
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testPostLogout()
    {
        // mock authenticate to return true
        $this->app->getContainer()['auth']
            ->expects( $this->once() )
            ->method('clearAttributes');

        $response = $this->runApp('POST', '/logout', [
            '_METHOD' => 'DELETE',
        ]);

        // assertions
        $this->assertEquals(302, $response->getStatusCode());
    }
}
