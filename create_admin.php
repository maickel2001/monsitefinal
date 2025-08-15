<?php
/**
 * Script de création d'un compte admin
 * Exécutez ce fichier une seule fois pour créer votre compte admin
 */

require_once 'config/config.php';
require_once 'includes/Database.php';

try {
    $db = Database::getInstance();
    
    // Vérifier si l'admin existe déjà
    $existingAdmin = $db->fetch(
        "SELECT id FROM admins WHERE email = :email",
        ['email' => 'admin@smmplatform.com']
    );
    
    if ($existingAdmin) {
        echo "❌ L'admin existe déjà !<br>";
        echo "Email: admin@smmplatform.com<br>";
        echo "Mot de passe: password<br>";
        echo "<a href='admin/login.php'>Aller à la connexion admin</a><br>";
        exit;
    }
    
    // Créer le hash du mot de passe
    $passwordHash = password_hash('password', PASSWORD_BCRYPT, ['cost' => 12]);
    
    // Insérer l'admin
    $adminId = $db->insert('admins', [
        'email' => 'admin@smmplatform.com',
        'password_hash' => $passwordHash,
        'name' => 'Administrator',
        'role' => 'super_admin',
        'status' => 'active'
    ]);
    
    if ($adminId) {
        echo "✅ Compte admin créé avec succès !<br><br>";
        echo "📧 <strong>Email:</strong> admin@smmplatform.com<br>";
        echo "🔑 <strong>Mot de passe:</strong> password<br>";
        echo "👑 <strong>Rôle:</strong> Super Admin<br>";
        echo "🔗 <strong>Lien:</strong> <a href='admin/login.php'>Connexion Admin</a><br><br>";
        
        echo "⚠️ <strong>IMPORTANT:</strong> Changez le mot de passe après votre première connexion !<br>";
        echo "🗑️ Supprimez ce fichier (create_admin.php) après utilisation pour la sécurité !<br>";
        
    } else {
        echo "❌ Erreur lors de la création de l'admin<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
}
?>