<?php

namespace Ducal\Api\Forms;

use Ducal\Api\Http\Requests\StoreSanctumTokenRequest;
use Ducal\Api\Models\PersonalAccessToken;
use Ducal\Base\Forms\FieldOptions\NameFieldOption;
use Ducal\Base\Forms\Fields\TextField;
use Ducal\Base\Forms\FormAbstract;

class SanctumTokenForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new PersonalAccessToken())
            ->setValidatorClass(StoreSanctumTokenRequest::class)
            ->add('name', TextField::class, NameFieldOption::make()->toArray());
    }
}
