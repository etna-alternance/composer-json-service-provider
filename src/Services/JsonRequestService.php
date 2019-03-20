<?php
/**
 * PHP version 7.1
 * @author BLU <dev@etna-alternance.net>
 */

declare(strict_types=1);

namespace ETNA\Json\Services;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Cette classe décrit le service auth qui va intéragir directement avec
 * le cookie authenticator contenu dans la requête HTTP.
 */
class JsonRequestService implements EventSubscriberInterface
{
    /**
     * C'est la fonction qui sera appelée par symfony lors d'un des events indiqué par getSubscribedEvents.
     *
     * @param FilterControllerEvent $event L'évènement
     */
    public function onKernelController(FilterControllerEvent $event): void
    {
        $controller = $event->getController();

        /*
         * cf la doc de symfony :
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!\is_array($controller)) {
            return;
        }

        $this->jsonRequest($event->getRequest());
    }

    /**
     * Gère le JSON dans le body d'une requête.
     *
     * @param Request $request
     */
    public function jsonRequest(Request $request): void
    {
        $content_type = $request->headers->get('Content-Type');

        if (\is_string($content_type)) {
            // on ne s'interese qu'aux requêtes de type "application/json"
            if (0 !== strpos($content_type, 'application/json')) {
                return;
            }

            $params  = json_decode((string) $request->getContent(), true);
            if (false === \is_array($params)) {
                throw new HttpException(400, 'Invalid JSON data');
            }

            // OUF, on peut enfin faire ce qu'on a à faire...
            $request->request->replace($params);
        }
    }

    /**
     * Retourne la liste des différents events sur lesquels cette classe va intervenir
     * En l'occurence, avant d'accéder à une des fonction d'un des controlleurs.
     *
     * @return array<string,array<string|integer>>
     */
    public static function getSubscribedEvents()
    {
        // 255 correspond à la plus haute priorité
        return [
            KernelEvents::CONTROLLER => ['onKernelController', 0],
        ];
    }
}
