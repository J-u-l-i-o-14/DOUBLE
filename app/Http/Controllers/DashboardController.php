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
                return $this->clientDashboard();
            default:
                return redirect()->route('login');
        }
    }

    private function adminDashboard()
    {
        $user = Auth::user();
        // Statistiques générales
        $stats = [
            'total_donors' => User::donors()->where('center_id', $user->center_id)->count(),
            'total_blood_bags' => BloodBag::available()->where('center_id', $user->center_id)->count(),
            'total_donations_this_month' => DonationHistory::thisMonth()->whereHas('donor', function($q) use ($user) { $q->where('center_id', $user->center_id); })->count(),
            'total_transfusions_this_month' => Transfusion::thisMonth()->whereHas('bloodBag', function($q) use ($user) { $q->where('center_id', $user->center_id); })->count(),
            'upcoming_campaigns' => Campaign::upcoming()->where('center_id', $user->center_id)->count(),
            'pending_appointments' => Appointment::pending()->where('center_id', $user->center_id)->count(),
        ];

        // Alertes critiques
        $alerts = [
            'expired_bags' => BloodBag::expired()->where('center_id', $user->center_id)->count(),
            'expiring_soon_bags' => BloodBag::expiringSoon()->where('center_id', $user->center_id)->count(),
            'low_stock_types' => $this->getLowStockBloodTypes($user->center_id),
            'active_alerts' => \App\Models\Alert::where('center_id', $user->center_id)->where('resolved', false)->latest()->get(),
        ];

        // Stock par groupe sanguin
        $stockByBloodType = BloodBag::available()
            ->where('center_id', $user->center_id)
            ->join('blood_types', 'blood_bags.blood_type_id', '=', 'blood_types.id')
            ->selectRaw('blood_types.group, COUNT(*) as count')
            ->groupBy('blood_types.group')
            ->pluck('count', 'blood_types.group')
            ->toArray();

        // Dons par mois (6 derniers mois)
        $donationsChart = $this->getDonationsChartData($user->center_id);

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

        return view('dashboard.admin', compact(
            'stats', 
            'alerts', 
            'stockByBloodType', 
            'donationsChart', 
            'upcomingCampaigns',
            'recentAppointments'
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

        // Stock par groupe sanguin
        $stockByBloodType = BloodBag::available()
            ->where('center_id', $user->center_id)
            ->join('blood_types', 'blood_bags.blood_type_id', '=', 'blood_types.id')
            ->selectRaw('blood_types.group, COUNT(*) as count')
            ->groupBy('blood_types.group')
            ->pluck('count', 'blood_types.group')
            ->toArray();

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

        return view('dashboard.manager', compact(
            'stats',
            'alerts',
            'stockByBloodType',
            'upcomingCampaigns',
            'recentAppointments',
            'pendingReservationDocuments'
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
            ->selectRaw('blood_types.group, COUNT(*) as count')
            ->groupBy('blood_types.group')
            ->pluck('count', 'blood_types.group')
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
            ->selectRaw('blood_types.group, COUNT(*) as count')
            ->groupBy('blood_types.group')
            ->havingRaw('COUNT(*) < ?', [$threshold])
            ->pluck('count', 'blood_types.group')
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