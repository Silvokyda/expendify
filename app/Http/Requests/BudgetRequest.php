<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BudgetRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = $this->user()->id;
        $id = $this->route('budget')?->id;

        return [
            'name' => [
                'required','string','max:64',
                Rule::unique('categories','name')
                    ->ignore($id)
                    ->where(fn($q)=>$q->where('user_id',$userId)),
            ],
            'monthly_limit' => ['nullable','numeric','min:0'],
        ];
    }
}
