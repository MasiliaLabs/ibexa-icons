<?php

declare(strict_types=1);

namespace Masilia\IbexaIcons\Tests\Lib;

use Ibexa\Core\MVC\Exception\ParameterNotFoundException;
use Masilia\Contracts\IbexaIcons\IconKind;
use Masilia\Contracts\IbexaIcons\IconSource;
use Masilia\IbexaIcons\Resolver\RegistryIconConfigResolver;
use Masilia\IbexaIcons\Tests\InMemoryRegistry;
use PHPUnit\Framework\TestCase;

final class RegistryIconConfigResolverTest extends TestCase
{
    private function resolver(): RegistryIconConfigResolver
    {
        $registry = new InMemoryRegistry();
        $registry->set(IconKind::Type, 'home_page', 'home', IconSource::Default);
        $registry->set(IconKind::Group, 'Block items', 'box-component', IconSource::Admin);

        return new RegistryIconConfigResolver($registry);
    }

    public function testItAnswersTheIconOfATypeAndOfAGroupLikeTheYamlOfIbexaWould(): void
    {
        $resolver = $this->resolver();

        self::assertTrue($resolver->hasParameter('content_type.home_page'));
        self::assertSame(['thumbnail' => '/bundles/ibexaadminuiassets/vendors/ids-assets/dist/img/all-icons.svg#home'], $resolver->getParameter('content_type.home_page'));
        // Ibexa asks for a group by the slug of its name.
        self::assertSame(['thumbnail' => '/bundles/ibexaadminuiassets/vendors/ids-assets/dist/img/all-icons.svg#box-component'], $resolver->getParameter('content_type_group.block_items'));
        self::assertFalse($resolver->hasParameter('content_type_group.Block items'));
    }

    public function testItSaysNothingAboutWhatHasNoIconSoTheYamlAndTheDefaultAnswer(): void
    {
        $resolver = $this->resolver();

        self::assertFalse($resolver->hasParameter('content_type.article'));
        self::assertFalse($resolver->hasParameter('content_type.default-config'));
        self::assertFalse($resolver->hasParameter('languages'));
        $this->expectException(ParameterNotFoundException::class);
        $resolver->getParameter('content_type.article');
    }

    public function testItIgnoresOtherNamespacesThanTheOneOfTheSiteaccessSettings(): void
    {
        self::assertFalse($this->resolver()->hasParameter('content_type.home_page', 'some_bundle'));
        self::assertTrue($this->resolver()->hasParameter('content_type.home_page', 'ibexa.site_access.config'));
    }

    public function testItCanBeSilencedToReadWhatTheYamlSaysOnItsOwn(): void
    {
        $resolver = $this->resolver();

        self::assertFalse($resolver->withoutRegistry(static fn (): bool => $resolver->hasParameter('content_type.home_page')));
        self::assertTrue($resolver->hasParameter('content_type.home_page'));
    }
}
