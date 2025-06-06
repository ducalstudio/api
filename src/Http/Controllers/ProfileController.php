<?php

namespace Ducal\Api\Http\Controllers;

use App\Http\Controllers\Controller;
use Ducal\Api\Facades\ApiHelper;
use Ducal\Api\Http\Resources\UserResource;
use Ducal\Base\Facades\BaseHelper;
use Ducal\Base\Http\Responses\BaseHttpResponse;
use Ducal\Media\Facades\RvMedia;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Get the user profile information.
     *
     * @group Profile
     * @authenticated
     */
    public function getProfile(Request $request, BaseHttpResponse $response)
    {
        return $response->setData(new UserResource($request->user()));
    }

    /**
     * Update Avatar
     *
     * @bodyParam avatar file required Avatar file.
     *
     * @group Profile
     * @authenticated
     */
    public function updateAvatar(Request $request, BaseHttpResponse $response)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => RvMedia::imageValidationRule(),
        ]);

        if ($validator->fails()) {
            return $response
                ->setError()
                ->setCode(422)
                ->setMessage(__('Data invalid!') . ' ' . implode(' ', $validator->errors()->all()) . '.');
        }

        try {
            $file = RvMedia::handleUpload($request->file('avatar'), 0, 'users');
            if (Arr::get($file, 'error') !== true) {
                $user = $request->user();
                $user->update(['avatar' => $file['data']->url]);

                return $response
                    ->setData([
                        'avatar' => $user->avatar_url,
                    ])
                    ->setMessage(__('Update avatar successfully!'));
            }

            return $response
                ->setError()
                ->setMessage(__('Update failed!'));
        } catch (Exception $ex) {
            return $response
                ->setError()
                ->setMessage($ex->getMessage());
        }
    }

    /**
     * Update profile
     *
     * @bodyParam name string required Name.
     * @bodyParam email string Email.
     * @bodyParam dob date nullable Date of birth (format: Y-m-d).
     * @bodyParam gender string Gender (male, female, other).
     * @bodyParam description string Description
     * @bodyParam phone string required Phone.
     *
     * @group Profile
     * @authenticated
     */
    public function updateProfile(Request $request, BaseHttpResponse $response)
    {
        $user = $request->user();
        $userId = $user->getKey();

        $validator = Validator::make($request->input(), [
            'first_name' => ['nullable', 'required_without:name', 'string', 'max:120', 'min:2'],
            'last_name' => ['nullable', 'required_without:name', 'string', 'max:120', 'min:2'],
            'name' => ['nullable', 'required_without:first_name', 'string', 'max:120', 'min:2'],
            'phone' => ['nullable', 'string', ...BaseHelper::getPhoneValidationRule(true)],
            'dob' => ['nullable', 'string', 'sometimes', 'date_format:' . BaseHelper::getDateFormat(), 'max:20'],
            'gender' => ['nullable', 'string', Rule::in(['male', 'female', 'other'])],
            'description' => ['nullable', 'string', 'max:1000'],
            'email' => [
                'nullable',
                'email',
                'max:60',
                'min:6',
                'unique:' . ApiHelper::getTable() . ',email,' . $userId,
            ],
        ]);

        if ($validator->fails()) {
            return $response
                ->setError()
                ->setCode(422)
                ->setMessage(__('Data invalid!') . ' ' . implode(' ', $validator->errors()->all()) . '.');
        }

        try {
            $data = $validator->validated();
            $user->fill($data);
            if (! empty($data['dob'])) {
                $user->dob = Carbon::parse($data['dob']);
            }
            $user->save();

            return $response
                ->setData($request->user()->toArray())
                ->setMessage(__('Update profile successfully!'));
        } catch (Exception $ex) {
            return $response
                ->setError()
                ->setMessage($ex->getMessage());
        }
    }

    /**
     * Update password
     *
     * @bodyParam password string required The new password of user.
     * @bodyParam old_password string required The current password of user.
     *
     * @group Profile
     * @authenticated
     */
    public function updatePassword(Request $request, BaseHttpResponse $response)
    {
        $validator = Validator::make($request->input(), [
            'password' => ['required', 'string', 'min:6', 'max:60'],
            'old_password' => ['required', 'string', 'min:6', 'max:60'],
        ]);

        if ($validator->fails()) {
            return $response
                ->setError()
                ->setCode(422)
                ->setMessage(__('Data invalid!') . ' ' . implode(' ', $validator->errors()->all()) . '.');
        }

        if (! Hash::check($request->input('old_password'), $request->user()->getAuthPassword())) {
            return $response
                ->setError()
                ->setCode(403)
                ->setMessage(__('Current password is not valid!'));
        }

        $request->user()->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return $response->setMessage(__('Update password successfully!'));
    }
}
