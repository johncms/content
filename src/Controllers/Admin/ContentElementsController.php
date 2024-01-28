<?php

declare(strict_types=1);

namespace Johncms\Content\Controllers\Admin;

use Johncms\Content\Forms\ContentElementForm;
use Johncms\Content\Models\ContentElement;
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

    public function edit(int $elementId, Request $request, Session $session, ContentElementForm $form): string | RedirectResponse
    {
        $element = ContentElement::query()->findOrFail($elementId);

        // TODO: Refactoring
        $form->setValues(
            [
                'name'        => $element->name,
                'code'        => $element->code,
                'detail_text' => $element->detail_text,
            ]
        );

        $form->buildForm();

        if ($request->isPost()) {
            try {
                $form->validate();
                $values = $form->getRequestValues();
                $element->update($values);
                $session->flash('message', __('The Element was Successfully Updated'));

                return new RedirectResponse(route('content.admin.sections', ['sectionId' => $element->section_id, 'type' => $element->content_type_id]));
            } catch (ValidationException $validationException) {
                return (new RedirectResponse(route('content.admin.elements.edit', ['elementId' => $elementId])))
                    ->withPost()
                    ->withValidationErrors($validationException->getErrors());
            }
        }

        return $this->render->render('johncms/content::admin/create_element_form', [
            'formFields'       => $form->getFormFields(),
            'validationErrors' => $form->getValidationErrors(),
            'storeUrl'         => route('content.admin.elements.edit', ['elementId' => $elementId]),
            'listUrl'          => route('content.admin.sections', ['sectionId' => $element->section_id, 'type' => $element->content_type_id]),
        ]);
    }

    public function delete(int $id, Request $request, Session $session): RedirectResponse | string
    {
        $data = [];
        $element = ContentElement::query()->findOrFail($id);

        if ($request->isPost()) {
            $element->delete();
            $session->flash('message', __('The Element was Successfully Deleted'));
            return new RedirectResponse(route('content.admin.sections', ['sectionId' => $element->section_id, 'type' => $element->content_type_id]));
        }

        $data['elementName'] = $element->name;
        $data['actionUrl'] = route('content.admin.elements.delete', ['id' => $id]);

        return $this->render->render('johncms/content::admin/delete', ['data' => $data]);
    }
}
