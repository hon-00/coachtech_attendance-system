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
            'work_date' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'clock_in'           => 'required|date_format:H:i',
            'clock_out'          => 'required|date_format:H:i|after_or_equal:clock_in',

            'breaks.*.start' => 'nullable|date_format:H:i|after_or_equal:clock_in|before_or_equal:clock_out','breaks.*.end'   => 'nullable|date_format:H:i|after_or_equal:breaks.*.start|before_or_equal:clock_out',

            'note'               => 'required|max:255',
        ];
    }

    public function messages()
    {
        return [
            'clock_out.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_in.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.required' => '出勤時間もしくは退勤時間が不適切な値です',

            'breaks.*.start.after_or_equal' => '休憩時間が不適切な値です',
            'breaks.*.start.before_or_equal' => '休憩時間が不適切な値です',

            'breaks.*.end.after_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',
            'breaks.*.end.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',

            'note.required' => '備考を記入してください',
            'note.max' => '備考は255文字以内で入力してください',
        ];
    }
}
