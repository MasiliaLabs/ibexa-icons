<?php

declare(strict_types=1);

namespace Masilia\Bundle\IbexaIcons\EventListener;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/** "Icons" under the Admin group of the Ibexa sidebar, for those who may see the icons. */
final readonly class MainMenuBuilderListener implements EventSubscriberInterface
{
    public function __construct(private AuthorizationCheckerInterface $authorization)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [ConfigureMenuEvent::MAIN_MENU => 'onMainMenuBuild'];
    }

    public function onMainMenuBuild(ConfigureMenuEvent $event): void
    {
        $admin = $event->getMenu()->getChild('main__admin');
        if (!$admin instanceof ItemInterface || !$this->authorization->isGranted(new Attribute('masilia_icon', 'view'))) {
            return;
        }

        $admin->addChild('main__admin__masilia_icons', ['route' => 'masilia.ibexa_icons.list'])
            ->setLabel('menu.icons')
            ->setExtra('translation_domain', 'masilia_ibexa_icons')
            ->setExtra('icon', 'file-type')
            ->setExtra('orderNumber', 154);
    }
}
