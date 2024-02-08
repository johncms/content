<?php

declare(strict_types=1);

namespace Johncms\Content\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Johncms\Content\DTO\SectionsListItemDTO;
use Johncms\Content\Models\ContentSection;
use Johncms\Content\Models\ContentType;
use Johncms\Exceptions\PageNotFoundException;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class PublicSectionsService
{
    public function __construct(
        private readonly CacheInterface $cache
    ) {
    }

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
                route('content.section', ['type' => $contentType->code, 'sectionPath' => $section->code])
            );
        }
        return $result;
    }

    public function getChildrenSections(ContentType $contentType, ContentSection $section): array
    {
        $result = [];
        $sections = ContentSection::query()->where('parent', $section->id)->get();
        foreach ($sections as $section) {
            $result[] = new SectionsListItemDTO(
                $section->id,
                $section->name,
                route('content.section', ['type' => $contentType->code, 'sectionPath' => $this->getCachedPath($section->id)])
            );
        }

        return $result;
    }

    /**
     * Getting the full URL of the section from cache.
     *
     * @param int $sectionId
     * @return string
     */
    public function getCachedPath(int $sectionId): string
    {
        $paths = $this->cache->rememberForever(
            'contentSectionPaths',
            function () use ($sectionId) {
                return [$sectionId => $this->getPath($sectionId)];
            }
        );

        if (empty($paths) || ! array_key_exists($sectionId, $paths)) {
            try {
                $this->cache->delete('contentSectionPaths');
            } catch (InvalidArgumentException) {
            }
            $paths = $this->cache->rememberForever(
                'contentSectionPaths',
                function () use ($sectionId, $paths) {
                    $paths[$sectionId] = $this->getPath($sectionId);
                    return $paths;
                }
            );
        }
        return $paths[$sectionId] ?? '';
    }

    public function getPath(int $id): string
    {
        $sectionUrl = '';
        $section = ContentSection::query()->find($id);
        if ($section !== null) {
            $path = [
                $section->code,
            ];
            $parent = $section->parentSection;
            while ($parent !== null) {
                $path[] = $parent->code;
                $parent = $parent->parentSection;
            }

            krsort($path);

            $sectionUrl = implode('/', $path);
        }

        return $sectionUrl;
    }

    /**
     * Checking the path and throws exception if section doesn't exist
     *
     * @return ContentSection[]
     */
    public function checkAndGetPath(string $sectionPath, ContentType $contentType): array
    {
        $path = [];
        if (empty($sectionPath)) {
            return $path;
        }
        $sectionPath = rtrim($sectionPath, '/');
        $segments = explode('/', $sectionPath);
        $parent = 0;
        foreach ($segments as $segment) {
            try {
                $check = ContentSection::query()
                    ->where('content_type_id', $contentType->id)
                    ->when($parent === 0, function (Builder $query) {
                        return $query->whereNull('parent');
                    })
                    ->when($parent > 0, function (Builder $query) use ($parent) {
                        return $query->where('parent', $parent);
                    })
                    ->where('code', $segment)
                    ->firstOrFail();
                $path[] = $check;
                $parent = $check->id;
            } catch (ModelNotFoundException) {
                throw new PageNotFoundException(__('The requested section was not found.'));
            }
        }
        return $path;
    }
}
