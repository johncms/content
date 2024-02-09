<?php

declare(strict_types=1);

namespace Johncms\Content\Controllers\Public;

use Illuminate\Database\Eloquent\Builder;
use Johncms\Content\DTO\ElementsListItemDTO;
use Johncms\Content\Models\ContentElement;
use Johncms\Content\Models\ContentType;
use Johncms\Content\Services\PublicSectionsService;
use Johncms\Controller\BaseController;

class PublicContentController extends BaseController
{
    protected string $moduleName = 'johncms/content';

    public function index(string $type, PublicSectionsService $sectionService): string
    {
        $contentType = ContentType::query()->where('code', $type)->firstOrFail();
        $this->metaTagManager->setAll($contentType->name);
        $this->navChain->add($contentType->name, route('content.type', ['type' => $contentType->code]));

        $elements = ContentElement::query()
            ->where('content_type_id', $contentType->id)
            ->whereNull('section_id')
            ->paginate();

        return $this->render->render('johncms/content::public/index', [
            'contentType' => $contentType,
            'sections'    => $sectionService->getRootSectionsForType($contentType),
            'elements'    => $elements->getItems()
                ->map(function (ContentElement $item) use ($contentType) {
                    return new ElementsListItemDTO(
                        $item->id,
                        $item->name,
                        route('content.rootElement', ['type' => $contentType->code, 'element' => $item->code])
                    );
                })
                ->toArray(),
            'pagination'  => $elements->render(),
        ]);
    }

    public function section(string $type, string $sectionPath, PublicSectionsService $sectionService): string
    {
        $contentType = ContentType::query()->where('code', $type)->firstOrFail();
        $this->navChain->add($contentType->name, route('content.type', ['type' => $contentType->code]));

        $sections = $sectionService->checkAndGetPath($sectionPath, $contentType);
        foreach ($sections as $section) {
            $this->navChain->add($section->name, route('content.section', ['type' => $contentType->code, 'sectionPath' => $sectionService->getCachedPath($section->id)]));
        }

        $currentSection = $sections[array_key_last($sections)];
        $this->metaTagManager->setAll($currentSection->name);

        $elements = ContentElement::query()
            ->where('content_type_id', $contentType->id)
            ->where('section_id', $currentSection->id)
            ->paginate();

        return $this->render->render('johncms/content::public/index', [
            'contentType' => $contentType,
            'sections'    => $sectionService->getChildrenSections($contentType, $currentSection),
            'elements'    => $elements->getItems()
                ->map(function (ContentElement $item) use ($contentType, $sectionService) {
                    return new ElementsListItemDTO(
                        $item->id,
                        $item->name,
                        route('content.element', [
                            'type'        => $contentType->code,
                            'sectionPath' => $sectionService->getCachedPath($item->section_id),
                            'element'     => $item->code,
                        ])
                    );
                })
                ->toArray(),
            'pagination'  => $elements->render(),
        ]);
    }

    public function element(string $type, string $sectionPath, string $element, PublicSectionsService $sectionService, \HTMLPurifier $purifier): string
    {
        $contentType = ContentType::query()->where('code', $type)->firstOrFail();
        $this->navChain->add($contentType->name, route('content.type', ['type' => $contentType->code]));

        $currentSection = null;
        if (! empty($sectionPath)) {
            $sections = $sectionService->checkAndGetPath($sectionPath, $contentType);
            foreach ($sections as $section) {
                $this->navChain->add($section->name, route('content.section', ['type' => $contentType->code, 'sectionPath' => $sectionService->getCachedPath($section->id)]));
            }
            $currentSection = $sections[array_key_last($sections)];
        }

        $contentElement = ContentElement::query()
            ->where('content_type_id', $contentType->id)
            ->when($currentSection, function (Builder $query) use ($currentSection) {
                $query->where('section_id', $currentSection->id);
            })
            ->where('code', $element)
            ->firstOrFail();

        if ($currentSection) {
            $this->navChain->add($contentElement->name, route('content.element', [
                'type'        => $contentType->code,
                'sectionPath' => $sectionService->getCachedPath($currentSection->id),
                'element'     => $contentElement->code,
            ]));
        } else {
            $this->navChain->add($contentElement->name, route('content.rootElement', [
                'type'    => $contentType->code,
                'element' => $contentElement->code,
            ]));
        }

        $this->metaTagManager->setAll($contentElement->name);

        return $this->render->render('johncms/content::public/detail', [
            'element' => [
                'detailText' => $purifier->purify($contentElement->detail_text),
            ],
        ]);
    }
}
