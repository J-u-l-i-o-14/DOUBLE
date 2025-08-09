<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Campaign;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentConfirmation;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AppointmentTaken;

class AppointmentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->is_donor) {
            $appointments = $user->appointments()->with('campaign')->latest()->paginate(10);
        } else if (in_array($user->role, ['admin', 'manager'])) {
            $appointments = Appointment::with(['donor', 'campaign'])
                ->where('center_id', $user->center_id)
                ->latest()->paginate(10);
        } else {
            $appointments = Appointment::with(['donor', 'campaign'])->latest()->paginate(10);
        }

        return view('appointments.index', compact('appointments'));
    }

    public function create()
    {
        $campaigns = Campaign::upcoming()->get();
        return view('appointments.create', compact('campaigns'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'appointment_date' => 'required|date|after:tomorrow',
            'appointment_time' => 'required|string',
            'type' => 'required|in:centre,campagne',
            'campaign_id' => 'required_if:type,campagne|exists:campaigns,id',
            'notes' => 'nullable|string|max:500',
        ]);
        $centerId = $user->center_id;
        
        // Combiner date et heure
        $appointmentDateTime = $request->appointment_date . ' ' . $request->appointment_time;
        
        $appointment = Appointment::create([
            'donor_id' => $user->id,
            'center_id' => $centerId,
            'appointment_date' => $appointmentDateTime,
            'type' => $request->type,
            'campaign_id' => $request->type === 'campagne' ? $request->campaign_id : null,
            'notes' => $request->notes,
            'status' => 'planifie',
        ]);

        // Envoyer email de confirmation
        try {
            Mail::to(auth()->user()->email)->send(new AppointmentConfirmation($appointment));
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas faire échouer la création
            \Log::error('Erreur envoi email: ' . $e->getMessage());
        }

        return redirect()->route('appointments.index')
            ->with('success', 'Rendez-vous créé avec succès. Un email de confirmation vous a été envoyé.');
    }

    public function show(Appointment $appointment)
    {
        $this->authorize('view', $appointment);
        return view('appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        $this->authorize('update', $appointment);
        $campaigns = Campaign::upcoming()->get();
        return view('appointments.edit', compact('appointment', 'campaigns'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);
        $user = auth()->user();
        $request->validate([
            'appointment_date' => 'required|date|after:now',
            'type' => 'required|in:centre,campagne',
            'campaign_id' => 'required_if:type,campagne|exists:campaigns,id',
            'notes' => 'nullable|string|max:500',
            'status' => 'sometimes|in:planifie,confirme,complete,annule',
        ]);
        $centerId = $user->center_id;
        $appointment->update([
            'appointment_date' => $request->appointment_date,
            'type' => $request->type,
            'campaign_id' => $request->type === 'campagne' ? $request->campaign_id : null,
            'notes' => $request->notes,
            'status' => $request->status ?? $appointment->status,
            'center_id' => $centerId,
        ]);

        return redirect()->route('appointments.index')
            ->with('success', 'Rendez-vous mis à jour avec succès.');
    }

    public function destroy(Appointment $appointment)
    {
        $this->authorize('delete', $appointment);
        
        $appointment->update(['status' => 'annule']);

        return redirect()->route('appointments.index')
            ->with('success', 'Rendez-vous annulé avec succès.');
    }

    public function confirm(Appointment $appointment)
    {
        $this->authorize('confirm', $appointment);

        $appointment->update([
            'status' => 'confirme',
            'confirmed_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Rendez-vous confirmé avec succès.');
    }

    // Formulaire public de prise de rendez-vous
    public function publicForm()
    {
        $campaigns = Campaign::all();
        return view('appointments.public', compact('campaigns'));
    }

    // Traitement du formulaire public
    public function publicStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'campaign_id' => 'required|exists:campaigns,id',
        ]);

        // Création ou récupération de l'utilisateur
        $user = User::firstOrCreate(
            ['email' => $validated['email']],
            ['name' => $validated['name'], 'password' => bcrypt(Str::random(12))]
        );

        // Création du rendez-vous
        $appointment = Appointment::create([
            'donor_id' => $user->id,
            'appointment_date' => now()->addDays(1), // À adapter selon le formulaire
            'type' => 'campagne',
            'campaign_id' => $validated['campaign_id'],
            'status' => 'planifie',
        ]);

        // Notification des admins/managers
        Notification::send(User::role(['admin', 'manager'])->get(), new AppointmentTaken($appointment));

        return redirect()->route('login')->with('success', 'Votre rendez-vous est pris. Un compte a été créé, veuillez vérifier votre email.');
    }
}