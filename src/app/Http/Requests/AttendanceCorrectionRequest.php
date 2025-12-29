<?php

namespace App\Http\Requests;

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
            $clockInValue  = $this->input('clock_in');
            $clockOutValue = $this->input('clock_out');

            if ($clockInValue && $clockOutValue) {
                $clockIn  = Carbon::createFromFormat('H:i', $clockInValue);
                $clockOut = Carbon::createFromFormat('H:i', $clockOutValue);

                if ($clockIn->gte($clockOut)) {
                    $validator->errors()->add(
                        'clock_in',
                        '出勤時間もしくは退勤時間が不適切な値です'
                    );
                }

                $breaks = $this->input('breaks', []);
                $newBreak = $this->input('breaks.new', []);
                $allBreaks = $breaks;

                if (!empty($newBreak)) {
                    $allBreaks['new'] = $newBreak;
                }

                foreach ($allBreaks as $key => $b) {
                    $startKey = is_numeric($key) ? "breaks.$key.start" : "breaks.new.start";
                    $endKey   = is_numeric($key) ? "breaks.$key.end"   : "breaks.new.end";

                    $startValue = $b['start'] ?? '';
                    $endValue   = $b['end'] ?? '';

                    // Carbon 比較（空文字でない場合のみ）
                    $start = $startValue !== '' ? Carbon::createFromFormat('H:i', $startValue) : null;
                    $end   = $endValue   !== '' ? Carbon::createFromFormat('H:i', $endValue)   : null;

                    if ($start && ($start->lt($clockIn) || $start->gt($clockOut))) {
                        $validator->errors()->add($startKey, '休憩時間が不適切な値です');
                    }
                    if ($end && ($end->gt($clockOut) || $end->lt($clockIn) || ($start && $end->lte($start)))) {
                        $validator->errors()->add($endKey, '休憩時間が不適切な値です');
                    }
                }
            }
        });
    }

    protected function prepareForValidation()
    {
        $breaks = $this->input('breaks', []);
        foreach ($breaks as $key => $b) {
            $breaks[$key] = [
                'start' => $b['start'] ?? '',
                'end'   => $b['end'] ?? '',
            ];
        }

        $newBreak = $this->input('breaks.new', []);
        $newBreak = [
            'start' => $newBreak['start'] ?? '',
            'end'   => $newBreak['end'] ?? '',
        ];

        $this->merge([
            'breaks' => $breaks,
            'breaks.new' => $newBreak,
        ]);
    }
}