<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReservationBulkUpdateRequest extends FormRequest
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
            'reservation_ids' => 'required|array|min:1|max:100', // Limiter le nombre d'IDs
            'reservation_ids.*' => [
                'required',
                'numeric',
                'exists:reservation_requests,id',
                function ($attribute, $value, $fail) {
                    // Vérifier que la valeur est un entier strict
                    if (!is_numeric($value) || intval($value) != floatval($value) || intval($value) <= 0) {
                        $fail('L\'ID de réservation doit être un nombre entier positif valide.');
                    }
                }
            ],
            'status' => [
                'required',
                'string',
                Rule::in(['confirmed', 'cancelled', 'completed', 'expired'])
            ]
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'reservation_ids.required' => 'Vous devez sélectionner au moins une réservation.',
            'reservation_ids.array' => 'Les IDs de réservation doivent être un tableau.',
            'reservation_ids.min' => 'Vous devez sélectionner au moins une réservation.',
            'reservation_ids.max' => 'Vous ne pouvez pas traiter plus de 100 réservations à la fois.',
            'reservation_ids.*.required' => 'Chaque ID de réservation est obligatoire.',
            'reservation_ids.*.numeric' => 'Chaque ID de réservation doit être un nombre.',
            'reservation_ids.*.exists' => 'Une ou plusieurs réservations sélectionnées n\'existent pas.',
            'status.required' => 'Le nouveau statut est obligatoire.',
            'status.in' => 'Le statut sélectionné n\'est pas valide.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Nettoyer les IDs de réservation
        $reservationIds = $this->input('reservation_ids', []);
        
        if (is_array($reservationIds)) {
            $cleanIds = [];
            foreach ($reservationIds as $id) {
                // Supprimer tout caractère non numérique
                $cleanId = preg_replace('/[^0-9]/', '', $id);
                if ($cleanId !== '' && (int)$cleanId > 0) {
                    $cleanIds[] = (int)$cleanId;
                }
            }
            $reservationIds = array_unique($cleanIds); // Supprimer les doublons
        }
        
        // Nettoyer le statut
        $status = '';
        if ($this->has('status')) {
            $status = trim($this->input('status'));
            $status = preg_replace('/[^a-z_]/', '', strtolower($status));
        }
        
        $this->merge([
            'reservation_ids' => $reservationIds,
            'status' => $status
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validation supplémentaire pour s'assurer que tous les IDs sont des entiers positifs
            $reservationIds = $this->input('reservation_ids', []);
            
            foreach ($reservationIds as $index => $id) {
                if (!is_int($id) || $id <= 0) {
                    $validator->errors()->add("reservation_ids.{$index}", 'L\'ID de réservation doit être un nombre entier positif.');
                }
            }
        });
    }
}
