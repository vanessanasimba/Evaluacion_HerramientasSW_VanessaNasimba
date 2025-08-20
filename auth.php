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

// --------- FUNCIONES --------- //
function registerUser(PDO $pdo, string $usuario, string $password): string {
    // Evita registrar duplicado si re-ejecutas el archivo
    $q = $pdo->prepare("SELECT 1 FROM usuario WHERE usuario=:u LIMIT 1");
    $q->execute([':u'=>$usuario]);
    if ($q->fetch()) return "Usuario ya existe, no se vuelve a registrar.";

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO usuario (usuario, contrasena) VALUES (:u, :h)");
    $stmt->execute([':u'=>$usuario, ':h'=>$hash]);
    return "Usuario registrado correctamente";
}

function loginUser(PDO $pdo, string $usuario, string $password): string {
    $stmt = $pdo->prepare("SELECT idusuario, contrasena FROM usuario WHERE usuario=:u LIMIT 1");
    $stmt->execute([':u'=>$usuario]);
    $row = $stmt->fetch();
    if(!$row || empty($row['contrasena'])) return "❌ Credenciales inválidas";

    // rtrim por si la columna fue CHAR o hubo copy/paste con salto de línea
    $hash = rtrim($row['contrasena']);

    if (password_verify($password, $hash)) {
        // Opcional: rehash si el algoritmo por defecto cambió
        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            $new = password_hash($password, PASSWORD_DEFAULT);
            $upd = $pdo->prepare("UPDATE usuario SET contrasena=:h WHERE idusuario=:id");
            $upd->execute([':h'=>$new, ':id'=>$row['idusuario']]);
        }
        return "✅ Inicio de sesión exitoso";
    }
    return "❌ Credenciales inválidas";
}

// --------- DEMO --------- //
// Ejecuta UNA sola vez el registro; en siguientes corridas no reinsertará:
echo registerUser($pdo, "VanessaMedina", "Vane12344"), 
"<br>";
echo loginUser($pdo, "VanessaMedina", "Vane12344"), 
"<br>";   
echo loginUser($pdo, "VanessaMedina", "Vane1234");    
