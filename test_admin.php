<?php
/**
 * Test Admin Functionality
 * Vérifie que l'admin peut se connecter et accéder au dashboard
 */

require_once 'config/config.php';
require_once 'includes/Admin.php';

echo "<h1>Test Admin Dashboard</h1>";

// Test 1: Vérifier la classe Admin
echo "<h2>Test 1: Classe Admin</h2>";
try {
    $admin = new Admin();
    echo "✅ Classe Admin chargée avec succès<br>";
} catch (Exception $e) {
    echo "❌ Erreur classe Admin: " . $e->getMessage() . "<br>";
}

// Test 2: Vérifier la connexion admin
echo "<h2>Test 2: Connexion Admin</h2>";
try {
    $admin = new Admin();
    $loginResult = $admin->login('admin@smmplatform.com', 'password');
    
    if ($loginResult) {
        echo "✅ Connexion admin réussie !<br>";
        echo "Admin ID: " . $_SESSION['admin_id'] . "<br>";
        echo "Admin Role: " . $_SESSION['admin_role'] . "<br>";
        
        // Test 3: Vérifier l'accès au dashboard
        echo "<h2>Test 3: Accès Dashboard Admin</h2>";
        if ($admin->isLoggedIn()) {
            echo "✅ Admin connecté et peut accéder au dashboard<br>";
            echo "<a href='admin/index.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Accéder au Dashboard Admin</a><br>";
        } else {
            echo "❌ Admin non connecté<br>";
        }
        
    } else {
        echo "❌ Échec de la connexion admin<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur connexion admin: " . $e->getMessage() . "<br>";
}

// Test 4: Vérifier les sessions
echo "<h2>Test 4: Sessions Admin</h2>";
if (isset($_SESSION['admin_id'])) {
    echo "✅ Session admin active<br>";
    echo "Admin ID: " . $_SESSION['admin_id'] . "<br>";
    echo "Admin Role: " . $_SESSION['admin_role'] . "<br>";
} else {
    echo "❌ Pas de session admin<br>";
}

echo "<hr>";
echo "<p>Test terminé. Vérifiez les résultats ci-dessus.</p>";
echo "<p><a href='admin/login.php'>Page de connexion admin</a></p>";
?>