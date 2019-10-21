<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
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
        return [
            'event_id' => [
                'required',
                Rule::exists('events', 'id'),
                Rule::exists('event_teams', 'event_id'),
            ],
            'teams.*.score' => [
                'required',
            ],
            'teams.*.crawl_score' => [
                'required',
            ],
            'note' => [
                'present',
            ],
        ];
    }
}
