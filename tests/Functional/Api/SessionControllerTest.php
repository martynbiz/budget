<?php
namespace Tests\Functional\Api;

use Tests\Functional\BaseTestCase;

// use Slim\App;
// use Slim\Http\Request;
// use Slim\Http\Response;
// use Slim\Http\Environment;

class SessionControllerTest extends BaseTestCase
{
    public function testPostLoginWithValidCredentials()
    {
        $container = $this->app->getContainer();

        // mmock authenticate anyway coz this is all that's used here
        $container->get('auth')
            ->method('authenticate')
            ->willReturn(true);

        // TODO How do we test POSTed JSON data????????????????

        $response = $this->runApp('POST', '/api/session', [
            'email' => 'martyn@example.com',
            'password' => 'password1',
        ]);

        // assertions
        $this->assertEquals(200, $response->getStatusCode());
    }

    // public function testPostLoginWithInvalidCredentials()
    // {
    //     $container = $this->app->getContainer();
    //
    //     // mmock authenticate anyway coz this is all that's used here
    //     $container->get('auth')
    //         ->method('authenticate')
    //         ->willReturn(false); // <-- authenticate() failed
    //
    //     $response = $this->runApp('POST', '/api/session', [
    //         'email' => 'martyn@example.com',
    //         'password' => 'password1',
    //     ]);
    //
    //     $json = json_decode((string)$response->getBody(), 1);
    //
    //     // assertions
    //     $this->assertEquals(401, $response->getStatusCode());
    //     $this->assertTrue( isset($json['error']) ); // has form
    // }
    //
    // public function testPostLogout()
    // {
    //     $response = $this->runApp('POST', '/api/session', [
    //         '_METHOD' => 'DELETE',
    //     ]);
    //
    //     // assertions
    //     $this->assertEquals(200, $response->getStatusCode());
    // }

    // /**
    //  * Process the application given a request method and URI
    //  *
    //  * @param string $requestMethod the request method (e.g. GET, POST, etc.)
    //  * @param string $requestUri the request URI
    //  * @param array|object|null $requestData the request data
    //  * @return \Slim\Http\Response
    //  */
    // public function runApp($requestMethod, $requestUri, $requestData = null)
    // {
    //     // Create a mock environment for testing with
    //     $environment = Environment::mock(
    //         [
    //             'REQUEST_METHOD' => $requestMethod,
    //             'REQUEST_URI' => $requestUri
    //         ]
    //     );
    //
    //     // Set up a request object based on the environment
    //     $request = Request::createFromEnvironment($environment);
    //
    //     // Add request data, if it exists
    //     if (isset($requestData)) {
    //         // $request = $request->withParsedBody($requestData);
    //         $request->write(json_encode($requestData));
    //         // $request->getBody()->rewind();
    //         // //set method & content type
    //         $request = $request->withHeader('Content-Type', 'application/json');
    //         $request = $request->withMethod($requestMethod);
    //     }
    //
    //     // Set up a response object
    //     $response = new Response();
    //
    //     // Process the application
    //     $response = $this->app->process($request, $response);
    //
    //     // Return the response
    //     return $response;
    // }
}
