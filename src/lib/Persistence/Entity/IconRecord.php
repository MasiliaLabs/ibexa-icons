<?php

declare(strict_types=1);

namespace Masilia\IbexaIcons\Persistence\Entity;

use Doctrine\ORM\Mapping as ORM;

/** The icon of a content type or of a group (table masilia_icon). Read and written through IconRegistry. */
#[ORM\Entity]
#[ORM\Table(name: 'masilia_icon')]
class IconRecord
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 10)]
        public string $kind,
        #[ORM\Id]
        #[ORM\Column(length: 190)]
        public string $identifier,
        #[ORM\Column(length: 80)]
        public string $icon,
        #[ORM\Column(length: 10)]
        public string $source,
        #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
        public \DateTimeImmutable $updatedAt,
    ) {
    }
}
