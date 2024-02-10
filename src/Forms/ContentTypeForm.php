<?php

declare(strict_types=1);

namespace Johncms\Content\Forms;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Johncms\Content\Models\ContentType;
use Johncms\Forms\AbstractForm;
use Johncms\Forms\Inputs\InputText;

class ContentTypeForm extends AbstractForm
{
    protected function prepareFormFields(): array
    {
        $fields = [];
        $fields['name'] = (new InputText())
            ->setLabel(__('Name'))
            ->setPlaceholder(p__('placeholder', 'Enter the Name of the Content Type'))
            ->setNameAndId('name')
            ->setValue($this->getValue('name'))
            ->setValidationRules(['NotEmpty']);

        $fields['code'] = (new InputText())
            ->setLabel(__('Code'))
            ->setPlaceholder(p__('placeholder', 'Enter the Code of the Content Type'))
            ->setNameAndId('code')
            ->setValue($this->getValue('code'))
            ->setValidationRules(
                [
                    'ModelNotExists' => [
                        'model'   => ContentType::class,
                        'field'   => 'code',
                        'exclude' => function (Builder $query) {
                            return $query->where('id', '!=', $this->getValue('id'));
                        },
                    ],
                ]
            );

        return $fields;
    }

    public function getRequestValues(): array
    {
        $values = parent::getRequestValues();
        $values['code'] = empty($values['code']) ? Str::slug($values['name']) : Str::slug($values['code']);
        return $values;
    }
}
