<?php
// Conexión a la base de datos
$host = 'localhost';
$db = 'foro';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Crear nueva conversación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titulo'])) {
    $titulo = $conn->real_escape_string($_POST['titulo']);
    $sql = "INSERT INTO conversaciones (titulo) VALUES ('$titulo')";
    $conn->query($sql);
}

// Obtener todas las conversaciones
$sql = "SELECT * FROM conversaciones ORDER BY fecha_creacion DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>FORUM</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/x-icon" href="images/favicon/forum-thinking-3-favicon.ico">
</head>
<body>
    
    <h1>FORUM</h1>
    <img src="images/forum-thinking-4.png" alt="Forum is thinking" style="width: 410px; height: 460px; border: 2px solid black; border-radius: 10px;"/>
    
    <h2>Iniciar nueva conversación</h2>
    <div class="formconversa">
    <form action="index.php" method="POST">
        <input type="text" name="titulo" placeholder="Título de la conversación" required>
        <button type="submit">Iniciar</button>
    </form>
    </div>
   
    <h2>Conversaciones</h2>
    <div class="listconversa">
    <ul>
        <?php while($row = $result->fetch_assoc()): ?>
            <li class="liconversa"><a href="conversacion.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['titulo']) ?></a></li>
        <?php endwhile; ?>
    </ul>
    </div>
</body>
</html>
