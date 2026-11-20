<?php

declare(strict_types=1);

namespace Masilia\Bundle\IbexaIcons\Controller;

use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The icons page of the admin: a shell that loads a React application, which reads and writes through the JSON API
 * (see IconsApiController). Seen with masilia_icon/view; what it changes needs masilia_icon/manage.
 */
final class IconsController extends Controller
{
    public function performAccessCheck(): void
    {
        parent::performAccessCheck();
        $this->denyAccessUnlessGranted(new Attribute('masilia_icon', 'view'));
    }

    #[Route('/icons', name: 'masilia.ibexa_icons.list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('@ibexadesign/icons/list.html.twig');
    }
}
