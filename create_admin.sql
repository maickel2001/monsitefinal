-- Création d'un compte admin pour l'accès administrateur
-- Exécutez ce fichier dans votre base de données MySQL

-- Insérer un compte admin
INSERT INTO admins (email, password_hash, name, role, status) VALUES (
    'admin@smmplatform.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Mot de passe: password
    'Administrator',
    'super_admin',
    'active'
);

-- Vérifier que l'admin a été créé
SELECT id, email, name, role, status, created_at FROM admins WHERE email = 'admin@smmplatform.com';