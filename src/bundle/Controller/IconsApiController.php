<?php

declare(strict_types=1);

namespace Masilia\Bundle\IbexaIcons\Controller;

use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute;
use Masilia\Contracts\IbexaAi\Os\Capability;
use Masilia\Contracts\IbexaIcons\IconKind;
use Masilia\Contracts\IbexaIcons\IconRegistry;
use Masilia\Contracts\IbexaIcons\IconSource;
use Masilia\Contracts\IbexaIcons\InvalidIcon;
use Masilia\IbexaIcons\Icon\ContentModelScanner;
use Masilia\IbexaIcons\Icon\Sprite;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * What the scripts of the icons page ask for, as JSON: the content types and groups with their icon, the icons of Ibexa to choose from,
 * and the two writes (choose an icon, take the choice back). Seen with masilia_icon/view, changed with masilia_icon/manage. A write
 * carries the CSRF token of the page in the X-CSRF-Token header (see CsrfTokenSubscriber).
 */
#[Route('/api/masilia/icons')]
final class IconsApiController extends Controller
{
    public const ROUTE_PREFIX = 'masilia.ibexa_icons.api.';

    public function __construct(
        private readonly IconRegistry $registry,
        private readonly ContentModelScanner $scanner,
        private readonly Sprite $sprite,
    ) {
    }

    public function performAccessCheck(): void
    {
        parent::performAccessCheck();
        $this->denyAccessUnlessGranted(new Attribute('masilia_icon', 'view'));
    }

    #[Route('/data', name: self::ROUTE_PREFIX . 'data', methods: ['GET'])]
    public function data(): JsonResponse
    {
        return new JsonResponse([
            'canManage' => $this->isGranted(new Attribute('masilia_icon', 'manage')),
            'assistant' => interface_exists(Capability::class),
            'items' => array_map(static fn (array $item): array => ['kind' => $item['kind']->value] + array_diff_key($item, ['kind' => true]), $this->scanner->scan()),
            'icons' => $this->sprite->ids(),
        ]);
    }

    #[Route('/{kind}/{identifier}', name: self::ROUTE_PREFIX . 'set', requirements: ['kind' => 'type|group', 'identifier' => '.+'], methods: ['PUT'])]
    public function set(Request $request, string $kind, string $identifier): JsonResponse
    {
        $this->denyAccessUnlessGranted(new Attribute('masilia_icon', 'manage'));
        $kind = IconKind::from($kind);
        if (!$this->scanner->exists($kind, $identifier)) {
            return $this->error('not_found', sprintf('There is no %s "%s" in the repository.', $kind->value, $identifier), Response::HTTP_NOT_FOUND);
        }
        $body = json_decode($request->getContent(), true);
        $icon = is_array($body) && is_string($body['icon'] ?? null) ? $body['icon'] : '';
        try {
            $this->registry->set($kind, $identifier, $icon, IconSource::Admin);
        } catch (InvalidIcon $e) {
            return $this->error('invalid_icon', $e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY, [['path' => 'icon', 'message' => $e->getMessage()]]);
        }

        return new JsonResponse(['kind' => $kind->value, 'identifier' => $identifier, 'icon' => $icon, 'source' => IconSource::Admin->value]);
    }

    #[Route('/{kind}/{identifier}', name: self::ROUTE_PREFIX . 'remove', requirements: ['kind' => 'type|group', 'identifier' => '.+'], methods: ['DELETE'])]
    public function remove(string $kind, string $identifier): JsonResponse
    {
        $this->denyAccessUnlessGranted(new Attribute('masilia_icon', 'manage'));
        $this->registry->remove(IconKind::from($kind), $identifier);

        return new JsonResponse(['kind' => $kind, 'identifier' => $identifier]);
    }

    /** @param list<array{path: string, message: string}> $problems */
    private function error(string $code, string $message, int $status, array $problems = []): JsonResponse
    {
        return new JsonResponse(['error' => ['code' => $code, 'message' => $message], 'problems' => $problems], $status);
    }
}
