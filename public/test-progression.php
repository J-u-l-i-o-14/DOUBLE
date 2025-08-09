<!DOCTYPE html>
<html>
<head>
    <title>Test Progression Commande</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Test de Progression de Commande</h1>
        
        <!-- Test avec différents statuts -->
        <div class="space-y-6">
            
            <!-- Statut: pending -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold mb-4">Test 1: Réservation PENDING</h2>
                <?php
                $order = (object)[
                    'id' => 1,
                    'status' => 'pending',
                    'reservationRequest' => (object)[
                        'id' => 1,
                        'status' => 'pending',
                        'updated_at' => new DateTime(),
                        'created_at' => new DateTime()
                    ]
                ];
                ?>
                
                <!-- Progression de la commande -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">Progression de la commande</h3>
                    <?php
                        $reservationStatus = $order->reservationRequest ? $order->reservationRequest->status : 'pending';
                        $isReservationCancelled = $reservationStatus === 'cancelled';
                        $isReservationConfirmed = in_array($reservationStatus, ['confirmed', 'completed']);
                        $isReservationCompleted = $reservationStatus === 'completed';
                    ?>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center text-sm">
                            <div class="flex items-center <?= !$isReservationCancelled ? 'text-green-600' : 'text-gray-400' ?>">
                                <i class="fas fa-check-circle mr-2"></i>
                                <span>Commandée</span>
                            </div>
                        </div>
                        <div class="flex-1 mx-4">
                            <div class="h-2 bg-gray-200 rounded-full">
                                <div class="h-2 rounded-full 
                                    <?php if($reservationStatus === 'pending'): ?>bg-yellow-500 w-1/4
                                    <?php elseif($reservationStatus === 'confirmed'): ?>bg-blue-500 w-3/4
                                    <?php elseif($reservationStatus === 'completed'): ?>bg-green-600 w-full
                                    <?php elseif($reservationStatus === 'cancelled'): ?>bg-red-500 w-1/4
                                    <?php else: ?>bg-gray-400 w-1/4
                                    <?php endif; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center text-sm">
                            <div class="flex items-center <?= $isReservationCompleted ? 'text-green-600' : 'text-gray-400' ?>">
                                <i class="fas fa-handshake mr-2"></i>
                                <span>Récupérée</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 text-xs text-gray-500 text-center">
                        <?php if($reservationStatus === 'pending'): ?>
                            <span class="text-yellow-600">⏳ En attente de validation par le centre</span>
                        <?php elseif($reservationStatus === 'confirmed'): ?>
                            <span class="text-blue-600">✅ Confirmée - Prête pour récupération</span>
                        <?php elseif($reservationStatus === 'completed'): ?>
                            <span class="text-green-600">🎉 Commande terminée - Sang récupéré</span>
                        <?php elseif($reservationStatus === 'cancelled'): ?>
                            <span class="text-red-600">❌ Réservation annulée</span>
                        <?php else: ?>
                            <span class="text-gray-600">📋 Statut: <?= $reservationStatus ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if($order->reservationRequest): ?>
                    <div class="mt-3 text-xs text-gray-600 flex items-center justify-between">
                        <span>Réservation #<?= $order->reservationRequest->id ?></span>
                        <?php if($order->reservationRequest->updated_at != $order->reservationRequest->created_at): ?>
                            <span>Dernière mise à jour: <?= $order->reservationRequest->updated_at->format('d/m/Y à H:i') ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Statut: confirmed -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold mb-4">Test 2: Réservation CONFIRMED</h2>
                <?php
                $order2 = (object)[
                    'id' => 2,
                    'status' => 'pending',
                    'reservationRequest' => (object)[
                        'id' => 2,
                        'status' => 'confirmed',
                        'updated_at' => new DateTime(),
                        'created_at' => new DateTime('-1 hour')
                    ]
                ];
                ?>
                
                <!-- Progression de la commande -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">Progression de la commande</h3>
                    <?php
                        $reservationStatus = $order2->reservationRequest ? $order2->reservationRequest->status : 'pending';
                        $isReservationCancelled = $reservationStatus === 'cancelled';
                        $isReservationConfirmed = in_array($reservationStatus, ['confirmed', 'completed']);
                        $isReservationCompleted = $reservationStatus === 'completed';
                    ?>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center text-sm">
                            <div class="flex items-center <?= !$isReservationCancelled ? 'text-green-600' : 'text-gray-400' ?>">
                                <i class="fas fa-check-circle mr-2"></i>
                                <span>Commandée</span>
                            </div>
                        </div>
                        <div class="flex-1 mx-4">
                            <div class="h-2 bg-gray-200 rounded-full">
                                <div class="h-2 rounded-full 
                                    <?php if($reservationStatus === 'pending'): ?>bg-yellow-500 w-1/4
                                    <?php elseif($reservationStatus === 'confirmed'): ?>bg-blue-500 w-3/4
                                    <?php elseif($reservationStatus === 'completed'): ?>bg-green-600 w-full
                                    <?php elseif($reservationStatus === 'cancelled'): ?>bg-red-500 w-1/4
                                    <?php else: ?>bg-gray-400 w-1/4
                                    <?php endif; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center text-sm">
                            <div class="flex items-center <?= $isReservationCompleted ? 'text-green-600' : 'text-gray-400' ?>">
                                <i class="fas fa-handshake mr-2"></i>
                                <span>Récupérée</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 text-xs text-gray-500 text-center">
                        <?php if($reservationStatus === 'pending'): ?>
                            <span class="text-yellow-600">⏳ En attente de validation par le centre</span>
                        <?php elseif($reservationStatus === 'confirmed'): ?>
                            <span class="text-blue-600">✅ Confirmée - Prête pour récupération</span>
                        <?php elseif($reservationStatus === 'completed'): ?>
                            <span class="text-green-600">🎉 Commande terminée - Sang récupéré</span>
                        <?php elseif($reservationStatus === 'cancelled'): ?>
                            <span class="text-red-600">❌ Réservation annulée</span>
                        <?php else: ?>
                            <span class="text-gray-600">📋 Statut: <?= $reservationStatus ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if($order2->reservationRequest): ?>
                    <div class="mt-3 text-xs text-gray-600 flex items-center justify-between">
                        <span>Réservation #<?= $order2->reservationRequest->id ?></span>
                        <?php if($order2->reservationRequest->updated_at != $order2->reservationRequest->created_at): ?>
                            <span>Dernière mise à jour: <?= $order2->reservationRequest->updated_at->format('d/m/Y à H:i') ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Statut: completed -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold mb-4">Test 3: Réservation COMPLETED</h2>
                <?php
                $order3 = (object)[
                    'id' => 3,
                    'status' => 'pending',
                    'reservationRequest' => (object)[
                        'id' => 3,
                        'status' => 'completed',
                        'updated_at' => new DateTime(),
                        'created_at' => new DateTime('-2 hours')
                    ]
                ];
                ?>
                
                <!-- Progression de la commande -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">Progression de la commande</h3>
                    <?php
                        $reservationStatus = $order3->reservationRequest ? $order3->reservationRequest->status : 'pending';
                        $isReservationCancelled = $reservationStatus === 'cancelled';
                        $isReservationConfirmed = in_array($reservationStatus, ['confirmed', 'completed']);
                        $isReservationCompleted = $reservationStatus === 'completed';
                    ?>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center text-sm">
                            <div class="flex items-center <?= !$isReservationCancelled ? 'text-green-600' : 'text-gray-400' ?>">
                                <i class="fas fa-check-circle mr-2"></i>
                                <span>Commandée</span>
                            </div>
                        </div>
                        <div class="flex-1 mx-4">
                            <div class="h-2 bg-gray-200 rounded-full">
                                <div class="h-2 rounded-full 
                                    <?php if($reservationStatus === 'pending'): ?>bg-yellow-500 w-1/4
                                    <?php elseif($reservationStatus === 'confirmed'): ?>bg-blue-500 w-3/4
                                    <?php elseif($reservationStatus === 'completed'): ?>bg-green-600 w-full
                                    <?php elseif($reservationStatus === 'cancelled'): ?>bg-red-500 w-1/4
                                    <?php else: ?>bg-gray-400 w-1/4
                                    <?php endif; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center text-sm">
                            <div class="flex items-center <?= $isReservationCompleted ? 'text-green-600' : 'text-gray-400' ?>">
                                <i class="fas fa-handshake mr-2"></i>
                                <span>Récupérée</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 text-xs text-gray-500 text-center">
                        <?php if($reservationStatus === 'pending'): ?>
                            <span class="text-yellow-600">⏳ En attente de validation par le centre</span>
                        <?php elseif($reservationStatus === 'confirmed'): ?>
                            <span class="text-blue-600">✅ Confirmée - Prête pour récupération</span>
                        <?php elseif($reservationStatus === 'completed'): ?>
                            <span class="text-green-600">🎉 Commande terminée - Sang récupéré</span>
                        <?php elseif($reservationStatus === 'cancelled'): ?>
                            <span class="text-red-600">❌ Réservation annulée</span>
                        <?php else: ?>
                            <span class="text-gray-600">📋 Statut: <?= $reservationStatus ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if($order3->reservationRequest): ?>
                    <div class="mt-3 text-xs text-gray-600 flex items-center justify-between">
                        <span>Réservation #<?= $order3->reservationRequest->id ?></span>
                        <?php if($order3->reservationRequest->updated_at != $order3->reservationRequest->created_at): ?>
                            <span>Dernière mise à jour: <?= $order3->reservationRequest->updated_at->format('d/m/Y à H:i') ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="mt-8 p-4 bg-green-50 border border-green-200 rounded-lg">
            <h3 class="font-semibold text-green-800">✅ Tests de Progression</h3>
            <p class="text-green-700 mt-2">
                1. **PENDING** (25%) : Barre jaune + message "En attente de validation"<br>
                2. **CONFIRMED** (75%) : Barre bleue + message "Confirmée - Prête pour récupération"<br>
                3. **COMPLETED** (100%) : Barre verte + message "Commande terminée - Sang récupéré"
            </p>
            <p class="text-green-600 mt-2 text-sm">
                La progression reflète maintenant le <strong>statut de la réservation</strong> et non le statut de la commande.
            </p>
        </div>
    </div>
</body>
</html>
