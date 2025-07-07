<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateAttendanceRequest extends FormRequest
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
            'clock_in_time' => ['nullable', 'date_format:H:i'],
            'Clock_out_time' => ['nullable', 'date_format:H:i', 'after_or_equal:clock_in_time'],
            'note' => ['nullable', 'string', 'max:255']
        ];
    }
}
