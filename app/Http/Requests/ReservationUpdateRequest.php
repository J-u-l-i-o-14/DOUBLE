<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReservationUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'manager']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in(['pending', 'confirmed', 'cancelled', 'completed', 'expired'])
            ],
            'note' => 'nullable|string|max:1000',
            'manager_notes' => 'nullable|string|max:1000'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Le statut est obligatoire.',
            'status.in' => 'Le statut sélectionné n\'est pas valide.',
            'note.max' => 'La note ne peut pas dépasser 1000 caractères.',
            'manager_notes.max' => 'Les notes du gestionnaire ne peuvent pas dépasser 1000 caractères.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Nettoyer les données avant validation
        $data = [];
        
        if ($this->has('status')) {
            // S'assurer que le statut est une chaîne propre
            $status = trim($this->input('status'));
            $status = preg_replace('/[^a-z_]/', '', strtolower($status));
            $data['status'] = $status;
        }
        
        if ($this->has('note')) {
            $data['note'] = trim($this->input('note'));
        }
        
        if ($this->has('manager_notes')) {
            $data['manager_notes'] = trim($this->input('manager_notes'));
        }
        
        $this->merge($data);
    }
}
