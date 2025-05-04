<?php

namespace Ducal\Api\Http\Controllers;

use Ducal\Api\Forms\SanctumTokenForm;
use Ducal\Api\Http\Requests\StoreSanctumTokenRequest;
use Ducal\Api\Models\PersonalAccessToken;
use Ducal\Api\Tables\SanctumTokenTable;
use Ducal\Base\Http\Actions\DeleteResourceAction;
use Ducal\Base\Http\Controllers\BaseController;
use Ducal\Base\Http\Responses\BaseHttpResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class SanctumTokenController extends BaseController
{
    public function index(SanctumTokenTable $sanctumTokenTable): JsonResponse|View
    {
        $this->pageTitle(trans('packages/api::sanctum-token.name'));

        return $sanctumTokenTable->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('packages/api::sanctum-token.create'));

        return SanctumTokenForm::create()->renderForm();
    }

    public function store(StoreSanctumTokenRequest $request): BaseHttpResponse
    {
        $accessToken = $request->user()->createToken($request->input('name'));

        session()->flash('plainTextToken', $accessToken->plainTextToken);

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('api.sanctum-token.index'))
            ->setNextUrl(route('api.sanctum-token.index'))
            ->withCreatedSuccessMessage();
    }

    public function destroy(PersonalAccessToken $sanctumToken): DeleteResourceAction
    {
        return DeleteResourceAction::make($sanctumToken);
    }
}
