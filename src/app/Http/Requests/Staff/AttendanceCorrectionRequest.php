<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

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
    public function rules(): array
    {
        // 基本ルール
        $rules = [
            'requested_clock_in'  => ['required', 'date_format:H:i'],
            'requested_clock_out' => ['required', 'date_format:H:i'],
            'reason'              => ['required', 'string', 'max:255'],
        ];

        // 休憩フィールドの動的追加
        foreach ($this->getBreakFields() as $field) {
            $rules[$field] = ['nullable', 'date_format:H:i'];
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'requested_clock_in.required'  => '出勤時刻を入力してください。',
            'requested_clock_in.date_format'  => '出勤時刻は「HH:MM」形式で入力してください。',
            'requested_clock_out.required' => '退勤時刻を入力してください。',
            'requested_clock_out.date_format' => '退勤時刻は「HH:MM」形式で入力してください。',
            'reason.required'              => '備考を記入してください。',
            'reason.string'                => '備考は文字列で入力してください。',
            'reason.max'                   => '備考は255文字以内で入力してください。',
        ];

        // 動的休憩用メッセージ
        foreach ($this->getBreakFields() as $field) {
            $messages[$field . '.date_format'] = '休憩時間は「HH:MM」形式で入力してください。';
        }

        return $messages;
    }

    /**
     * 動的に休憩フィールドを取得
     */
    private function getBreakFields(): array
    {
        // breakX_start / breakX_end 形式のフィールドを抽出
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
            $in  = $this->input('requested_clock_in');
            $out = $this->input('requested_clock_out');

            if ($in && $out) {
                $inTime  = Carbon::createFromFormat('H:i', $in);
                $outTime = Carbon::createFromFormat('H:i', $out);

                // 出勤・退勤整合チェック
                if ($inTime->gte($outTime)) {
                    $validator->errors()->add('requested_clock_in', '出勤時間もしくは退勤時間が不適切な値です');
                }

                // 休憩時間整合チェック
                foreach ($this->getBreakFields() as $field) {
                    $val = $this->input($field);
                    if ($val) {
                        $breakTime = Carbon::createFromFormat('H:i', $val);
                        if ($breakTime->lt($inTime) || $breakTime->gt($outTime)) {
                            $validator->errors()->add($field, '休憩時間が勤務時間外です');
                        }
                    }
                }
            }
        });
    }
}
