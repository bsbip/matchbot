<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;

class CreateCustomEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $minUsers = Config::get('match.min_users');

        return [
            'users' => [
                'array',
                "size:{$minUsers}",
            ],
            'users.*.id' => [
                'required',
                'distinct',
            ],
        ];
    }
}
