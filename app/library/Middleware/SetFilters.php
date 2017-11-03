<?php
namespace App\Middleware;

class SetFilters extends Base
{
    /**
     * Attach to routes to ensure protected pages
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        $params = $request->getQueryParams();
        $container = $this->container;

        if ($container->get('auth')->isAuthenticated()) {

            $currentUser = $this->getCurrentUser();

            // fund filter
            if ($fundId = $request->getQueryParam('fund')) {
                $container->get('session')->set(SESSION_FILTER_FUND, $fundId);
            }

            // fund must exist so we attempt to fetch it from the db
            $fundId = $container->get('session')->get(SESSION_FILTER_FUND);
            ($fund = $currentUser->funds()->find($fundId)) ||
                ($fund = $currentUser->funds()->first());
            if (!$fund) return $response->withRedirect( $container->get('router')->pathFor('funds') );

            // set session var - from confirmed $fund
            $container->get('session')->set(SESSION_FILTER_FUND, $fund->id);
        }

        $response = $next($request, $response);

        return $response;
    }
}
