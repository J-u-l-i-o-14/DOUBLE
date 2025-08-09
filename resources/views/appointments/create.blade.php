@extends('layouts.app')

@section('title', 'Prendre un rendez-vous')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-calendar-plus me-2"></i>
                        Prendre un rendez-vous
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('appointments.store') }}">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="appointment_date" class="form-label">Date du rendez-vous <span class="text-danger">*</span></label>
                                <input type="date" 
                                       name="appointment_date" 
                                       id="appointment_date" 
                                       class="form-control @error('appointment_date') is-invalid @enderror" 
                                       value="{{ old('appointment_date') }}"
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                       required>
                                @error('appointment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Les rendez-vous doivent être pris au minimum le lendemain
                                </small>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="appointment_time" class="form-label">Heure du rendez-vous <span class="text-danger">*</span></label>
                                <select name="appointment_time" 
                                        id="appointment_time" 
                                        class="form-select @error('appointment_time') is-invalid @enderror" 
                                        required>
                                    <option value="">Sélectionnez une heure</option>
                                    <option value="08:00" {{ old('appointment_time') == '08:00' ? 'selected' : '' }}>08:00</option>
                                    <option value="08:30" {{ old('appointment_time') == '08:30' ? 'selected' : '' }}>08:30</option>
                                    <option value="09:00" {{ old('appointment_time') == '09:00' ? 'selected' : '' }}>09:00</option>
                                    <option value="09:30" {{ old('appointment_time') == '09:30' ? 'selected' : '' }}>09:30</option>
                                    <option value="10:00" {{ old('appointment_time') == '10:00' ? 'selected' : '' }}>10:00</option>
                                    <option value="10:30" {{ old('appointment_time') == '10:30' ? 'selected' : '' }}>10:30</option>
                                    <option value="11:00" {{ old('appointment_time') == '11:00' ? 'selected' : '' }}>11:00</option>
                                    <option value="11:30" {{ old('appointment_time') == '11:30' ? 'selected' : '' }}>11:30</option>
                                    <option value="14:00" {{ old('appointment_time') == '14:00' ? 'selected' : '' }}>14:00</option>
                                    <option value="14:30" {{ old('appointment_time') == '14:30' ? 'selected' : '' }}>14:30</option>
                                    <option value="15:00" {{ old('appointment_time') == '15:00' ? 'selected' : '' }}>15:00</option>
                                    <option value="15:30" {{ old('appointment_time') == '15:30' ? 'selected' : '' }}>15:30</option>
                                    <option value="16:00" {{ old('appointment_time') == '16:00' ? 'selected' : '' }}>16:00</option>
                                    <option value="16:30" {{ old('appointment_time') == '16:30' ? 'selected' : '' }}>16:30</option>
                                    <option value="17:00" {{ old('appointment_time') == '17:00' ? 'selected' : '' }}>17:00</option>
                                </select>
                                @error('appointment_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Type de rendez-vous <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="type" 
                                               id="type_centre" 
                                               value="centre" 
                                               {{ old('type') == 'centre' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label" for="type_centre">
                                            <i class="fas fa-hospital me-2 text-primary"></i>
                                            <strong>Rendez-vous en centre</strong>
                                            <br>
                                            <small class="text-muted">Don de sang dans un centre fixe</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="type" 
                                               id="type_campagne" 
                                               value="campagne" 
                                               {{ old('type') == 'campagne' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label" for="type_campagne">
                                            <i class="fas fa-users me-2 text-success"></i>
                                            <strong>Campagne de don</strong>
                                            <br>
                                            <small class="text-muted">Participation à une campagne</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @error('type')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="campaign_section" style="display: none;">
                            <label for="campaign_id" class="form-label">Campagne <span class="text-danger">*</span></label>
                            <select name="campaign_id" 
                                    id="campaign_id" 
                                    class="form-select @error('campaign_id') is-invalid @enderror">
                                <option value="">Sélectionnez une campagne</option>
                                @forelse($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}" {{ old('campaign_id') == $campaign->id ? 'selected' : '' }}>
                                        {{ $campaign->name }} 
                                        @if($campaign->start_date && $campaign->end_date)
                                            ({{ $campaign->start_date->format('d/m/Y') }} - {{ $campaign->end_date->format('d/m/Y') }})
                                        @endif
                                    </option>
                                @empty
                                    <option value="" disabled>Aucune campagne disponible</option>
                                @endforelse
                            </select>
                            @error('campaign_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (optionnel)</label>
                            <textarea name="notes" 
                                      id="notes" 
                                      class="form-control @error('notes') is-invalid @enderror" 
                                      rows="3" 
                                      placeholder="Informations complémentaires ou demandes particulières...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Informations importantes :</strong>
                            <ul class="mb-0 mt-2">
                                <li>Les rendez-vous doivent être pris au minimum le lendemain</li>
                                <li>Vous recevrez une confirmation par email</li>
                                <li>Pensez à vous munir d'une pièce d'identité le jour du don</li>
                                <li>Il est recommandé de bien manger et s'hydrater avant le don</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('appointments.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-calendar-check me-1"></i>
                                Confirmer le rendez-vous
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeRadios = document.querySelectorAll('input[name="type"]');
    const campaignSection = document.getElementById('campaign_section');
    const campaignSelect = document.getElementById('campaign_id');
    
    function toggleCampaignSection() {
        const selectedType = document.querySelector('input[name="type"]:checked');
        if (selectedType && selectedType.value === 'campagne') {
            campaignSection.style.display = 'block';
            campaignSelect.setAttribute('required', 'required');
        } else {
            campaignSection.style.display = 'none';
            campaignSelect.removeAttribute('required');
            campaignSelect.value = '';
        }
    }
    
    typeRadios.forEach(radio => {
        radio.addEventListener('change', toggleCampaignSection);
    });
    
    // Appeler au chargement de la page pour l'état initial
    toggleCampaignSection();
    
    // Validation de date côté client
    const dateInput = document.getElementById('appointment_date');
    dateInput.addEventListener('change', function() {
        const selectedDate = new Date(this.value);
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        tomorrow.setHours(0, 0, 0, 0);
        
        if (selectedDate < tomorrow) {
            alert('La date du rendez-vous doit être au minimum le lendemain.');
            this.value = '';
        }
    });
});
</script>
@endsection
