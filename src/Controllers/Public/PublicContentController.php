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

    public function section(string $type, string $section)
    {
        dd('section', $type, $section);
    }

    public function element(string $type, string $section, string $element)
    {
        dd('element', $type, $section, $element);
    }
}
