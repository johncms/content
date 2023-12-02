<?php

declare(strict_types=1);

namespace Johncms\Content\Controllers\Admin;

use Johncms\Content\Forms\ContentSectionForm;
use Johncms\Content\Models\ContentSection;
use Johncms\Content\Resources\ContentSectionResource;
use Johncms\Controller\BaseAdminController;
use Johncms\Exceptions\ValidationException;
use Johncms\Http\Request;
use Johncms\Http\Response\RedirectResponse;
use Johncms\Http\Session;

class ContentSectionsController extends BaseAdminController
{
    protected string $moduleName = 'johncms/content';

    public function __construct()
    {
        parent::__construct();
        $this->navChain->add(__('Content'), route('content.admin.index'));
        $this->metaTagManager->setAll(__('Content'));
    }

    public function index(int $type, Session $session): string
    {
        $contentSections = ContentSection::query()->where('content_type_id', $type)->get();

        return $this->render->render('johncms/content::admin/sections', [
            'data' => [
                'typeId'       => $type,
                'message'      => $session->getFlash('message'),
                'contentTypes' => ContentSectionResource::createFromCollection($contentSections)->toArray(),
            ],
        ]);
    }

    public function create(int $type, Request $request, Session $session, ContentSectionForm $form): string | RedirectResponse
    {
        if ($request->isPost()) {
            try {
                $form->validate();
                $values = $form->getRequestValues();
                // TODO: Refactoring
                $values['content_type_id'] = $type;
                ContentSection::query()->create($values);
                $session->flash('message', __('The Section was Successfully Created'));
                return new RedirectResponse(route('content.admin.sections', ['type' => $type]));
            } catch (ValidationException $validationException) {
                return (new RedirectResponse(route('content.admin.sections.create', ['type' => $type])))
                    ->withPost()
                    ->withValidationErrors($validationException->getErrors());
            }
        }

        return $this->render->render('johncms/content::admin/create_section_form', [
            'formFields'       => $form->getFormFields(),
            'validationErrors' => $form->getValidationErrors(),
            'storeUrl'         => route('content.admin.sections.create', ['type' => $type]),
            'listUrl'          => route('content.admin.index'),
        ]);
    }

    public function delete(int $id, Request $request, Session $session): RedirectResponse | string
    {
        $data = [];
        $contentSection = ContentSection::query()->findOrFail($id);

        if ($request->isPost()) {
            $contentSection->delete();
            $session->flash('message', __('The Section was Successfully Deleted'));
            return new RedirectResponse(route('content.admin.index'));
        }

        $data['elementName'] = $contentSection->name;
        $data['actionUrl'] = route('content.admin.sections.delete', ['id' => $id]);

        return $this->render->render('johncms/content::admin/delete', ['data' => $data]);
    }
}
