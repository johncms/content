<?php

declare(strict_types=1);

namespace Johncms\Content\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ContentSection extends Model
{
    protected $fillable = [
        'content_type_id',
        'parent',
        'name',
        'code',
    ];

    public function contentType(): HasOne
    {
        return $this->hasOne(ContentType::class, 'id', 'content_type_id');
    }

    public function parentSection(): HasOne
    {
        return $this->hasOne(__CLASS__, 'id', 'parent');
    }

    public function childSections(): HasMany
    {
        return $this->hasMany(__CLASS__, 'parent', 'id');
    }
}
