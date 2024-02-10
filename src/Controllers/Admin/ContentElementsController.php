<?php

declare(strict_types=1);

namespace Johncms\Content\Controllers\Admin;

use GuzzleHttp\Psr7\UploadedFile;
use Johncms\Content\Forms\ContentElementForm;
use Johncms\Content\Models\ContentElement;
use Johncms\Content\Services\NavChainService;
use Johncms\Controller\BaseAdminController;
use Johncms\Exceptions\ValidationException;
use Johncms\Files\FileInfo;
use Johncms\Files\FileStorage;
use Johncms\Http\Request;
use Johncms\Http\Response\RedirectResponse;
use Johncms\Http\Session;
use Psr\Log\LoggerInterface;

class ContentElementsController extends BaseAdminController
{
    protected string $moduleName = 'johncms/content';

    public function __construct(
        private readonly NavChainService $breadcrumbs,
        private readonly Session $session
    ) {
        parent::__construct();
        $this->navChain->add(__('Content'), route('content.admin.index'));
        $this->metaTagManager->setAll(__('Content'));
    }

    public function create(int $type, ?int $sectionId, Request $request, ContentElementForm $form): string | RedirectResponse
    {
        $this->breadcrumbs->setAdminBreadcrumbs($type, $sectionId);
        $this->metaTagManager->setAll(__('Create Element'));
        $this->navChain->add(__('Create Element'));

        $form->setValues(
            [
                'content_type_id' => $type,
                'section_id'      => $sectionId !== 0 ? $sectionId : null,
            ]
        );

        if ($request->isPost()) {
            try {
                $form->validate();
                $values = $form->getRequestValues();
                $element = ContentElement::query()->create($values);

                // Save file ids
                $files = (array) $request->getPost('detail_text_files', [], FILTER_VALIDATE_INT);
                if (! empty($files)) {
                    $element->files()->sync($files);
                }

                $this->session->flash('message', __('The Element was Successfully Created'));
                return new RedirectResponse(route('content.admin.sections', ['sectionId' => $sectionId, 'type' => $type]));
            } catch (ValidationException $validationException) {
                return (new RedirectResponse(route('content.admin.elements.create', ['sectionId' => $sectionId, 'type' => $type])))
                    ->withPost()
                    ->withValidationErrors($validationException->getErrors());
            }
        }

        return $this->render->render('johncms/content::admin/content_element_form', [
            'formTitle'        => __('Create Element'),
            'formFields'       => $form->getFormFields(),
            'validationErrors' => $form->getValidationErrors(),
            'storeUrl'         => route('content.admin.elements.create', ['sectionId' => $sectionId, 'type' => $type]),
            'listUrl'          => route('content.admin.sections', ['sectionId' => $sectionId, 'type' => $type]),
        ]);
    }

    public function edit(int $elementId, Request $request, ContentElementForm $form): string | RedirectResponse
    {
        $element = ContentElement::query()->findOrFail($elementId);

        $this->breadcrumbs->setAdminBreadcrumbs($element->content_type_id, $element->section_id);
        $this->metaTagManager->setAll($element->name);
        $this->navChain->add($element->name);

        $form->setValues(
            [
                'id'              => $element->id,
                'content_type_id' => $element->content_type_id,
                'section_id'      => $element->section_id,
                'name'            => $element->name,
                'code'            => $element->code,
                'detail_text'     => $element->detail_text,
            ]
        );

        $form->buildForm();

        if ($request->isPost()) {
            try {
                $form->validate();
                $values = $form->getRequestValues();
                $element->update($values);

                $files = (array) $request->getPost('detail_text_files', [], FILTER_VALIDATE_INT);
                if (! empty($files)) {
                    $files = array_merge($files, $element->files->pluck('id')->toArray());
                    $element->files()->sync($files);
                }

                $this->session->flash('message', __('The Element was Successfully Updated'));

                return new RedirectResponse(route('content.admin.sections', ['sectionId' => $element->section_id, 'type' => $element->content_type_id]));
            } catch (ValidationException $validationException) {
                return (new RedirectResponse(route('content.admin.elements.edit', ['elementId' => $elementId])))
                    ->withPost()
                    ->withValidationErrors($validationException->getErrors());
            }
        }

        return $this->render->render('johncms/content::admin/content_element_form', [
            'formTitle'        => __('Edit Element'),
            'formFields'       => $form->getFormFields(),
            'validationErrors' => $form->getValidationErrors(),
            'storeUrl'         => route('content.admin.elements.edit', ['elementId' => $elementId]),
            'listUrl'          => route('content.admin.sections', ['sectionId' => $element->section_id, 'type' => $element->content_type_id]),
        ]);
    }

    public function delete(int $id, Request $request): RedirectResponse | string
    {
        $data = [];
        $element = ContentElement::query()->findOrFail($id);

        if ($request->isPost()) {
            $element->delete();
            $this->session->flash('message', __('The Element was Successfully Deleted'));
            return new RedirectResponse(route('content.admin.sections', ['sectionId' => $element->section_id, 'type' => $element->content_type_id]));
        }

        $data['elementName'] = $element->name;
        $data['actionUrl'] = route('content.admin.elements.delete', ['id' => $id]);

        return $this->render->render('johncms/content::admin/delete', ['data' => $data]);
    }

    public function uploadFile(Request $request, LoggerInterface $logger, FileStorage $storage): array
    {
        try {
            /** @var UploadedFile[] $files */
            $files = $request->getUploadedFiles();
            $file_info = new FileInfo($files['upload']->getClientFilename());
            if (! $file_info->isImage()) {
                return [
                    'error' => [
                        'message' => __('Only images are allowed'),
                    ],
                ];
            }

            $file = $storage->saveFromRequest('upload', 'content');
            return [
                'id'       => $file->id,
                'name'     => $file->name,
                'uploaded' => 1,
                'url'      => $file->url,
            ];
        } catch (\Throwable $e) {
            $logger->error($e->getMessage(), ['trace' => $e->getTrace()]);
            http_response_code(500);
            return ['errors' => $e->getMessage()];
        }
    }
}
