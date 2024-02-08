<?php

declare(strict_types=1);

namespace Johncms\Content\Services;

use Johncms\Content\DTO\SectionsListItemDTO;
use Johncms\Content\Models\ContentSection;
use Johncms\Content\Models\ContentType;

class PublicSectionsService
{
    /**
     * @return SectionsListItemDTO[]
     */
    public function getRootSectionsForType(ContentType $contentType): array
    {
        $result = [];
        $sections = ContentSection::query()->where('content_type_id', $contentType->id)->whereNull('parent')->get();
        foreach ($sections as $section) {
            $result[] = new SectionsListItemDTO(
                $section->id,
                $section->name,
                route('content.section', ['type' => $contentType->code, 'section' => $section->code])
            );
        }
        return $result;
    }
}
