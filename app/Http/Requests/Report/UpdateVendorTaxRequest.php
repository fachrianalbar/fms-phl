<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'tax_invoice_number' => $this->normalizeText($this->input('tax_invoice_number')),
            'withholding_tax_number' => $this->normalizeText($this->input('withholding_tax_number')),
            'withholding_tax_amount' => $this->parseNominal($this->input('withholding_tax_amount')),
            'withholding_tax_date' => $this->normalizeText($this->input('withholding_tax_date')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tax_invoice_number' => ['nullable', 'string', 'max:100'],
            'withholding_tax_number' => ['nullable', 'string', 'max:100'],
            'withholding_tax_amount' => ['nullable', 'numeric', 'min:0'],
            'withholding_tax_date' => ['nullable', 'date'],
        ];
    }

    private function normalizeText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function parseNominal(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $t = trim($value);
        if ($t === '') {
            return null;
        }

        if (! preg_match('/^-?\d{1,3}(\.\d{3})*$/', $t) && ! preg_match('/^-?\d+$/', $t)) {
            return $value;
        }

        return (int) str_replace('.', '', $t);
    }
}
