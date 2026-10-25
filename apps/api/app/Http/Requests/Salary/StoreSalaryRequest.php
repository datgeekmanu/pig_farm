<?php

namespace App\Http\Requests\Salary;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nom_ouvrier' => ['required', 'string', 'max:255'],
            'poste' => ['nullable', 'string', 'max:255'],
            'montant' => ['required', 'integer', 'min:0'],
            'date_paiement' => ['required', 'date'],
            'mois_concerne' => ['required', 'regex:/^\d{4}-\d{2}$/'], // YYYY-MM
            'notes' => ['nullable', 'string'],
        ];
    }
}
