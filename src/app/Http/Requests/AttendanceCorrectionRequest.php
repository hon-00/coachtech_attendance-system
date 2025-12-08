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
            'clock_in'   => 'required|date_format:H:i',
            'clock_out'  => 'required|date_format:H:i',

            'breaks.*.start' => 'nullable|date_format:H:i',
            'breaks.*.end'   => 'nullable|date_format:H:i',

            'note' => 'required|string|max:255',
        ];
    }


    public function messages()
    {
        return [
            'clock_in.required'  => '出勤時間を入力してください',
            'clock_in.date_format'  => '出勤時間の形式が不正です',

            'clock_out.required' => '退勤時間を入力してください',
            'clock_out.date_format' => '退勤時間の形式が不正です',

            'breaks.*.start.date_format' => '休憩開始時間の形式が不正です',
            'breaks.*.end.date_format'   => '休憩終了時間の形式が不正です',
            'note.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $clockIn  = $this->input('clock_in');
            $clockOut = $this->input('clock_out');
            $breaks   = $this->input('breaks', []);

            if ($clockIn >= $clockOut) {
                $validator->errors()->add(
                    'clock_in',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            foreach ($breaks as $i => $b) {
                $start = $b['start'] ?? null;
                $end   = $b['end'] ?? null;

                if ($start) {
                    if ($start < $clockIn || $start > $clockOut) {
                        $validator->errors()->add(
                            "breaks.$i.start",
                            '休憩時間が不適切な値です'
                        );
                    }
                    if ($start === null || $start === '') {
                        $validator->errors()->add(
                            "breaks.$i.start",
                            '時間を入力してください'
                        );
                    }
                }

                if ($end) {
                    if ($end > $clockOut) {
                        $validator->errors()->add(
                            "breaks.$i.end",
                            '休憩時間もしくは退勤時間が不適切な値です'
                        );
                    }

                    if ($start && $end <= $start) {
                        $validator->errors()->add(
                            "breaks.$i.end",
                            '休憩時間が不適切な値です'
                        );
                    }

                    if ($end === null || $end === '') {
                        $validator->errors()->add(
                            "breaks.$i.end",
                            '時間を入力してください'
                        );
                    }
                }
            }
        });
    }
}
