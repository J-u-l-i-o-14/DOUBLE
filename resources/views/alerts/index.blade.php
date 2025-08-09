@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Gestion des Alertes</h1>
                <div class="btn-group">
                    <button type="button" class="btn btn-success" onclick="generateAlerts()">
                        <i class="fas fa-sync-alt"></i> Générer les alertes
                    </button>
                    @if(auth()->user()->role === 'admin')
                    <div class="dropdown">
                        <button class="btn btn-danger dropdown-toggle" type="button" data-toggle="dropdown">
                            <i class="fas fa-check-double"></i> Résoudre tout
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#" onclick="resolveAllByType('low_stock')">
                                Alertes de stock faible
                            </a>
                            <a class="dropdown-item" href="#" onclick="resolveAllByType('expiration')">
                                Alertes d'expiration
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Statistiques des alertes -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Alertes</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-bell fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Non Résolues</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['unresolved'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Stock Faible</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['low_stock'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-tint fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Expiration</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['expiration'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Filtres</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('alerts.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Statut</label>
                            <select class="form-control" name="status" id="status">
                                <option value="">Tous</option>
                                <option value="unresolved" {{ request('status') === 'unresolved' ? 'selected' : '' }}>Non résolues</option>
                                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Résolues</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-control" name="type" id="type">
                                <option value="">Tous</option>
                                <option value="low_stock" {{ request('type') === 'low_stock' ? 'selected' : '' }}>Stock faible</option>
                                <option value="expiration" {{ request('type') === 'expiration' ? 'selected' : '' }}>Expiration</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="submit" class="form-label">&nbsp;</label>
                            <button type="submit" class="form-control btn btn-primary">Filtrer</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Liste des alertes -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Liste des Alertes</h6>
                </div>
                <div class="card-body">
                    @if($alerts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Groupe Sanguin</th>
                                    <th>Message</th>
                                    <th>Date Création</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($alerts as $alert)
                                <tr class="{{ $alert->resolved ? '' : 'table-warning' }}">
                                    <td>
                                        @if($alert->type === 'low_stock')
                                            <span class="badge badge-danger">Stock Faible</span>
                                        @elseif($alert->type === 'expiration')
                                            <span class="badge badge-info">Expiration</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $alert->type }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">{{ $alert->bloodType->group }}</span>
                                    </td>
                                    <td>{{ $alert->message }}</td>
                                    <td>{{ $alert->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($alert->resolved)
                                            <span class="badge badge-success">Résolue</span>
                                        @else
                                            <span class="badge badge-warning">En attente</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            @if(!$alert->resolved)
                                                <button class="btn btn-success btn-sm" onclick="resolveAlert({{ $alert->id }})">
                                                    <i class="fas fa-check"></i> Résoudre
                                                </button>
                                            @else
                                                <button class="btn btn-warning btn-sm" onclick="unresolveAlert({{ $alert->id }})">
                                                    <i class="fas fa-undo"></i> Annuler
                                                </button>
                                            @endif
                                            @if(auth()->user()->role === 'admin')
                                                <button class="btn btn-danger btn-sm" onclick="deleteAlert({{ $alert->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="row">
                        <div class="col-sm-12 col-md-5">
                            <div class="dataTables_info">
                                Affichage de {{ $alerts->firstItem() ?? 0 }} à {{ $alerts->lastItem() ?? 0 }} 
                                sur {{ $alerts->total() }} alertes
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-7">
                            <div class="dataTables_paginate paging_simple_numbers">
                                {{ $alerts->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <p class="text-muted">Aucune alerte trouvée.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function resolveAlert(alertId) {
    if (confirm('Êtes-vous sûr de vouloir marquer cette alerte comme résolue ?')) {
        fetch(`/alerts/${alertId}/resolve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur: ' + data.message);
            }
        });
    }
}

function unresolveAlert(alertId) {
    if (confirm('Êtes-vous sûr de vouloir marquer cette alerte comme non résolue ?')) {
        fetch(`/alerts/${alertId}/unresolve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur: ' + data.message);
            }
        });
    }
}

function deleteAlert(alertId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer définitivement cette alerte ?')) {
        fetch(`/alerts/${alertId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur: ' + data.message);
            }
        });
    }
}

function resolveAllByType(type) {
    if (confirm(`Êtes-vous sûr de vouloir résoudre toutes les alertes de type "${type}" ?`)) {
        fetch('/alerts/resolve-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ type: type })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur: ' + data.message);
            }
        });
    }
}

function generateAlerts() {
    if (confirm('Êtes-vous sûr de vouloir générer les alertes maintenant ?')) {
        fetch('/alerts/generate', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Alertes générées avec succès');
                location.reload();
            } else {
                alert('Erreur: ' + data.message);
            }
        });
    }
}
</script>
@endsection
