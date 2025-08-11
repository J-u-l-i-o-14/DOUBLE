<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST ALERTCONTROLLER ===\n\n";

try {
    // Test 1: Vérifier la classe AlertController
    echo "🧪 TEST 1 - Vérification classe AlertController:\n";
    echo "===============================================\n";
    
    $controllerClass = \App\Http\Controllers\AlertController::class;
    if (class_exists($controllerClass)) {
        echo "✅ Classe AlertController existe\n";
        
        $reflection = new ReflectionClass($controllerClass);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        
        echo "✅ Méthodes publiques disponibles:\n";
        foreach ($methods as $method) {
            if (!$method->isConstructor() && $method->getDeclaringClass()->getName() === $controllerClass) {
                echo "   - {$method->getName()}\n";
            }
        }
        
        // Vérifier le constructeur
        $constructor = $reflection->getConstructor();
        if ($constructor) {
            echo "✅ Constructeur présent\n";
        }
        
    } else {
        echo "❌ Classe AlertController introuvable\n";
    }
    echo "\n";

    // Test 2: Vérifier les routes
    echo "🧪 TEST 2 - Vérification routes alertes:\n";
    echo "========================================\n";
    
    $routes = [
        'alerts.index',
        'alerts.resolve',
        'alerts.resolveAll',
        'alerts.destroy',
        'api.alerts.active'
    ];
    
    foreach ($routes as $routeName) {
        try {
            $route = route($routeName, $routeName === 'alerts.resolve' || $routeName === 'alerts.destroy' ? 1 : []);
            echo "✅ Route {$routeName}: OK\n";
        } catch (Exception $e) {
            echo "❌ Route {$routeName}: ERREUR - " . $e->getMessage() . "\n";
        }
    }
    echo "\n";

    // Test 3: Vérifier le middleware
    echo "🧪 TEST 3 - Vérification middleware:\n";
    echo "====================================\n";
    
    if (class_exists(\App\Http\Middleware\RoleMiddleware::class)) {
        echo "✅ RoleMiddleware existe\n";
        
        // Test création instance
        $roleMiddleware = new \App\Http\Middleware\RoleMiddleware();
        echo "✅ RoleMiddleware peut être instancié\n";
        
    } else {
        echo "❌ RoleMiddleware introuvable\n";
    }
    echo "\n";

} catch (Exception $e) {
    echo "❌ Erreur générale: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "=== DIAGNOSTIC ===\n";
echo "Si l'erreur persiste, elle pourrait venir de:\n";
echo "1. Cache de routes (php artisan route:clear)\n";
echo "2. Cache de config (php artisan config:clear)\n";
echo "3. Autoload (composer dump-autoload)\n";
echo "4. Utilisation incorrecte dans une vue ou route\n";
