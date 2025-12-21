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

            'breaks.new.start' => 'nullable|date_format:H:i',
            'breaks.new.end'   => 'nullable|date_format:H:i',

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

            'breaks.new.start.date_format' => '休憩開始時間の形式が不正です',
            'breaks.new.end.date_format'   => '休憩終了時間の形式が不正です',

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

            $allBreaks = array_merge(
                $this->input('breaks', []),
                ['new' => $this->input('breaks.new', [])]
            );

            foreach ($allBreaks as $key => $b) {
                $start = $b['start'] ?? null;
                $end   = $b['end'] ?? null;

                if ($start) {
                    if ($clockIn && $clockOut && ($start < $clockIn || $start > $clockOut)) {
                        $validator->errors()->add(
                            "breaks.$key.start",
                            '休憩時間が不適切な値です'
                        );
                    }
                }

                if ($end) {
                    if ($clockOut && $end > $clockOut) {
                        $validator->errors()->add(
                            "breaks.$key.end",
                            '休憩時間もしくは退勤時間が不適切な値です'
                        );
                    }
                    if ($start && $end <= $start) {
                        $validator->errors()->add(
                            "breaks.$key.end",
                            '休憩時間が不適切な値です'
                        );
                    }
                }
            }
        });
    }
}