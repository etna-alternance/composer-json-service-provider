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
    private $app;

    public function boot(Application $app)
    {
    }

    public function register(Application $app)
    {
        $this->app = $app;

        $app->before([$this, "jsonInputHandler"]);
        $app->error([$this, "errorHandler"]);
    }

    /**
     * Gere le JSON dans le body d'une requete
     *
     * @param Request $request
     */
    public function jsonInputHandler(Request $request)
    {
        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) === true ? $data : array());
        }
    }

    /**
     * Gere les erreurs et retourne un truc standardisé
     *
     * Le code d'erreur HTTP est gardé, mais pas forcément le message, et sera encapsuler dans une réponse JSON
     * Le message de l'exception n'est retourné que si ce n'est pas une erreur 500
     * ou que le `debug` est activé
     *
     * @param \Exception $exception
     * @param integer $code HTTP status code (100 <= $code <= 50x)
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function errorHandler(\Exception $exception, $code)
    {
        if (false === is_a($exception, "Symfony\Component\HttpKernel\Exception\HttpException")) {
            $code = $exception->getCode();
        }

        if (true === is_a($exception, "InvalidArgumentException")) {
            $code = 400;
        }

        if ($code >= 100 && $code < 500) {
            return $this->app->json($exception->getMessage(), $code);
        }

        return $this->app->json($this->app["debug"] === true ? $exception->getMessage() : null, 500);
    }
}
