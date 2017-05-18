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

        // fund filter
        if ($fundId = $request->getQueryParam('filter__fund_id')) {
            $container->get('session')->set(SESSION_FILTER_FUND, $fundId);
        }

        // set (default) fund
        // fund must exist so we attempt to fetch it from the db
        ($fund = $container->get('model.fund')->find($fundId)) ||
            ($fund = $container->get('model.fund')->first());
        if (!$fund) return $response->withRedirect('/funds');

        $container->get('session')->set(SESSION_FILTER_FUND, $fund->id);


        // month filter
        if (!$month = $request->getQueryParam('filter__month'))
            $month = date('Y-m');
        $container->get('session')->set(SESSION_FILTER_MONTH, $month);

        $response = $next($request, $response);

        return $response;
    }
}
