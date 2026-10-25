<?php

namespace App\Http\Requests\Formula;

use App\Support\CurrentFarm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnalyzeFormulaRequest extends FormRequest
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
        $farmId = app(CurrentFarm::class)->id();

        return [
            'phase' => ['required', Rule::in(StoreFormulaRequest::PHASES)],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.ingredient_id' => ['required', Rule::exists('ingredients', 'id')->where('farm_id', $farmId)],
            'lines.*.percent' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
