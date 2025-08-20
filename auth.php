<?php
// db.php – conexión a la base de datos con PDO (MySQL en este caso)
$host = "localhost";
$db   = "evaluacion_herramienta";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear tabla si no existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS usuario (
        idusuario INT AUTO_INCREMENT PRIMARY KEY,
        usuario VARCHAR(100) UNIQUE NOT NULL,
        contrasena VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// ---------------- FUNCIONES ---------------- //

/**
 * Registra un usuario con contraseña en hash
 */
function registerUser(PDO $pdo, string $usuario, string $password): string {
    // Generar hash seguro
    $hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO usuario (usuario, contrasena) VALUES (:usuario, :hash)");
        $stmt->execute([":usuario" => $usuario, ":hash" => $hash]);
        return "Usuario registrado correctamente";
    } catch (PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}

/**
 * Autentica al usuario verificando el hash
 */
function loginUser(PDO $pdo, string $usuario, string $password): string {
    $stmt = $pdo->prepare("SELECT contrasena FROM usuario WHERE usuario = :usuario LIMIT 1");
    $stmt->execute([":usuario" => $usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['contrasena'])) {
        return "✅ Inicio de sesión exitoso";
    }
    return "❌ Credenciales inválidas";
}

// ---------------- DEMO ---------------- //
// Ejemplo de uso:
echo registerUser($pdo, "VanessaNasimba", "Vane12344@");
echo "<br>";
echo loginUser($pdo, "VanessaNasimba", "Vane12344@");  // correcto
echo "<br>";
echo loginUser($pdo, "VanessaNasimba", "Vane12344"); // incorrecto
