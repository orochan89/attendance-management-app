<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

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
    public function rules(): array
    {
        $rules = [
            'clock_in_time'  => ['required', 'date_format:H:i'],
            'clock_out_time' => ['required', 'date_format:H:i'],
            'reason'              => ['required', 'string', 'max:255'],
        ];

        foreach ($this->getBreakFields() as $field) {
            $rules[$field] = ['nullable', 'date_format:H:i'];
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'clock_in_time.required'  => '出勤時刻を入力してください。',
            'clock_in_time.date_format'  => '出勤時刻は「HH:MM」形式で入力してください',
            'clock_out_time.required' => '退勤時刻を入力してください',
            'clock_out_time.date_format' => '退勤時刻は「HH:MM」形式で入力してください',
            'reason.required'              => '備考を記入してください',
            'reason.string'                => '備考は文字列で入力してください',
            'reason.max'                   => '備考は255文字以内で入力してください',
        ];

        foreach ($this->getBreakFields() as $field) {
            $messages[$field . '.date_format'] = '休憩時間は「HH:MM」形式で入力してください';
        }

        return $messages;
    }

    private function getBreakFields(): array
    {
        $fields = [];
        foreach ($this->keys() as $key) {
            if (preg_match('/^break\d+_(start|end)$/', $key)) {
                $fields[] = $key;
            }
        }
        return $fields;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $in  = $this->input('clock_in_time');
            $out = $this->input('clock_out_time');

            if ($in && $out) {
                $inTime  = Carbon::createFromFormat('H:i', $in);
                $outTime = Carbon::createFromFormat('H:i', $out);

                if ($inTime->gte($outTime)) {
                    $validator->errors()->add('clock_in_time', '出勤時間もしくは退勤時間が不適切な値です');
                }

                foreach ($this->input('breaks', []) as $index => $break) {
                    if (!empty($break['start'])) {
                        $start = Carbon::createFromFormat('H:i', $break['start']);
                        if ($start->lt($inTime) || $start->gt($outTime)) {
                            $validator->errors()->add("breaks.$index.start", '出勤時間もしくは退勤時間が不適切な値です');
                        }
                    }
                    if (!empty($break['end'])) {
                        $end = Carbon::createFromFormat('H:i', $break['end']);
                        if ($end->lt($inTime) || $end->gt($outTime)) {
                            $validator->errors()->add("breaks.$index.end", '出勤時間もしくは退勤時間が不適切な値です');
                        }
                    }
                }
            }
        });
    }
}
