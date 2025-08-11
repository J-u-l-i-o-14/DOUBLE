@extends('layouts.main')
@section('page-title','Toutes les transactions')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 flex items-center">
            <i class="fas fa-exchange-alt mr-3 text-red-600"></i>Toutes les Transactions
        </h1>
        <a href="{{ route('orders.index') }}" class="text-sm text-gray-600 hover:text-gray-800 inline-flex items-center"><i class="fas fa-arrow-left mr-2"></i>Retour</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="md:col-span-2">
                <label class="text-xs font-medium text-gray-500 uppercase">Recherche</label>
                <input name="q" value="{{ request('q') }}" placeholder="Prescription, téléphone..." class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase">Statut paiement</label>
                <select name="payment_status" class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                    <option value="">Tous</option>
                    @foreach(['pending','partial','paid'] as $st)
                        <option value="{{ $st }}" @selected(request('payment_status')==$st)>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase">Période</label>
                <input type="date" name="from" value="{{ request('from') }}" class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
            </div>
            <div class="flex items-end gap-2">
                <button class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm hover:bg-gray-900">Filtrer</button>
                <a href="{{ route('orders.transactions') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Reset</a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ordonnance</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acompte</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reste</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Paiement</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Statut réservation</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @php
                        $query = \App\Models\Order::with(['user','reservationRequest'])
                            ->when(request('q'), function($q){
                                $term='%'.request('q').'%';
                                $q->where('prescription_number','like',$term)
                                  ->orWhere('phone_number','like',$term);
                            })
                            ->when(request('payment_status'), fn($q)=>$q->where('payment_status',request('payment_status')))
                            ->when(request('from'), fn($q)=>$q->whereDate('created_at','>=',request('from')))
                            ->latest();
                        $transactions = $query->paginate(25)->appends(request()->query());
                    @endphp
                    @forelse($transactions as $tr)
                        @php
                            $reservationStatus = $tr->reservationRequest->status ?? null;
                            $isCancelledOrExpired = in_array($reservationStatus,['cancelled','expired']);
                            $total = $tr->original_price ?? $tr->total_amount;
                            if($tr->payment_status==='partial'){ $deposit=$tr->deposit_amount ?? ($total*0.5); }
                            elseif($tr->payment_status==='paid' && in_array($reservationStatus,['completed','terminated'])) { $deposit=$total; }
                            elseif($tr->payment_status==='paid' && $isCancelledOrExpired){ $deposit=$tr->deposit_amount ?? ($total*0.5); if($deposit>=$total){$deposit=$total*0.5;} }
                            elseif($tr->payment_status==='pending'){ $deposit=0; } else { $deposit=$tr->deposit_amount ?? ($total*0.5); }
                            $remaining = max($total-$deposit,0);
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-sm font-mono">#{{ $tr->id }}</td>
                            <td class="px-4 py-2 text-sm">{{ $tr->user->name ?? '—' }}</td>
                            <td class="px-4 py-2 text-sm">{{ $tr->prescription_number }}</td>
                            <td class="px-4 py-2 text-sm font-semibold text-gray-900">{{ number_format($total,0) }} F</td>
                            <td class="px-4 py-2 text-xs text-green-700">{{ number_format($deposit,0) }} F</td>
                            <td class="px-4 py-2 text-xs @if($remaining>0 && !$isCancelledOrExpired) text-orange-600 @else text-red-600 line-through @endif">{{ number_format($remaining,0) }} F</td>
                            <td class="px-4 py-2 text-xs">
                                @if($isCancelledOrExpired && $tr->payment_status==='paid')
                                    <span class="px-2 py-1 rounded-full bg-red-100 text-red-800">Réservation {{ $reservationStatus==='cancelled'?'annulée':'expirée' }}</span>
                                @elseif($tr->payment_status==='partial' || ($tr->payment_status==='paid' && $deposit<$total))
                                    <span class="px-2 py-1 rounded-full bg-yellow-100 text-yellow-800">Acompte</span>
                                @elseif($tr->payment_status==='paid')
                                    <span class="px-2 py-1 rounded-full bg-green-100 text-green-800">Payé</span>
                                @else
                                    <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-800">Pending</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-xs">{{ $reservationStatus ?? '—' }}</td>
                            <td class="px-4 py-2 text-xs">{{ $tr->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-2 text-xs"><a href="{{ route('orders.show',$tr) }}" class="text-blue-600 hover:underline">Voir</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-10 text-center text-gray-500 text-sm">
                                <i class="fas fa-inbox text-2xl mb-2"></i>
                                <p>Aucune transaction trouvée.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $transactions->links() }}</div>
    </div>
</div>
@endsection
