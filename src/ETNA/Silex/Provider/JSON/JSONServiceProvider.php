<?php

namespace ETNA\Silex\Provider\JSON;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class JSONServiceProvider implements ServiceProviderInterface
{
    public function boot(Application $app)
    {
    }

    public function register(Application $app)
    {
        // Gere le JSON en body
        $app->before(
            function (Request $request) {
                if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                    $data = json_decode($request->getContent(), true);
                    $request->request->replace(is_array($data) ? $data : array());
                }
            }
        );

        /* Error Handler magique :
         * catch toutes les exceptions, et fait une réponse JSON normale, en gardant le code d'erreur HTTP :)
         */
        $app->error(
            function (\Exception $e, $code) use ($app) {
                if (!is_a($e, "Symfony\Component\HttpKernel\Exception\HttpException")) {
                    $code = $e->getCode();
                }
                if (is_a($e, "InvalidArgumentException")) {
                    $code = 400;
                }
                if ($code >= 100 && $code < 500) {
                    return $app->json($e->getMessage(), $code);
                }
                return $app->json($app["debug"] == true ? $e->getMessage() : null, 500);
            }
        );
    }
}
