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
        return [
            'clock_in_time' => ['required', 'date_format:H:i'],
            'clock_out_time' => ['required', 'date_format:H:i'],
            'reason' => ['required', 'string', 'max:255'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end' => ['nullable', 'date_format:H:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'clock_in_time.required' => '出勤時刻を入力してください',
            'clock_out_time.required' => '退勤時刻を入力してください',
            'clock_in_time.date_format' => '出勤時刻は「HH:MM」形式で入力してください',
            'clock_out_time.date_format' => '退勤時刻は「HH:MM」形式で入力してください',
            'reason.required' => '備考を記入してください',
            'reason.string' => '備考は文字列で入力してください',
            'reason.max' => '備考は255文字以内で入力してください',
            'breaks.*.start.date_format' => '休憩開始時刻は「HH:MM」形式で入力してください',
            'breaks.*.end.date_format' => '休憩終了時刻は「HH:MM」形式で入力してください',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $in = $this->input('clock_in_time');
            $out = $this->input('clock_out_time');

            if ($in && $out) {
                $inTime = Carbon::createFromFormat('H:i', $in);
                $outTime = Carbon::createFromFormat('H:i', $out);

                if ($inTime->gte($outTime)) {
                    $validator->errors()->add('clock_in_time', '出勤時間もしくは退勤時間が不適切な値です。');
                }

                foreach ($this->input('breaks', []) as $index => $break) {
                    if (!empty($break['start'])) {
                        $start = Carbon::createFromFormat('H:i', $break['start']);
                        if ($start->lt($inTime) || $start->gt($outTime)) {
                            $validator->errors()->add("breaks.$index.start", '休憩時間が勤務時間外です。');
                        }
                    }
                    if (!empty($break['end'])) {
                        $end = Carbon::createFromFormat('H:i', $break['end']);
                        if ($end->lt($inTime) || $end->gt($outTime)) {
                            $validator->errors()->add("breaks.$index.end", '休憩時間が勤務時間外です。');
                        }
                    }
                }
            }
        });
    }
}
