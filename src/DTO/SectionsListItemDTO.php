<?php

declare(strict_types=1);

namespace Johncms\Content\DTO;

class SectionsListItemDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $url,
    ) {
    }
}
