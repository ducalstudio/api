<?php

namespace Ducal\Api\Forms\Settings;

use Ducal\Api\Facades\ApiHelper;
use Ducal\Api\Http\Requests\ApiSettingRequest;
use Ducal\Base\Forms\FieldOptions\OnOffFieldOption;
use Ducal\Base\Forms\Fields\OnOffCheckboxField;
use Ducal\Setting\Forms\SettingForm;

class ApiSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setValidatorClass(ApiSettingRequest::class)
            ->setSectionTitle(trans('packages/api::api.setting_title'))
            ->setSectionDescription(trans('packages/api::api.setting_description'))
            ->contentOnly()
            ->add(
                'api_enabled',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('packages/api::api.api_enabled'))
                    ->value(ApiHelper::enabled())
                    ->toArray()
            );
    }
}
