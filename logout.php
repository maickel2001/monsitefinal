<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/config.php';
require_once 'includes/User.php';

// Vérifier si l'utilisateur est connecté avant de le déconnecter
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user') {
    $user = new User();
    
    // Logout the user
    $user->logout();
    
    // S'assurer que la session est bien détruite
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

// Nettoyer les cookies de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Rediriger vers la page d'accueil
header('Location: index.php');
exit;