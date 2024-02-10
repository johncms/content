<?php

declare(strict_types=1);

namespace Johncms\Content\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Johncms\Files\Models\File;

class ContentElement extends Model
{
    protected $table = 'content_elements';

    protected $fillable = [
        'content_type_id',
        'section_id',
        'name',
        'code',
        'detail_text',
    ];

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'content_element_files', 'element_id', 'file_id');
    }
}
