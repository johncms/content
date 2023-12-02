<?php

declare(strict_types=1);

namespace Johncms\Content\Resources;

use Johncms\Content\Models\ContentSection;
use Johncms\Http\Resources\AbstractResource;

/**
 * @mixin ContentSection
 */
class ContentSectionResource extends AbstractResource
{
    public function toArray(): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'url'  => route('content.admin.sections', ['sectionId' => $this->id, 'type' => $this->content_type_id])
        ];
    }
}
