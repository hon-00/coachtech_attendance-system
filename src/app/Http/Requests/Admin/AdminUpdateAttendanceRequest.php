<?php

namespace App\Http\Requests;

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
            'clock_in'           => 'required|date_format:H:i',
            'clock_out'          => 'required|date_format:H:i|after_or_equal:clock_in',

            'breaks.*.start'     => 'nullable|date_format:H:i|after_or_equal:clock_in|before_or_equal:clock_out',
            'breaks.*.end'       => 'nullable|date_format:H:i|after_or_equal:breaks.*.start|before_or_equal:clock_out',

            'note'               => 'required|max:255',
        ];
    }
}
