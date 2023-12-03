<?php

declare(strict_types=1);

namespace Johncms\Content\Resources;

use Johncms\Content\Models\ContentElement;
use Johncms\Http\Resources\AbstractResource;

/**
 * @mixin ContentElement
 */
class ContentElementResource extends AbstractResource
{
    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'code'      => $this->code,

            // TODO: Change url
            'deleteUrl' => route('content.admin.sections.delete', ['id' => $this->id, 'type' => $this->content_type_id]),
            'url'       => route('content.admin.sections', ['sectionId' => $this->id, 'type' => $this->content_type_id]),
        ];
    }
}
