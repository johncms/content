<?php

declare(strict_types=1);

namespace Johncms\Content\Forms;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Johncms\Content\Models\ContentElement;
use Johncms\Forms\AbstractForm;
use Johncms\Forms\Inputs\InputText;
use Johncms\Forms\Inputs\Textarea;

class ContentElementForm extends AbstractForm
{
    protected function prepareFormFields(): array
    {
        $fields = [];
        $fields['name'] = (new InputText())
            ->setLabel(__('Name'))
            ->setPlaceholder(p__('placeholder', 'Enter the Name of the Element'))
            ->setNameAndId('name')
            ->setValue($this->getValue('name'))
            ->setValidationRules(['NotEmpty']);

        $fields['code'] = (new InputText())
            ->setLabel(__('Code'))
            ->setPlaceholder(p__('placeholder', 'Enter the Code of the Element'))
            ->setNameAndId('code')
            ->setValue($this->getValue('code'))
            ->setValidationRules(
                [
                    'ModelNotExists' => [
                        'model'   => ContentElement::class,
                        'field'   => 'code',
                        'exclude' => function (Builder $query) {
                            return $query->where('content_type_id', '=', $this->getValue('content_type_id'))
                                ->where('section_id', '=', $this->getValue('section_id'))
                                ->where('id', '!=', $this->getValue('id'));
                        },
                    ],
                ]
            );

        $fields['detail_text'] = (new Textarea())
            ->setLabel(__('Detail Text'))
            ->setPlaceholder(p__('placeholder', 'Enter the Detail Text'))
            ->setNameAndId('detail_text')
            ->setValue($this->getValue('detail_text'));

        return $fields;
    }

    public function getRequestValues(): array
    {
        $values = parent::getRequestValues();
        $values['code'] = empty($values['code']) ? Str::slug($values['name']) : Str::slug($values['code']);
        return $values;
    }
}
