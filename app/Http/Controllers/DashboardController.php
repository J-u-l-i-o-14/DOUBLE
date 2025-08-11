<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\BloodBag;
use App\Models\DonationHistory;
use App\Models\Campaign;
use App\Models\Patient;
use App\Models\Transfusion;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Vérifier si l'utilisateur a accès à un dashboard
        if (!$user->has_dashboard) {
            return redirect()->route('home')->with('error', 'Vous n\'avez pas accès au dashboard.');
        }
        
        switch ($user->role) {
            case 'admin':
                return $this->adminDashboard();
            case 'manager':
                return $this->managerDashboard();
            case 'client':
            case 'patient':
                return $this->clientDashboard();
            default:
                return redirect()->route('login');
        }
    }

    // Dashboard client accessible uniquement pour les clients, donneurs et patients
    public function clientReservationDashboard()
    {
        $user = Auth::user();
        
        // Vérifier que seuls les clients, donneurs et patients peuvent accéder
        if (!in_array($user->role, ['client', 'donor', 'patient'])) {
            return redirect()->route('dashboard')->with('error', 'Accès non autorisé.');
        }
        
        return $this->clientDashboard();
    }

    private function adminDashboard()
    {
        $user = Auth::user();
        
        // Pour les admins, montrer les stats de tous les centres ou du centre spécifique
        $centerFilter = $user->center_id; // Admins peuvent voir leur centre ou tous
        
        // Statistiques générales
        $stats = [
            'total_donors' => User::donors()->when($centerFilter, function($q) use ($centerFilter) { 
                return $q->where('center_id', $centerFilter); 
            })->count(),
            'total_blood_bags' => BloodBag::available()->when($centerFilter, function($q) use ($centerFilter) { 
                return $q->where('center_id', $centerFilter); 
            })->count(),
            'total_donations_this_month' => DonationHistory::thisMonth()->when($centerFilter, function($q) use ($centerFilter) {
                return $q->whereHas('donor', function($query) use ($centerFilter) { 
                    $query->where('center_id', $centerFilter); 
                });
            })->count(),
            'total_transfusions_this_month' => Transfusion::thisMonth()->when($centerFilter, function($q) use ($centerFilter) {
                return $q->whereHas('bloodBag', function($query) use ($centerFilter) { 
                    $query->where('center_id', $centerFilter); 
                });
            })->count(),
            'upcoming_campaigns' => Campaign::upcoming()->when($centerFilter, function($q) use ($centerFilter) { 
                return $q->where('center_id', $centerFilter); 
            })->count(),
            'pending_appointments' => Appointment::pending()->when($centerFilter, function($q) use ($centerFilter) { 
                return $q->where('center_id', $centerFilter); 
            })->count(),
            // Statistiques financières basées sur les vrais mouvements de transaction
            'total_revenue' => \App\Models\ReservationRequest::when($centerFilter, function($q) use ($centerFilter) { 
                return $q->where('center_id', $centerFilter); 
            })
                ->whereHas('order')
                ->with('order')
                ->get()
                ->sum(function($reservation) {
                    if ($reservation->order) {
                        // Compter seulement ce qui est effectivement payé
                        return $reservation->order->total_amount;
                    }
                    return 0;
                }),
            'monthly_revenue' => \App\Models\ReservationRequest::when($centerFilter, function($q) use ($centerFilter) { 
                return $q->where('center_id', $centerFilter); 
            })
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->whereHas('order')
                ->with('order')
                ->get()
                ->sum(function($reservation) {
                    if ($reservation->order) {
                        // Compter seulement ce qui est effectivement payé ce mois
                        return $reservation->order->total_amount;
                    }
                    return 0;
                }),
            'pending_revenue' => \App\Models\ReservationRequest::when($centerFilter, function($q) use ($centerFilter) { 
                return $q->where('center_id', $centerFilter); 
            })
                ->whereIn('status', ['pending', 'confirmed']) // Exclure expired, cancelled, terminated, completed
                ->whereHas('order', function($q) {
                    $q->whereIn('payment_status', ['pending', 'partial']) // Exclure paid et failed
                      ->whereNotIn('status', ['expired', 'cancelled', 'terminated', 'completed']);
                })
                ->with('order')
                ->get()
                ->sum(function($reservation) {
                    if ($reservation->order) {
                        // Calculer le montant restant à payer
                        $remaining = $reservation->order->remaining_amount ?? 
                                    ($reservation->order->total_amount - ($reservation->order->deposit_amount ?? 0));
                        return max(0, $remaining); // Ne pas retourner de valeurs négatives
                    }
                    return 0;
                }),
        ];

        // Alertes critiques
        $alerts = [
            'expired_bags' => BloodBag::expired()->when($centerFilter, function($q) use ($centerFilter) { 
                return $q->where('center_id', $centerFilter); 
            })->count(),
            'expiring_soon_bags' => BloodBag::expiringSoon()->when($centerFilter, function($q) use ($centerFilter) { 
                return $q->where('center_id', $centerFilter); 
            })->count(),
            'low_stock_types' => $this->getLowStockBloodTypes($centerFilter),
            'active_alerts' => \App\Models\Alert::when($centerFilter, function($q) use ($centerFilter) { 
                return $q->where('center_id', $centerFilter); 
            })->where('resolved', false)->latest()->get(),
        ];

        // Dons par mois (6 derniers mois)
        $donationsChart = $this->getDonationsChartData($centerFilter);
        
        // Chiffre d'affaires par mois (6 derniers mois) - Sprint 5
        $revenueChart = $this->getRevenueChartData($centerFilter);

        // Prochaines campagnes
        $upcomingCampaigns = Campaign::upcoming()
            ->when($centerFilter, function($q) use ($centerFilter) { 
                return $q->where('center_id', $centerFilter); 
            })
            ->with('organizer')
            ->orderBy('date')
            ->limit(5)
            ->get();

        // Rendez-vous récents
        $recentAppointments = Appointment::with(['donor', 'campaign'])
            ->when($centerFilter, function($q) use ($centerFilter) { 
                return $q->where('center_id', $centerFilter); 
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Transactions financières récentes - Sprint 5
        $recentTransactions = \App\Models\Order::with(['user'])
            ->when($centerFilter, function($q) use ($centerFilter) { 
                return $q->where('center_id', $centerFilter); 
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.admin', compact(
            'stats', 
            'alerts', 
            'donationsChart',
            'revenueChart',
            'upcomingCampaigns',
            'recentAppointments',
            'recentTransactions'
        ));
    }

    private function managerDashboard()
    {
        $user = Auth::user();
        // Statistiques du manager
        $stats = [
            'total_campaigns' => Campaign::where('center_id', $user->center_id)->count(),
            'upcoming_campaigns' => Campaign::upcoming()->where('center_id', $user->center_id)->count(),
            'pending_appointments' => Appointment::pending()->where('center_id', $user->center_id)->count(),
            'total_donors' => User::donors()->where('center_id', $user->center_id)->count(),
            'total_blood_bags' => BloodBag::available()->where('center_id', $user->center_id)->count(),
            // Statistiques des réservations
            'total_reservations' => \App\Models\ReservationRequest::where('center_id', $user->center_id)->count(),
            'pending_reservations' => \App\Models\ReservationRequest::where('center_id', $user->center_id)
                ->where('status', 'pending')->count(),
            'confirmed_reservations' => \App\Models\ReservationRequest::where('center_id', $user->center_id)
                ->where('status', 'confirmed')->count(),
            // Statistiques financières basées sur les vrais mouvements de transaction
            'total_revenue' => \App\Models\ReservationRequest::where('center_id', $user->center_id)
                ->whereHas('order')
                ->with('order')
                ->get()
                ->sum(function($reservation) {
                    if ($reservation->order) {
                        // Compter seulement ce qui est effectivement payé
                        return $reservation->order->total_amount;
                    }
                    return 0;
                }),
            'monthly_revenue' => \App\Models\ReservationRequest::where('center_id', $user->center_id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->whereHas('order')
                ->with('order')
                ->get()
                ->sum(function($reservation) {
                    if ($reservation->order) {
                        // Compter seulement ce qui est effectivement payé ce mois
                        return $reservation->order->total_amount;
                    }
                    return 0;
                }),
            'pending_revenue' => \App\Models\ReservationRequest::where('center_id', $user->center_id)
                ->whereIn('status', ['pending', 'confirmed']) // Exclure expired, cancelled, terminated, completed
                ->whereHas('order', function($q) {
                    $q->whereIn('payment_status', ['pending', 'partial']) // Exclure paid et failed
                      ->whereNotIn('status', ['expired', 'cancelled', 'terminated', 'completed']);
                })
                ->with('order')
                ->get()
                ->sum(function($reservation) {
                    if ($reservation->order) {
                        // Calculer le montant restant à payer
                        $remaining = $reservation->order->remaining_amount ?? 
                                    ($reservation->order->total_amount - ($reservation->order->deposit_amount ?? 0));
                        return max(0, $remaining); // Ne pas retourner de valeurs négatives
                    }
                    return 0;
                }),
        ];

        // Documents de réservation à valider (pour la cloche)
        $pendingReservationDocuments = \App\Models\Document::whereHas('reservationRequest', function($q) use ($user) {
            $q->where('center_id', $user->center_id);
        })->where('verified', false)->get();

        // Alertes
        $alerts = [
            'expired_bags' => BloodBag::expired()->where('center_id', $user->center_id)->count(),
            'expiring_soon_bags' => BloodBag::expiringSoon()->where('center_id', $user->center_id)->count(),
            'low_stock_types' => $this->getLowStockBloodTypes($user->center_id),
            'active_alerts' => \App\Models\Alert::where('center_id', $user->center_id)->where('resolved', false)->latest()->get(),
        ];

        // Prochaines campagnes
        $upcomingCampaigns = Campaign::upcoming()
            ->where('center_id', $user->center_id)
            ->with('organizer')
            ->orderBy('date')
            ->limit(5)
            ->get();

        // Rendez-vous récents
        $recentAppointments = Appointment::with(['donor', 'campaign'])
            ->where('center_id', $user->center_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Chiffre d'affaires par mois - Sprint 5
        $revenueChart = $this->getRevenueChartData($user->center_id);

        // Transactions financières récentes - Sprint 5
        $recentTransactions = \App\Models\Order::with(['user'])
            ->where('center_id', $user->center_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Réservations récentes du centre
        $recentReservations = \App\Models\ReservationRequest::with(['user', 'items.bloodType'])
            ->where('center_id', $user->center_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.manager', compact(
            'stats',
            'alerts',
            'upcomingCampaigns',
            'recentAppointments',
            'pendingReservationDocuments',
            'revenueChart',
            'recentTransactions',
            'recentReservations'
        ));
    }

    private function clientDashboard()
    {
        // Statistiques du client
        $stats = [
            'available_blood_bags' => BloodBag::available()->count(),
            'upcoming_campaigns' => Campaign::upcoming()->count(),
            'total_donors' => User::donors()->count(),
        ];

        // Stock par groupe sanguin
        $stockByBloodType = BloodBag::available()
            ->join('blood_types', 'blood_bags.blood_type_id', '=', 'blood_types.id')
            ->selectRaw('blood_types.`group` as blood_group, COUNT(*) as count')
            ->groupBy('blood_group')
            ->pluck('count', 'blood_group')
            ->toArray();

        // Prochaines campagnes
        $upcomingCampaigns = Campaign::upcoming()
            ->with('organizer')
            ->orderBy('date')
            ->limit(5)
            ->get();

        // Poches de sang disponibles
        $availableBloodBags = BloodBag::available()
            ->with('donor')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.client', compact(
            'stats',
            'stockByBloodType',
            'upcomingCampaigns',
            'availableBloodBags'
        ));
    }

    private function getLowStockBloodTypes($centerId, $threshold = 5)
    {
        return BloodBag::available()
                ->where('center_id', $centerId)
            ->join('blood_types', 'blood_bags.blood_type_id', '=', 'blood_types.id')
            ->selectRaw('blood_types.`group` as blood_group, COUNT(*) as count')
            ->groupBy('blood_group')
            ->havingRaw('COUNT(*) < ?', [$threshold])
            ->pluck('count', 'blood_group')
            ->toArray();
    }

    private function getDonationsChartData($centerId = null)
    {
        $months = [];
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');
            $query = DonationHistory::thisMonth();
            if ($centerId) {
                $query = $query->whereHas('donor', function($q) use ($centerId) { $q->where('center_id', $centerId); });
            }
            $count = $query
                ->whereMonth('donated_at', $date->month)
                ->whereYear('donated_at', $date->year)
                ->count();
            $data[] = $count;
        }
        return [
            'labels' => $months,
            'data' => $data
        ];
    }

    // Nouvelle méthode pour les données de revenus - Sprint 5
    private function getRevenueChartData($centerId = null)
    {
        $user = Auth::user();
        $centerFilter = $centerId ?? $user->center_id;
        
        $months = [];
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            $revenue = \App\Models\Order::when($centerFilter, function($q) use ($centerFilter) { 
                return $q->where('center_id', $centerFilter); 
            })
                ->where('payment_status', '!=', 'failed')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum(\DB::raw('CASE 
                    WHEN payment_status = "partial" THEN COALESCE(deposit_amount, total_amount * 0.5, 0)
                    WHEN payment_status = "paid" THEN COALESCE(total_amount, 0)
                    ELSE 0 
                END'));
            $data[] = $revenue;
        }
        return [
            'labels' => $months,
            'data' => $data
        ];
    }

    private function getDonorNotifications($donor)
    {
        $notifications = [];
        
        // Notification si peut donner
        if ($donor->can_donate) {
            $notifications[] = [
                'type' => 'success',
                'message' => 'Vous pouvez faire un don de sang ! Prenez rendez-vous dès maintenant.',
                'action' => route('appointments.create')
            ];
        } else {
            $days = $donor->next_donation_date->diffInDays(now());
            $notifications[] = [
                'type' => 'info',
                'message' => "Prochain don possible dans {$days} jours (" . (optional($donor->next_donation_date)->format('d/m/Y')) . ")."
            ];
        }
        
        // Notification pour rendez-vous à venir
        $nextAppointment = $donor->appointments()->upcoming()->first();
        if ($nextAppointment) {
            $notifications[] = [
                'type' => 'warning',
                'message' => "Rendez-vous prévu le {$nextAppointment->formatted_date}.",
                'action' => route('appointments.show', $nextAppointment)
            ];
        }
        
        return $notifications;
    }
}