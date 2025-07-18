<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
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
            'clock_in_time' => 'nullable|date_format:H:i',
            'clock_out_time' => 'nullable|date_format:H:i|after:clock_in_time',
            'break1_start' => 'nullable|date_format:H:i',
            'break1_end' => 'nullable|date_format:H:i|after:break1_start',
            'break2_start' => 'nullable|date_format:H:i',
            'break2_end' => 'nullable|date_format:H:i|after:break2_start',
            'note' => 'nullable|string|max:255',
        ];
    }
}
