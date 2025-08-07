<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;
use App\Models\Center;
use App\Models\Notification;
use Carbon\Carbon;

class Sprint4Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "=== Seeding Sprint 4 - Commandes et Notifications ===\n";
        
        // Récupérer quelques utilisateurs et centres
        $users = User::where('role', 'client')->take(3)->get();
        $centers = Center::take(2)->get();
        $managers = User::whereIn('role', ['manager', 'admin'])->get();
        
        if ($users->isEmpty() || $centers->isEmpty()) {
            echo "⚠️ Pas assez d'utilisateurs ou de centres pour créer des commandes de test\n";
            return;
        }
        
        echo "Création de commandes de test...\n";
        
        // Créer des commandes avec différents statuts pour tester le Sprint 4
        $ordersData = [
            [
                'user_id' => $users[0]->id,
                'center_id' => $centers[0]->id,
                'prescription_number' => 'ORD-2024-001',
                'phone_number' => '70123456',
                'prescription_images' => json_encode(['prescription1.jpg', 'prescription2.jpg']),
                'blood_type' => 'A+',
                'quantity' => 2,
                'unit_price' => 5000,
                'total_amount' => 10000,
                'original_price' => 10000,
                'deposit_amount' => 5000,
                'remaining_amount' => 5000,
                'payment_method' => 'tmoney',
                'payment_status' => 'partial',
                'status' => 'pending',
                'document_status' => 'pending',
                'notes' => 'Commande urgente - Patient en réanimation',
                'order_date' => now(),
            ],
            [
                'user_id' => $users[1]->id,
                'center_id' => $centers[0]->id,
                'prescription_number' => 'ORD-2024-002',
                'phone_number' => '70987654',
                'prescription_images' => json_encode(['prescription3.jpg']),
                'blood_type' => 'O-',
                'quantity' => 1,
                'unit_price' => 5000,
                'total_amount' => 5000,
                'original_price' => 5000,
                'deposit_amount' => 2500,
                'remaining_amount' => 2500,
                'payment_method' => 'flooz',
                'payment_status' => 'partial',
                'status' => 'confirmed',
                'document_status' => 'approved',
                'validated_by' => $managers->first()->id ?? null,
                'validated_at' => now()->subHours(2),
                'validation_notes' => 'Documents vérifiés et approuvés',
                'notes' => 'Intervention chirurgicale programmée',
                'order_date' => now()->subHours(3),
            ],
            [
                'user_id' => $users[2]->id,
                'center_id' => $centers[1]->id,
                'prescription_number' => 'ORD-2024-003',
                'phone_number' => '70555888',
                'prescription_images' => json_encode(['prescription4.jpg', 'prescription5.jpg']),
                'blood_type' => 'B+',
                'quantity' => 3,
                'unit_price' => 5000,
                'total_amount' => 15000,
                'original_price' => 15000,
                'deposit_amount' => 7500,
                'remaining_amount' => 7500,
                'payment_method' => 'carte_bancaire',
                'payment_status' => 'partial',
                'status' => 'ready',
                'document_status' => 'approved',
                'validated_by' => $managers->first()->id ?? null,
                'validated_at' => now()->subDays(1),
                'validation_notes' => 'Tout est en ordre, commande prête',
                'notes' => 'Transfusion prévue demain matin',
                'order_date' => now()->subDays(2),
            ],
            // Commande avec même numéro d'ordonnance (test Sprint 4)
            [
                'user_id' => $users[0]->id,
                'center_id' => $centers[1]->id,
                'prescription_number' => 'ORD-2024-001', // Même ordonnance que la première
                'phone_number' => '70123456',
                'prescription_images' => json_encode(['prescription1.jpg', 'prescription2.jpg']),
                'blood_type' => 'AB+',
                'quantity' => 1,
                'unit_price' => 5000,
                'total_amount' => 5000,
                'original_price' => 5000,
                'deposit_amount' => 2500,
                'remaining_amount' => 2500,
                'payment_method' => 'tmoney',
                'payment_status' => 'partial',
                'status' => 'pending',
                'document_status' => 'pending',
                'notes' => 'Commande supplémentaire sur même ordonnance',
                'order_date' => now()->subMinutes(30),
            ]
        ];
        
        $createdOrders = [];
        foreach ($ordersData as $orderData) {
            $order = Order::create($orderData);
            $createdOrders[] = $order;
            echo "- Commande créée : ID {$order->id}, {$order->prescription_number}, {$order->status}\n";
        }
        
        echo "\nCréation de notifications pour les gestionnaires...\n";
        
        // Créer des notifications pour chaque commande
        foreach ($createdOrders as $order) {
            // Notification pour les gestionnaires du centre
            $centerManagers = User::where('center_id', $order->center_id)
                ->whereIn('role', ['manager', 'admin'])
                ->get();
                
            foreach ($centerManagers as $manager) {
                Notification::create([
                    'user_id' => $manager->id,
                    'type' => 'new_order',
                    'title' => 'Nouvelle commande de sang',
                    'message' => "Nouvelle commande de {$order->quantity} poche(s) de {$order->blood_type} - Ordonnance: {$order->prescription_number}",
                    'data' => json_encode([
                        'order_id' => $order->id,
                        'prescription_number' => $order->prescription_number,
                        'blood_type' => $order->blood_type,
                        'quantity' => $order->quantity,
                        'document_status' => $order->document_status
                    ]),
                    'read_at' => $order->status === 'confirmed' ? now()->subHours(1) : null,
                    'created_at' => $order->created_at,
                ]);
            }
        }
        
        // Notifications supplémentaires pour tester différents types
        if ($managers->isNotEmpty()) {
            $manager = $managers->first();
            
            // Notification de validation nécessaire
            Notification::create([
                'user_id' => $manager->id,
                'type' => 'document_validation_required',
                'title' => 'Validation de documents requise',
                'message' => 'Des documents d\'ordonnance sont en attente de validation',
                'data' => json_encode([
                    'pending_orders' => Order::where('document_status', 'pending')->count()
                ]),
                'read_at' => null,
                'created_at' => now()->subMinutes(15),
            ]);
            
            // Notification de stock faible
            Notification::create([
                'user_id' => $manager->id,
                'type' => 'low_stock',
                'title' => 'Stock faible',
                'message' => 'Le stock de sang O- est critique dans votre centre',
                'data' => json_encode([
                    'blood_type' => 'O-',
                    'current_stock' => 2,
                    'threshold' => 5
                ]),
                'read_at' => null,
                'created_at' => now()->subMinutes(45),
            ]);
        }
        
        echo "\n✅ Sprint 4 Seeder terminé !\n";
        echo "Commandes créées : " . count($createdOrders) . "\n";
        echo "Notifications créées : " . Notification::count() . "\n";
    }
}
