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

        $content = $request->getContent();
        if (true === is_resource($content)) {
            // Ca ne devrait pas arriver, mais juste au cas ou Symfony décide de nous retourner
            // une resource sur un truc un peu gros par exemple
            $this->app->abort(500, "\$request->getContent() is not a string, WTF ????");
        }

        $data = json_decode($content, true);
        if (false === is_array($data)) {
            $this->app->abort(400, "Invalid JSON data");
        }

        // OUF, on peut enfin faire ce qu'on a à faire...
        $request->request->replace($data);
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
