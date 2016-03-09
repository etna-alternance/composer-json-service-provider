<?php

namespace ETNA\Silex\Provider\JSON;

use Silex\Application;
use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class JSONServiceProvider implements ServiceProviderInterface
{
    private $app;

    public function register(Container $app)
    {
        $this->app = $app;

        $app->before([$this, "jsonInputHandler"]);
        $app->error([$this, "errorHandler"]);
    }

    /**
     * Gère le JSON dans le body d'une requête
     *
     * @param Request $request
     */
    public function jsonInputHandler(Request $request)
    {
        // on ne s'interese qu'aux requêtes de type "application/json"
        if (0 !== strpos($request->headers->get('Content-Type'), 'application/json')) {
            return;
        }

        $params = json_decode($request->getContent(), true);
        if (false === is_array($params)) {
            $this->app->abort(400, "Invalid JSON data");
        }

        // OUF, on peut enfin faire ce qu'on a à faire...
        $request->request->replace($params);
    }

    /**
     * Gère les erreurs et retourne un truc standardisé
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
        $code = $this->sanitizeExceptionCode($code, $exception);

        switch (true) {
            case 100 <= $code && $code < 500:
            case $this->app["debug"] === true:
                $message = $exception->getMessage();
                break;

            default:
                $message = null;
                break;
        }

        return $this->app->json($message, $code);
    }

    /**
     * @param integer $code
     * @param null|\Exception $exception
     * @return integer
     */
    private function sanitizeExceptionCode($code, \Exception $exception = null)
    {
        switch (true) {
            case $exception instanceof \InvalidArgumentException:
                return 400;

            case null !== $exception && !$exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException:
                return $this->sanitizeExceptionCode($exception->getCode());

            case 100 <= $code && $code < 600:
                return $code;

            default:
                return 500;
        }
    }
}
