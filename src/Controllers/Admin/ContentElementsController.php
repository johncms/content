<?php

declare(strict_types=1);

namespace Johncms\Content\Controllers\Admin;

use Johncms\Content\Forms\ContentElementForm;
use Johncms\Content\Models\ContentElement;
use Johncms\Content\Models\ContentSection;
use Johncms\Controller\BaseAdminController;
use Johncms\Exceptions\ValidationException;
use Johncms\Http\Request;
use Johncms\Http\Response\RedirectResponse;
use Johncms\Http\Session;

class ContentElementsController extends BaseAdminController
{
    protected string $moduleName = 'johncms/content';

    public function __construct()
    {
        parent::__construct();
        $this->navChain->add(__('Content'), route('content.admin.index'));
        $this->metaTagManager->setAll(__('Content'));
    }

    public function create(int $type, ?int $sectionId, Request $request, Session $session, ContentElementForm $form): string | RedirectResponse
    {
        if ($request->isPost()) {
            try {
                $form->validate();
                $values = $form->getRequestValues();
                // TODO: Refactoring
                $values['content_type_id'] = $type;
                if ($sectionId > 0) {
                    $values['section_id'] = $sectionId;
                }

                ContentElement::query()->create($values);
                $session->flash('message', __('The Element was Successfully Created'));
                return new RedirectResponse(route('content.admin.sections', ['sectionId' => $sectionId, 'type' => $type]));
            } catch (ValidationException $validationException) {
                return (new RedirectResponse(route('content.admin.elements.create', ['sectionId' => $sectionId, 'type' => $type])))
                    ->withPost()
                    ->withValidationErrors($validationException->getErrors());
            }
        }

        return $this->render->render('johncms/content::admin/create_element_form', [
            'formFields'       => $form->getFormFields(),
            'validationErrors' => $form->getValidationErrors(),
            'storeUrl'         => route('content.admin.elements.create', ['sectionId' => $sectionId, 'type' => $type]),
            'listUrl'          => route('content.admin.index'),
        ]);
    }

    public function delete(int $type, int $id, Request $request, Session $session): RedirectResponse | string
    {
        $data = [];
        $contentSection = ContentSection::query()->findOrFail($id);

        if ($request->isPost()) {
            $contentSection->delete();
            $session->flash('message', __('The Section was Successfully Deleted'));
            return new RedirectResponse(route('content.admin.sections', ['type' => $type]));
        }

        $data['elementName'] = $contentSection->name;
        $data['actionUrl'] = route('content.admin.sections.delete', ['id' => $id, 'type' => $type]);

        return $this->render->render('johncms/content::admin/delete', ['data' => $data]);
    }
}
