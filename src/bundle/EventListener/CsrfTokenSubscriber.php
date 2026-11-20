<?php

declare(strict_types=1);

namespace Masilia\Bundle\IbexaIcons\EventListener;

use Masilia\Bundle\IbexaIcons\Controller\IconsApiController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * The writes of the icons API are made by the scripts of the admin page, with the CSRF token of the admin (the one of the REST API of
 * Ibexa) in the X-CSRF-Token header. A write without a valid token is refused before the controller runs.
 */
final readonly class CsrfTokenSubscriber implements EventSubscriberInterface
{
    public const HEADER = 'X-CSRF-Token';

    public function __construct(
        private CsrfTokenManagerInterface $tokens,
        #[Autowire('%ibexa.rest.csrf_token_intention%')]
        private string $intention,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::CONTROLLER => 'onController'];
    }

    public function onController(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        $controller = $event->getController();
        $instance = is_array($controller) ? $controller[0] : $controller;
        if (!$instance instanceof IconsApiController || in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return;
        }
        $value = (string) $request->headers->get(self::HEADER, '');
        if ($value !== '' && $this->tokens->isTokenValid(new CsrfToken($this->intention, $value))) {
            return;
        }
        $refusal = new JsonResponse(['error' => ['code' => 'csrf', 'message' => 'The CSRF token is missing or not valid: reload the page.'], 'problems' => []], Response::HTTP_FORBIDDEN);
        $event->setController(static fn (): Response => $refusal);
    }
}
