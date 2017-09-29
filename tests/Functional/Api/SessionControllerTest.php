<?php
namespace Tests\Functional\Api;

use Tests\Functional\BaseTestCase;

class SessionControllerTest extends BaseTestCase
{
    public function testPostLoginWithValidCredentials()
    {
        $container = $this->app->getContainer();

        // mmock authenticate anyway coz this is all that's used here
        $container->get('auth')
            ->method('authenticate')
            ->willReturn(true);

        $response = $this->runApp('POST', '/api/session/login', [
            'email' => 'martyn@example.com',
            'password' => 'password1',
        ]);

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostLoginWithInvalidCredentials()
    {
        $container = $this->app->getContainer();

        // mmock authenticate anyway coz this is all that's used here
        $container->get('auth')
            ->method('authenticate')
            ->willReturn(false); // <-- authenticate() failed

        $response = $this->runApp('POST', '/api/session/login', [
            'email' => 'martyn@example.com',
            'password' => 'password1',
        ]);

        $json = json_decode((string)$response->getBody(), 1);

        // assertions
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertTrue( isset($json['errors']) ); // has form
    }

    public function testPostLogout()
    {
        $response = $this->runApp('POST', '/api/session/logout', [
            '_METHOD' => 'DELETE',
        ]);

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
    }
}
