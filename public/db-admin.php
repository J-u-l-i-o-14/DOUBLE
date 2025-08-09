<!DOCTYPE html>
<html>
<head>
    <title>Administration SQLite - Blood Bank</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .query-form { background: #f9f9f9; padding: 20px; margin: 20px 0; border-radius: 5px; }
        textarea { width: 100%; height: 100px; }
        .btn { padding: 10px 20px; background: #007cba; color: white; border: none; cursor: pointer; }
        .btn:hover { background: #005a8b; }
    </style>
</head>
<body>
    <h1>🩸 Administration Base de Données - Blood Bank</h1>
    
    <?php
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    if (isset($_POST['query'])) {
        $query = $_POST['query'];
        echo "<h2>Résultat de la requête :</h2>";
        try {
            if (stripos($query, 'SELECT') === 0) {
                $results = \DB::select($query);
                if ($results) {
                    echo "<table>";
                    $first = (array)$results[0];
                    echo "<tr>";
                    foreach (array_keys($first) as $column) {
                        echo "<th>$column</th>";
                    }
                    echo "</tr>";
                    foreach ($results as $row) {
                        echo "<tr>";
                        foreach ((array)$row as $value) {
                            echo "<td>$value</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>Aucun résultat</p>";
                }
            } else {
                $result = \DB::statement($query);
                echo "<p>Requête exécutée avec succès</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red'>Erreur: " . $e->getMessage() . "</p>";
        }
    }
    ?>
    
    <div class="query-form">
        <h2>Exécuter une requête SQL</h2>
        <form method="POST">
            <textarea name="query" placeholder="Entrez votre requête SQL ici..."><?= $_POST['query'] ?? 'SELECT * FROM users LIMIT 10;' ?></textarea>
            <br><br>
            <button type="submit" class="btn">Exécuter</button>
        </form>
    </div>
    
    <h2>Requêtes prédéfinies :</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 10px;">
        <form method="POST">
            <input type="hidden" name="query" value="SELECT * FROM users LIMIT 10;">
            <button type="submit" class="btn">Voir les utilisateurs</button>
        </form>
        
        <form method="POST">
            <input type="hidden" name="query" value="SELECT * FROM orders;">
            <button type="submit" class="btn">Voir les commandes</button>
        </form>
        
        <form method="POST">
            <input type="hidden" name="query" value="SELECT * FROM reservation_requests;">
            <button type="submit" class="btn">Voir les réservations</button>
        </form>
        
        <form method="POST">
            <input type="hidden" name="query" value="SELECT name FROM sqlite_master WHERE type='table';">
            <button type="submit" class="btn">Lister les tables</button>
        </form>
        
        <form method="POST">
            <input type="hidden" name="query" value="SELECT table_name, column_name, data_type FROM information_schema.columns;">
            <button type="submit" class="btn">Structure des tables</button>
        </form>
    </div>
    
    <h2>Statistiques rapides :</h2>
    <?php
    echo "<ul>";
    $tables = \DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
    foreach ($tables as $table) {
        $count = \DB::table($table->name)->count();
        echo "<li><strong>{$table->name}</strong> : $count lignes</li>";
    }
    echo "</ul>";
    ?>
</body>
</html>
