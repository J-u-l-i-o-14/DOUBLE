@extends('layouts.app')

@section('title', 'Dashboard Client')

@push('styles')
<style>
    .gradient-bg {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .card-hover {
        transition: all 0.3s ease;
    }
    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    .stat-card {
        background: linear-gradient(45deg, #667eea, #764ba2);
        color: white;
        border-radius: 15px;
        padding: 25px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,0.1);
        transform: rotate(45deg);
        transition: all 0.3s ease;
    }
    .stat-card:hover::before {
        top: -10%;
        right: -10%;
    }
    .blood-type-badge {
        background: linear-gradient(45deg, #e74c3c, #c0392b);
        color: white;
        padding: 8px 16px;
        border-radius: 25px;
        font-weight: bold;
        display: inline-block;
        box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
    }
    .campaign-card {
        border-left: 5px solid #e74c3c;
        background: linear-gradient(90deg, #fff5f5 0%, #ffffff 100%);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }
    .campaign-card:hover {
        background: linear-gradient(90deg, #ffeaea 0%, #ffffff 100%);
        transform: translateX(5px);
    }
    .table-modern {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    .table-modern thead {
        background: linear-gradient(45deg, #667eea, #764ba2);
        color: white;
    }
    .table-modern tbody tr:hover {
        background: linear-gradient(90deg, #f8f9ff 0%, #ffffff 100%);
    }
    .welcome-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }
    .welcome-section::before {
        content: '🩸';
        position: absolute;
        top: -20px;
        right: -20px;
        font-size: 120px;
        opacity: 0.1;
    }
    .action-button {
        background: linear-gradient(45deg, #e74c3c, #c0392b);
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 25px;
        font-weight: bold;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
    }
    .action-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(231, 76, 60, 0.4);
        color: white;
        text-decoration: none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Section de bienvenue -->
    <div class="welcome-section">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="h2 mb-3">🩸 Bienvenue dans votre espace client</h1>
                <p class="mb-0 opacity-90">Gérez vos réservations de sang et suivez la disponibilité des stocks en temps réel.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="{{ route('blood.reservation') }}" class="action-button">
                    <i class="fas fa-plus me-2"></i>Nouvelle Réservation
                </a>
            </div>
        </div>
    </div>

    <!-- Statistiques principales avec design moderne -->
    <div class="row g-4 mb-5">
        <div class="col-lg-4 col-md-6">
            <div class="stat-card card-hover">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="h1 mb-0">{{ $stats['available_blood_bags'] }}</h3>
                        <p class="mb-0">Poches disponibles</p>
                    </div>
                    <i class="fas fa-tint fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="stat-card card-hover" style="background: linear-gradient(45deg, #27ae60, #2ecc71);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="h1 mb-0">{{ $stats['upcoming_campaigns'] }}</h3>
                        <p class="mb-0">Campagnes à venir</p>
                    </div>
                    <i class="fas fa-bullhorn fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="stat-card card-hover" style="background: linear-gradient(45deg, #8e44ad, #9b59b6);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="h1 mb-0">{{ $stats['total_donors'] }}</h3>
                        <p class="mb-0">Donneurs actifs</p>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Stock par groupe sanguin -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm card-hover" style="border-radius: 15px;">
                <div class="card-header bg-transparent border-0 pt-4">
                    <h4 class="text-dark mb-0">
                        <i class="fas fa-chart-pie text-danger me-2"></i>
                        Stock par groupe sanguin
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($stockByBloodType as $bloodType => $count)
                        <div class="col-6 col-md-4">
                            <div class="text-center p-3 rounded" style="background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%); border: 2px solid #ffe6e6;">
                                <div class="blood-type-badge mb-2">{{ $bloodType }}</div>
                                <div class="fw-bold text-dark">{{ $count }} poches</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Prochaines campagnes -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm card-hover" style="border-radius: 15px;">
                <div class="card-header bg-transparent border-0 pt-4">
                    <h4 class="text-dark mb-0">
                        <i class="fas fa-calendar-alt text-success me-2"></i>
                        Prochaines campagnes
                    </h4>
                </div>
                <div class="card-body">
                    @forelse($upcomingCampaigns as $campaign)
                    <div class="campaign-card">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <h6 class="fw-bold text-dark mb-2">{{ $campaign->name }}</h6>
                                <p class="small text-muted mb-1">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ optional($campaign->campaign_date)->format('d/m/Y H:i') }}
                                </p>
                                <p class="small text-muted mb-1">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    {{ $campaign->location }}
                                </p>
                                <p class="small text-muted mb-0">
                                    <i class="fas fa-user me-1"></i>
                                    Organisé par {{ optional($campaign->organizer)->name }}
                                </p>
                            </div>
                            <span class="badge bg-success">Actif</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucune campagne à venir</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Poches de sang disponibles -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm card-hover" style="border-radius: 15px;">
                <div class="card-header bg-transparent border-0 pt-4">
                    <h4 class="text-dark mb-0">
                        <i class="fas fa-list-alt text-primary me-2"></i>
                        Poches de sang disponibles
                    </h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 border-0">
                                        <i class="fas fa-tint me-2"></i>Groupe sanguin
                                    </th>
                                    <th class="px-4 py-3 border-0">
                                        <i class="fas fa-user me-2"></i>Donneur
                                    </th>
                                    <th class="px-4 py-3 border-0">
                                        <i class="fas fa-calendar me-2"></i>Date de collecte
                                    </th>
                                    <th class="px-4 py-3 border-0">
                                        <i class="fas fa-clock me-2"></i>Expiration
                                    </th>
                                    <th class="px-4 py-3 border-0">
                                        <i class="fas fa-check-circle me-2"></i>Statut
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($availableBloodBags as $bloodBag)
                                <tr>
                                    <td class="px-4 py-3">
                                        <span class="blood-type-badge" style="font-size: 0.85rem; padding: 4px 12px;">
                                            {{ $bloodBag->blood_type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="fw-medium text-dark">
                                            {{ optional($bloodBag->donor)->name ?? 'Anonyme' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-muted">
                                            {{ optional($bloodBag->collection_date)->format('d/m/Y') ?? 'Non définie' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-muted">
                                            {{ optional($bloodBag->expiration_date)->format('d/m/Y') ?? 'Non définie' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="fas fa-check me-1"></i>Disponible
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-5 text-center">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <div class="text-muted">Aucune poche de sang disponible</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm card-hover" style="border-radius: 15px; background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);">
                <div class="card-body text-center py-5">
                    <h5 class="text-dark mb-4">
                        <i class="fas fa-rocket text-primary me-2"></i>
                        Actions rapides
                    </h5>
                    <div class="row g-3 justify-content-center">
                        <div class="col-md-3">
                            <a href="{{ route('blood.reservation') }}" class="action-button d-block py-3">
                                <i class="fas fa-shopping-cart me-2"></i>
                                Réserver du sang
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('campaigns.public') }}" class="action-button d-block py-3" style="background: linear-gradient(45deg, #27ae60, #2ecc71);">
                                <i class="fas fa-bullhorn me-2"></i>
                                Voir les campagnes
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('orders.index') }}" class="action-button d-block py-3" style="background: linear-gradient(45deg, #8e44ad, #9b59b6);">
                                <i class="fas fa-history me-2"></i>
                                Mes commandes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 