<?php

declare(strict_types=1);

namespace Johncms\Content\Forms;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Johncms\Content\Models\ContentSection;
use Johncms\Content\Services\ContentSectionService;
use Johncms\Forms\AbstractForm;
use Johncms\Forms\Inputs\InputHidden;
use Johncms\Forms\Inputs\InputText;
use Johncms\Forms\Inputs\Select;
use Johncms\Http\Request;

class ContentSectionForm extends AbstractForm
{
    public function __construct(
        private readonly ContentSectionService $contentSectionService,
        Request $request,
        array $values = []
    ) {
        parent::__construct($request, $values);
    }

    protected function prepareFormFields(): array
    {
        $fields = [];

        $fields['content_type_id'] = (new InputHidden())
            ->setNameAndId('content_type_id')
            ->setValue($this->getValue('content_type_id'));

        $fields['parent'] = (new Select())
            ->setLabel(__('Parent Section'))
            ->setNameAndId('parent')
            ->setPlaceholder('test')
            ->setValue($this->getValue('parent'))
            ->setOptions($this->getSections());

        $fields['name'] = (new InputText())
            ->setLabel(__('Name'))
            ->setPlaceholder(p__('placeholder', 'Enter the Name of the Section'))
            ->setNameAndId('name')
            ->setValue($this->getValue('name'))
            ->setValidationRules(['NotEmpty']);

        $fields['code'] = (new InputText())
            ->setLabel(__('Code'))
            ->setPlaceholder(p__('placeholder', 'Enter the Code of the Section'))
            ->setNameAndId('code')
            ->setValue($this->getValue('code'))
            ->setValidationRules(
                [
                    'ModelNotExists' => [
                        'model'   => ContentSection::class,
                        'field'   => 'code',
                        'exclude' => function (Builder $query) {
                            $parent = (int) $this->getValue('parent');
                            if (empty($parent)) {
                                $parent = null;
                            }
                            return $query->where('content_type_id', '=', (int) $this->getValue('content_type_id'))
                                ->where('parent', '=', $parent)
                                ->where('id', '!=', $this->getValue('id'));
                        },
                    ],
                ]
            );

        return $fields;
    }

    private function getSections(): array
    {
        $result = [
            [
                'name'  => __('Root'),
                'value' => null,
            ],
        ];
        $contentTypeId = (int) $this->getValue('content_type_id');
        $sections = $this->contentSectionService->getAllContentTypeSectionsFlatList($contentTypeId, [$this->getValue('id')]);

        foreach ($sections as $section) {
            $result[] = [
                'name'  => str_repeat('&bull;', $section['level'] + 1) . ' ' . $section['name'],
                'value' => $section['id'],
            ];
        }

        return $result;
    }

    public function getRequestValues(): array
    {
        $values = parent::getRequestValues();
        if (empty($values['parent'])) {
            $values['parent'] = null;
        }

        $values['code'] = empty($values['code']) ? Str::slug($values['name']) : Str::slug($values['code']);

        return $values;
    }
}
