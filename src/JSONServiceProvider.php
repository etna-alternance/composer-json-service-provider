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

        // Cas particulier pour les "cons" qui nous envoient le content-type,
        // mais pas du tout de content...
        // du coup, on force le request à un tableau vide, histoire d'écraser tout ce
        // que les autres trucs auraient pu mettre.
        $body = $request->getContent();
        if ("" === $body) {
            $request->request->replace([]);
            return;
        }

        $params = json_decode($body, true);

        // On n'utilise pas json_last_error() parce qu'il nous faut obligatoirement
        // un tableau pour le $request->request->replace() un peu plus bas...
        // Ce n'est donc pas vraiment une erreur de json_decode que l'on cherche
        // à vérifier.
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
