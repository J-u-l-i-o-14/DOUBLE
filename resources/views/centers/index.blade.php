@extends('layouts.main')

@section('page-title', 'Gestion des centres')

@section('content')
<div class="space-y-8">
    <!-- Header + actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                <i class="fas fa-hospital-user mr-3 text-red-600"></i>Gestion des Centres
            </h1>
            <p class="text-gray-600 mt-1 text-sm">Administration des centres de collecte, informations et maintenance.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('centers.create') }}" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg shadow-sm">
                <i class="fas fa-plus mr-2"></i>Nouveau Centre
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex items-center">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mr-4">
                <i class="fas fa-hospital text-red-600"></i>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-semibold">Centres</p>
                <p class="text-2xl font-bold text-gray-900">{{ $centers->total() }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex items-center">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                <i class="fas fa-map-marker-alt text-blue-600"></i>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-semibold">Régions couvertes</p>
                <p class="text-2xl font-bold text-gray-900">{{ $centers->pluck('region_id')->unique()->filter()->count() }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex items-center">
            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mr-4">
                <i class="fas fa-check-circle text-green-600"></i>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-semibold">Actifs</p>
                <p class="text-2xl font-bold text-gray-900">{{ $centers->where('active', true)->count() }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex items-center">
            <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center mr-4">
                <i class="fas fa-tools text-yellow-600"></i>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-semibold">Maintenance</p>
                <p class="text-2xl font-bold text-gray-900">{{ $centers->where('active', false)->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Filtres / Recherche -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="text-xs font-medium text-gray-500 uppercase">Recherche</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Nom, adresse..." class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase">Région</label>
                <select name="region" class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                    <option value="">Toutes</option>
                    @foreach(($allRegions ?? collect()) as $region)
                        <option value="{{ $region->id }}" @selected(request('region')==$region->id)>{{ $region->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm hover:bg-gray-900">Filtrer</button>
                <a href="{{ route('centers.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Réinitialiser</a>
            </div>
        </form>
    </div>

    <!-- Table centres -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center"><i class="fas fa-database mr-2 text-red-600"></i>Liste des Centres</h2>
            <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700">{{ $centers->total() }} enregistrement(s)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Région</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adresse</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($centers as $center)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium text-gray-900 flex items-center">
                                <i class="fas fa-clinic-medical text-red-500 mr-2"></i>{{ $center->name }}
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-700">{{ optional($center->region)->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-700">{{ $center->address ?? '—' }}</td>
                            <td class="px-6 py-3">
                                @if($center->active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i>Actif</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"><i class="fas fa-tools mr-1"></i>Maintenance</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 flex items-center space-x-3 text-sm">
                                <a href="{{ route('centers.edit', $center) }}" class="text-blue-600 hover:text-blue-800 inline-flex items-center"><i class="fas fa-edit mr-1"></i>Modifier</a>
                                <form action="{{ route('centers.destroy', $center) }}" method="POST" onsubmit="return confirm('Supprimer ce centre ?')" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 inline-flex items-center"><i class="fas fa-trash mr-1"></i>Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500 text-sm">
                                <i class="fas fa-inbox text-2xl mb-2"></i>
                                <p>Aucun centre trouvé.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-gray-50 flex items-center justify-between">
            <div class="text-xs text-gray-500">Page {{ $centers->currentPage() }} / {{ $centers->lastPage() }}</div>
            <div>{{ $centers->withQueryString()->links() }}</div>
        </div>
    </div>
</div>
@endsection