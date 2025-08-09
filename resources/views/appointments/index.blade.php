@extends('layouts.app')

@section('title', 'Mes Rendez-vous')
@section('page-title', auth()->user()->is_donor ? 'Mes Rendez-vous' : 'Gestion des Rendez-vous')

@section('page-actions')
@if(auth()->user()->is_donor)
    <a href="{{ route('appointments.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>
        Nouveau Rendez-vous
    </a>
@endif
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Liste des Rendez-vous
                </h5>
            </div>
            @if(!auth()->user()->is_donor)
            <div class="col-auto">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                        <i class="fas fa-filter me-1"></i>
                        Filtres
                    </button>
                </div>
            </div>
            @endif
        </div>
        
        @if(!auth()->user()->is_donor)
        <div class="collapse mt-3" id="filtersCollapse">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tous les statuts</option>
                        <option value="planifie" {{ request('status') === 'planifie' ? 'selected' : '' }}>Planifié</option>
                        <option value="confirme" {{ request('status') === 'confirme' ? 'selected' : '' }}>Confirmé</option>
                        <option value="complete" {{ request('status') === 'complete' ? 'selected' : '' }}>Complété</option>
                        <option value="annule" {{ request('status') === 'annule' ? 'selected' : '' }}>Annulé</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Tous les types</option>
                        <option value="centre" {{ request('type') === 'centre' ? 'selected' : '' }}>Centre</option>
                        <option value="campagne" {{ request('type') === 'campagne' ? 'selected' : '' }}>Campagne</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date à partir de</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" 
                           value="{{ request('date_from') }}"
                           min="{{ date('Y-m-d', strtotime('-1 year')) }}"
                           max="{{ date('Y-m-d', strtotime('+6 months')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search me-1"></i>
                            Filtrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
        @endif
    </div>
    
    <div class="card-body">
        @if($appointments->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            @if(!auth()->user()->is_donor)
                                <th>Donneur</th>
                            @endif
                            <th>Date & Heure</th>
                            <th>Type</th>
                            <th>Campagne</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($appointments as $appointment)
                        <tr>
                            @if(!auth()->user()->is_donor)
                                <td>
                                    <div>
                                        <strong>{{ $appointment->donor->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $appointment->donor->email }}</small>
                                        @if($appointment->donor->blood_type)
                                            <br>
                                            <span class="blood-type-badge blood-{{ substr($appointment->donor->blood_type, 0, -1) }}">
                                                {{ $appointment->donor->blood_type }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            @endif
                            <td>
                                <div>
                                    <strong>{{ $appointment->appointment_date->format('d/m/Y') }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $appointment->appointment_date->format('H:i') }}</small>
                                    @if($appointment->appointment_date->isToday())
                                        <br>
                                        <span class="badge bg-info">Aujourd'hui</span>
                                    @elseif($appointment->appointment_date->isTomorrow())
                                        <br>
                                        <span class="badge bg-warning">Demain</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $appointment->type === 'centre' ? 'primary' : 'success' }}">
                                    {{ ucfirst($appointment->type) }}
                                </span>
                            </td>
                            <td>
                                @if($appointment->campaign)
                                    <div>
                                        <strong>{{ $appointment->campaign->name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            {{ $appointment->campaign->location }}
                                        </small>
                                    </div>
                                @else
                                    <span class="text-muted">Centre principal</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'planifie' => 'secondary',
                                        'confirme' => 'primary',
                                        'complete' => 'success',
                                        'annule' => 'danger'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$appointment->status] ?? 'secondary' }}">
                                    {{ ucfirst($appointment->status) }}
                                </span>
                                @if($appointment->confirmed_at)
                                    <br>
                                    <small class="text-muted">
                                        Confirmé le {{ $appointment->confirmed_at->format('d/m/Y') }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-outline-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if(auth()->user()->is_donor && in_array($appointment->status, ['planifie', 'confirme']))
                                        <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-outline-primary" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    
                                    @if(!auth()->user()->is_donor && $appointment->status === 'planifie')
                                        <form method="POST" action="{{ route('appointments.confirm', $appointment) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-success" title="Confirmer">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if(in_array($appointment->status, ['planifie', 'confirme']))
                                        <form method="POST" action="{{ route('appointments.cancel', $appointment) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-danger" title="Annuler" 
                                                    onclick="return confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center">
                {{ $appointments->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucun rendez-vous trouvé</h5>
                @if(auth()->user()->is_donor)
                    <p class="text-muted">Vous n'avez pas encore de rendez-vous planifié.</p>
                    <a href="{{ route('appointments.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Prendre un rendez-vous
                    </a>
                @else
                    <p class="text-muted">Aucun rendez-vous ne correspond à vos critères de recherche.</p>
                @endif
            </div>
        @endif
    </div>
</div>

@if(!auth()->user()->is_donor)
<!-- Statistiques rapides -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h4>{{ $appointments->where('status', 'planifie')->count() }}</h4>
                <p class="mb-0">Planifiés</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4>{{ $appointments->where('status', 'confirme')->count() }}</h4>
                <p class="mb-0">Confirmés</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4>{{ $appointments->where('status', 'complete')->count() }}</h4>
                <p class="mb-0">Complétés</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h4>{{ $appointments->whereDate('appointment_date', today())->count() }}</h4>
                <p class="mb-0">Aujourd'hui</p>
            </div>
        </div>
    </div>
</div>
@endif
@endsection