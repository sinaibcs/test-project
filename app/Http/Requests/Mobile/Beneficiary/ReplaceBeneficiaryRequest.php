<?php

namespace App\Http\Requests\Mobile\Beneficiary;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class ReplaceBeneficiaryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
//            'beneficiary_id' => 'required|integer|exists:beneficiaries,id',
            'replace_with_ben_id' => 'required|integer|exists:beneficiaries,id',
            'cause_id' => 'required|integer|exists:lookups,id',
            'cause_detail' => 'nullable|string|max:250',
            'cause_date' => 'nullable|date',
            'cause_proof_doc' => [
                'nullable',
                File::types(['pdf', 'jpeg', 'jpg', 'png'])
                    ->max('10mb'),
            ],
        ];
    }
}
