<?php

declare(strict_types=1);

namespace Johncms\Content\Controllers\Public;

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

        return $this->render->render('johncms/content::public/index', [
            'contentType' => $contentType,
            'sections'    => $sectionService->getRootSectionsForType($contentType),
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

        return $this->render->render('johncms/content::public/index', [
            'contentType' => $contentType,
            'sections'    => $sectionService->getChildrenSections($contentType, $currentSection),
        ]);
    }

    public function element(string $type, string $sectionPath, string $element)
    {
        dd('element', $type, $sectionPath, $element);
    }
}
