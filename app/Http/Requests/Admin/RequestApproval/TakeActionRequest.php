<?php

namespace App\Http\Requests\Admin\RequestApproval;

use App\Models\RequestApproval;
use Illuminate\Foundation\Http\FormRequest;

class TakeActionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
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
            'request_approval_id' => 'required|numeric|exists:request_approvals,id',
            'action' => 'required|in:'
                . RequestApproval::ACTION_DECLINE . ','
                . RequestApproval::ACTION_APPROVE
        ];
    }
}
