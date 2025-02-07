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

// Obtener conversación y mensajes
$conversacion_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sql = "SELECT * FROM conversaciones WHERE id = $conversacion_id";
$conversacion = $conn->query($sql)->fetch_assoc();

$sql = "SELECT * FROM mensajes WHERE conversacion_id = $conversacion_id AND respuesta_a IS NULL ORDER BY fecha_mensaje ASC";
$mensajes = $conn->query($sql);

// Agregar nuevo mensaje o respuesta
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = $conn->real_escape_string($_POST['mensaje']);
    $archivo = null;
    $respuesta_a = isset($_POST['respuesta_a']) ? (int)$_POST['respuesta_a'] : null;

    if (!empty($_FILES['archivo']['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["archivo"]["name"]);
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $valid_extensions = ["jpg", "jpeg", "png", "gif", "mp4", "mov", "avi"];
        if (in_array($file_type, $valid_extensions)) {
            if (move_uploaded_file($_FILES["archivo"]["tmp_name"], $target_file)) {
                $archivo = $target_file;
            }
        }
    }

    $sql = "INSERT INTO mensajes (conversacion_id, mensaje, archivo, respuesta_a, fecha_mensaje) VALUES ($conversacion_id, '$mensaje', '$archivo', " . ($respuesta_a ? $respuesta_a : 'NULL') . ", NOW())";
    $conn->query($sql);
    header("Location: conversacion.php?id=$conversacion_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($conversacion['titulo']) ?></title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/x-icon" href="images/favicon/forum-thinking-3-favicon.ico">
</head>
<body>
    <h2><?= htmlspecialchars($conversacion['titulo']) ?></h2>

    <h3>Mensajes</h3>
    <ul>
        <?php while ($row = $mensajes->fetch_assoc()): ?>
            <div class="limensajes">
                <li class="mensaje" id="mensaje-<?= $row['id'] ?>">
                    <div class="contenido-mensaje">
                        <?php
                        // Mostrar el mensaje con saltos de línea respetados
                        echo nl2br(htmlspecialchars($row['mensaje']));
                        ?>
                    </div>
                    <?php if ($row['archivo']): ?>
                        <div class="contenido-media">
                            <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $row['archivo'])): ?>
                                <br><img src="<?= $row['archivo'] ?>" alt="Imagen">
                            <?php elseif (preg_match('/\.(mp4|mov|avi)$/i', $row['archivo'])): ?>
                                <br><video controls>
                                    <source src="<?= $row['archivo'] ?>" type="video/<?= pathinfo($row['archivo'], PATHINFO_EXTENSION) ?>">
                                    Tu navegador no soporta la etiqueta de video.
                                </video>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <small>(<?= $row['fecha_mensaje'] ?>)</small>

                    <!-- Botón de respuesta -->
                    <button onclick="setRespuestaA(<?= $row['id'] ?>)">Responder</button>

                    <!-- Mostrar respuestas anidadas -->
                    <?php
                    $respuesta_sql = "SELECT * FROM mensajes WHERE respuesta_a = " . $row['id'] . " ORDER BY fecha_mensaje ASC";
                    $respuestas = $conn->query($respuesta_sql);
                    while ($respuesta = $respuestas->fetch_assoc()): ?>
                        <div class="respuesta">
                            <div class="contenido-mensaje">
                                <?php
                                // Mostrar la respuesta con saltos de línea respetados
                                echo nl2br(htmlspecialchars($respuesta['mensaje']));
                                ?>
                            </div>
                            <?php if ($respuesta['archivo']): ?>
                                <div class="contenido-media">
                                    <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $respuesta['archivo'])): ?>
                                        <br><img src="<?= $respuesta['archivo'] ?>" alt="Imagen">
                                    <?php elseif (preg_match('/\.(mp4|mov|avi)$/i', $respuesta['archivo'])): ?>
                                        <br><video controls>
                                            <source src="<?= $respuesta['archivo'] ?>" type="video/<?= pathinfo($respuesta['archivo'], PATHINFO_EXTENSION) ?>">
                                            Tu navegador no soporta la etiqueta de video.
                                        </video>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <small>(<?= $respuesta['fecha_mensaje'] ?>)</small>
                        </div>
                    <?php endwhile; ?>
                </li>
            </div>
        <?php endwhile; ?>
    </ul>

    <h3>Agregar nuevo mensaje o respuesta</h3>
    <form action="conversacion.php?id=<?= $conversacion_id ?>" method="POST" enctype="multipart/form-data">
        <textarea name="mensaje" placeholder="Escribe tu mensaje aquí..." required></textarea>
        <input type="file" name="archivo" accept="image/*,video/*">
        <input type="hidden" id="respuesta_a" name="respuesta_a" value="">
        
        <!-- Botones para publicar o cancelar respuesta -->
        <button type="submit">Publicar</button>
        <button type="button" id="cancelar-respuesta" onclick="cancelarRespuesta()" style="display:none;">Cancelar</button>
    </form>

    <p><a href="index.php" class="back-link">Volver al inicio</a></p>
    
    <script>
        function setRespuestaA(id) {
            document.getElementById('respuesta_a').value = id;
            document.getElementById('cancelar-respuesta').style.display = 'inline-block';
            window.scrollTo(0, document.body.scrollHeight);  // Desplaza la pantalla hacia el formulario
        }

        function cancelarRespuesta() {
            document.getElementById('respuesta_a').value = '';
            document.getElementById('cancelar-respuesta').style.display = 'none';
        }
    </script>
</body>
</html>

