<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReservationStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'center_id' => [
                'nullable', 
                'numeric',
                'exists:centers,id',
                function ($attribute, $value, $fail) {
                    // Vérifier que la valeur est un entier strict
                    if ($value !== null && (!is_numeric($value) || intval($value) != floatval($value))) {
                        $fail('Le centre doit être un nombre entier valide.');
                    }
                }
            ],
            'items' => 'required|array|min:1|max:10', // Limiter le nombre d'items
            'items.*.blood_type_id' => [
                'required',
                'numeric',
                'exists:blood_types,id',
                function ($attribute, $value, $fail) {
                    // Vérifier que la valeur est un entier strict
                    if (!is_numeric($value) || intval($value) != floatval($value) || intval($value) <= 0) {
                        $fail('Le type sanguin doit être un nombre entier positif valide.');
                    }
                }
            ],
            'items.*.quantity' => [
                'required',
                'numeric',
                'min:1',
                'max:50', // Limiter la quantité maximale
                function ($attribute, $value, $fail) {
                    // Vérifier que la valeur est un entier strict
                    if (!is_numeric($value) || intval($value) != floatval($value) || intval($value) <= 0) {
                        $fail('La quantité doit être un nombre entier positif valide.');
                    }
                }
            ],
            'urgent' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'center_id.exists' => 'Le centre sélectionné n\'existe pas.',
            'center_id.numeric' => 'Le centre doit être un nombre valide.',
            'items.required' => 'Vous devez sélectionner au moins un type de sang.',
            'items.min' => 'Vous devez sélectionner au moins un type de sang.',
            'items.max' => 'Vous ne pouvez pas sélectionner plus de 10 types de sang.',
            'items.*.blood_type_id.required' => 'Le type sanguin est obligatoire.',
            'items.*.blood_type_id.exists' => 'Le type sanguin sélectionné n\'existe pas.',
            'items.*.blood_type_id.numeric' => 'Le type sanguin doit être un nombre valide.',
            'items.*.quantity.required' => 'La quantité est obligatoire.',
            'items.*.quantity.min' => 'La quantité doit être d\'au moins 1.',
            'items.*.quantity.max' => 'La quantité ne peut pas dépasser 50 unités.',
            'items.*.quantity.numeric' => 'La quantité doit être un nombre valide.',
            'notes.max' => 'Les notes ne peuvent pas dépasser 1000 caractères.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Nettoyer et sécuriser les données avant validation
        $items = $this->input('items', []);
        
        // Nettoyer chaque item
        foreach ($items as $key => $item) {
            if (isset($item['blood_type_id'])) {
                // Supprimer tout caractère non numérique et s'assurer que c'est un entier
                $bloodTypeId = preg_replace('/[^0-9]/', '', $item['blood_type_id']);
                $items[$key]['blood_type_id'] = $bloodTypeId !== '' ? (int)$bloodTypeId : null;
            }
            
            if (isset($item['quantity'])) {
                // Supprimer tout caractère non numérique et s'assurer que c'est un entier
                $quantity = preg_replace('/[^0-9]/', '', $item['quantity']);
                $items[$key]['quantity'] = $quantity !== '' ? (int)$quantity : null;
            }
        }
        
        $this->merge([
            'items' => $items
        ]);
        
        // Nettoyer center_id si présent
        if ($this->has('center_id')) {
            $centerId = preg_replace('/[^0-9]/', '', $this->input('center_id'));
            $this->merge([
                'center_id' => $centerId !== '' ? (int)$centerId : null
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validation supplémentaire pour s'assurer que tous les IDs sont des entiers positifs
            $items = $this->input('items', []);
            
            foreach ($items as $index => $item) {
                if (isset($item['blood_type_id']) && (!is_int($item['blood_type_id']) || $item['blood_type_id'] <= 0)) {
                    $validator->errors()->add("items.{$index}.blood_type_id", 'Le type sanguin doit être un nombre entier positif.');
                }
                
                if (isset($item['quantity']) && (!is_int($item['quantity']) || $item['quantity'] <= 0)) {
                    $validator->errors()->add("items.{$index}.quantity", 'La quantité doit être un nombre entier positif.');
                }
            }
            
            // Vérifier la cohérence des données
            $bloodTypeIds = array_column($items, 'blood_type_id');
            if (count($bloodTypeIds) !== count(array_unique($bloodTypeIds))) {
                $validator->errors()->add('items', 'Vous ne pouvez pas sélectionner le même type sanguin plusieurs fois.');
            }
        });
    }
}
