<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include 'conexion.php';

// Variables para mensajes
$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $cedula = trim($_POST['cedula'] ?? '');
    $nombres = trim($_POST['nombres'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
    $sexo = trim($_POST['sexo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $cargo = trim($_POST['cargo'] ?? '');
    $fecha_ingreso = trim($_POST['fecha_ingreso'] ?? '');
    $salario = trim($_POST['salario'] ?? '');

    // Array para almacenar errores
    $errores = [];

    // VALIDACIONES
    // Validar cédula ecuatoriana (exactamente 10 dígitos)
    if (empty($cedula)) {
        $errores[] = "La cédula es obligatoria";
    } elseif (!preg_match('/^[0-9]{10}$/', $cedula)) {
        $errores[] = "La cédula ecuatoriana debe contener exactamente 10 dígitos";
    }

    // Validar nombres (solo letras y espacios)
    if (empty($nombres)) {
        $errores[] = "El nombre es obligatorio";
    } elseif (!preg_match('/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/', $nombres)) {
        $errores[] = "El nombre solo debe contener letras y espacios";
    } elseif (strlen($nombres) > 100) {
        $errores[] = "El nombre no puede exceder 100 caracteres";
    }

    // Validar apellidos (solo letras y espacios)
    if (empty($apellidos)) {
        $errores[] = "El apellido es obligatorio";
    } elseif (!preg_match('/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/', $apellidos)) {
        $errores[] = "El apellido solo debe contener letras y espacios";
    } elseif (strlen($apellidos) > 100) {
        $errores[] = "El apellido no puede exceder 100 caracteres";
    }

    // Validar fecha de nacimiento
    if (empty($fecha_nacimiento)) {
        $errores[] = "La fecha de nacimiento es obligatoria";
    } else {
        $fecha_nac = strtotime($fecha_nacimiento);
        $fecha_hoy = time();
        if ($fecha_nac > $fecha_hoy) {
            $errores[] = "La fecha de nacimiento no puede ser en el futuro";
        }
    }

    // Validar sexo
    if (empty($sexo)) {
        $errores[] = "El sexo es obligatorio";
    } elseif (!in_array($sexo, ['M', 'F', 'Otro'])) {
        $errores[] = "Sexo inválido";
    }

    // Validar teléfono (solo números)
    if (empty($telefono)) {
        $errores[] = "El teléfono es obligatorio";
    } elseif (!preg_match('/^[0-9\s\-\+\(\)]{7,20}$/', $telefono)) {
        $errores[] = "El teléfono debe contener solo números y caracteres válidos";
    }

    // Validar correo
    if (empty($correo)) {
        $errores[] = "El correo es obligatorio";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo no es válido";
    } elseif (strlen($correo) > 100) {
        $errores[] = "El correo no puede exceder 100 caracteres";
    }

    // Validar dirección
    if (!empty($direccion) && strlen($direccion) > 255) {
        $errores[] = "La dirección no puede exceder 255 caracteres";
    }

    // Validar cargo
    if (empty($cargo)) {
        $errores[] = "El cargo es obligatorio";
    } elseif (strlen($cargo) > 100) {
        $errores[] = "El cargo no puede exceder 100 caracteres";
    }

    // Validar fecha de ingreso
    if (empty($fecha_ingreso)) {
        $errores[] = "La fecha de ingreso es obligatoria";
    } else {
        $fecha_ing = strtotime($fecha_ingreso);
        if ($fecha_ing > $fecha_hoy) {
            $errores[] = "La fecha de ingreso no puede ser en el futuro";
        }
    }

    // Validar salario (solo números con decimales)
    if (empty($salario)) {
        $errores[] = "El salario es obligatorio";
    } elseif (!preg_match('/^\d+(\.\d{1,2})?$/', $salario)) {
        $errores[] = "El salario debe ser un número válido";
    } elseif ($salario < 0) {
        $errores[] = "El salario no puede ser negativo";
    }

    // Si no hay errores, guardar en la base de datos
    if (empty($errores)) {
        // Verificar si la cédula ya existe
        $stmt = $conexion->prepare("SELECT id_persona FROM personas WHERE cedula = ?");
        $stmt->bind_param("s", $cedula);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $mensaje = "La cédula ya está registrada en el sistema";
            $tipo_mensaje = "error";
        } else {
            // Insertar persona
            $stmt = $conexion->prepare(
                "INSERT INTO personas (cedula, nombres, apellidos, fecha_nacimiento, sexo, telefono, correo, direccion, estado) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)"
            );
            $stmt->bind_param(
                "ssssssss",
                $cedula,
                $nombres,
                $apellidos,
                $fecha_nacimiento,
                $sexo,
                $telefono,
                $correo,
                $direccion
            );

            if ($stmt->execute()) {
                $id_persona = $conexion->insert_id;

                // Insertar empleado
                $stmt = $conexion->prepare(
                    "INSERT INTO empleados (id_persona, cargo, fecha_ingreso, salario) 
                     VALUES (?, ?, ?, ?)"
                );
                $stmt->bind_param("isss", $id_persona, $cargo, $fecha_ingreso, $salario);

                if ($stmt->execute()) {
                    $mensaje = "Empleado registrado exitosamente";
                    $tipo_mensaje = "exito";
                    // Limpiar el formulario
                    $_POST = [];
                } else {
                    $mensaje = "Error al registrar el empleado: " . $conexion->error;
                    $tipo_mensaje = "error";
                }
            } else {
                $mensaje = "Error al registrar la persona: " . $conexion->error;
                $tipo_mensaje = "error";
            }
        }
    } else {
        $mensaje = implode("<br>", $errores);
        $tipo_mensaje = "error";
    }
}

// Obtener lista de empleados
$empleados = [];
$resultado = $conexion->query(
    "SELECT e.id_empleado, p.id_persona, p.cedula, p.nombres, p.apellidos, 
            p.telefono, p.correo, e.cargo, e.fecha_ingreso, e.salario 
     FROM empleados e 
     INNER JOIN personas p ON e.id_persona = p.id_persona 
     ORDER BY p.nombres ASC"
);

if ($resultado) {
    $empleados = $resultado->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Empleados - REVOLUT7N GYM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            overflow-y: auto;
        }

        .sidebar h2 {
            margin-bottom: 20px;
            font-size: 18px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        .menu-section {
            margin-bottom: 30px;
        }

        .menu-section h3 {
            font-size: 14px;
            color: #95a5a6;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .menu-section ul {
            list-style: none;
        }

        .menu-section ul li a {
            color: #ecf0f1;
            text-decoration: none;
            display: block;
            padding: 8px 15px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .menu-section ul li a:hover {
            background-color: #3498db;
        }

        .main-content {
            flex: 1;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header h1 {
            color: #2c3e50;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info a {
            padding: 10px 20px;
            background-color: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .user-info a:hover {
            background-color: #c0392b;
        }

        .content-box {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #ecf0f1;
        }

        .tab-button {
            padding: 10px 20px;
            background: none;
            border: none;
            color: #2c3e50;
            cursor: pointer;
            font-size: 16px;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .tab-button.active {
            color: #3498db;
            border-bottom-color: #3498db;
        }

        .tab-button:hover {
            color: #3498db;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-row.full {
            grid-template-columns: 1fr;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
            font-weight: 500;
        }

        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="date"],
        input[type="tel"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            font-family: inherit;
            font-size: 14px;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        button {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
        }

        button.btn-secondary {
            background-color: #95a5a6;
        }

        button.btn-secondary:hover {
            background-color: #7f8c8d;
        }

        .mensaje {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: none;
        }

        .mensaje.exito {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            display: block;
        }

        .mensaje.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            display: block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table thead {
            background-color: #2c3e50;
            color: white;
        }

        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        table tbody tr:hover {
            background-color: #f5f5f5;
        }

        table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        .btn-editar {
            background-color: #f39c12;
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn-editar:hover {
            background-color: #e67e22;
        }

        .btn-eliminar {
            background-color: #e74c3c;
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn-eliminar:hover {
            background-color: #c0392b;
        }

        .acciones {
            display: flex;
            gap: 5px;
        }

        .info-text {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
        }

        .error-input {
            border-color: #e74c3c !important;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- SIDEBAR -->
        <div class="sidebar">
            <div class="menu-section">
                <h3>Sistema</h3>
                <ul>
                    <li><a href="principal.php">Inicio</a></li>
                    <li><a href="cerrar_sesion.php">Cerrar sesión</a></li>
                </ul>
            </div>

            <div class="menu-section">
                <h3>Personas</h3>
                <ul>
                    <li><a href="clientes.php">Clientes</a></li>
                    <li><a href="empleados.php" style="background-color: #3498db;">Empleados</a></li>
                    <li><a href="usuarios.php">Usuarios</a></li>
                </ul>
            </div>
        </div>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="main-content">
            <div class="header">
                <h1>Gestión de Empleados</h1>
                <div class="user-info">
                    <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                    <a href="cerrar_sesion.php">Cerrar Sesión</a>
                </div>
            </div>

            <!-- MENSAJES -->
            <div class="mensaje <?php echo $tipo_mensaje; ?>" id="mensaje">
                <?php echo $mensaje; ?>
            </div>

            <!-- TABS -->
            <div class="content-box">
                <div class="tabs">
                    <button class="tab-button active" onclick="cambiarTab('formulario')">
                        Agregar Empleado
                    </button>
                    <button class="tab-button" onclick="cambiarTab('lista')">
                        Lista de Empleados (<?php echo count($empleados); ?>)
                    </button>
                </div>

                <!-- TAB 1: FORMULARIO -->
                <div id="formulario" class="tab-content active">
                    <h2>Formulario de Registro de Empleado</h2>
                    <p style="color: #7f8c8d; margin-bottom: 20px;">
                        Completa todos los campos marcados con * (obligatorio)
                    </p>

                    <form method="POST" id="formEmpleado" onsubmit="validarFormulario(event)">
                        <!-- DATOS PERSONALES -->
                        <h3 style="margin-top: 20px; margin-bottom: 15px; color: #2c3e50;">
                            Datos Personales
                        </h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="cedula">Cédula Ecuatoriana * <span class="info-text">(10 dígitos)</span></label>
                                <input type="text" id="cedula" name="cedula" 
                                       placeholder="Ej: 0957152804" maxlength="10"
                                       value="<?php echo htmlspecialchars($_POST['cedula'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="nombres">Nombres * <span class="info-text">(Solo letras)</span></label>
                                <input type="text" id="nombres" name="nombres" 
                                       placeholder="Ej: Juan Carlos" maxlength="100"
                                       value="<?php echo htmlspecialchars($_POST['nombres'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="apellidos">Apellidos * <span class="info-text">(Solo letras)</span></label>
                                <input type="text" id="apellidos" name="apellidos" 
                                       placeholder="Ej: Pérez García" maxlength="100"
                                       value="<?php echo htmlspecialchars($_POST['apellidos'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="fecha_nacimiento">Fecha de Nacimiento *</label>
                                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                                       value="<?php echo htmlspecialchars($_POST['fecha_nacimiento'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="sexo">Sexo *</label>
                                <select id="sexo" name="sexo">
                                    <option value="">Selecciona una opción</option>
                                    <option value="M" <?php echo ($_POST['sexo'] ?? '') === 'M' ? 'selected' : ''; ?>>Masculino</option>
                                    <option value="F" <?php echo ($_POST['sexo'] ?? '') === 'F' ? 'selected' : ''; ?>>Femenino</option>
                                    <option value="Otro" <?php echo ($_POST['sexo'] ?? '') === 'Otro' ? 'selected' : ''; ?>>Otro</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="telefono">Teléfono * <span class="info-text">(Solo números)</span></label>
                                <input type="tel" id="telefono" name="telefono" 
                                       placeholder="Ej: 09123456789" maxlength="20"
                                       value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="correo">Correo Electrónico *</label>
                                <input type="email" id="correo" name="correo" 
                                       placeholder="Ej: juan@example.com" maxlength="100"
                                       value="<?php echo htmlspecialchars($_POST['correo'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-group form-row full">
                            <label for="direccion">Dirección</label>
                            <textarea id="direccion" name="direccion" 
                                      placeholder="Ej: Calle Principal 123, Apto 4"><?php echo htmlspecialchars($_POST['direccion'] ?? ''); ?></textarea>
                        </div>

                        <!-- DATOS LABORALES -->
                        <h3 style="margin-top: 30px; margin-bottom: 15px; color: #2c3e50;">
                            Datos Laborales
                        </h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="cargo">Cargo * <span class="info-text">(Solo texto)</span></label>
                                <input type="text" id="cargo" name="cargo" 
                                       placeholder="Ej: Entrenador Personal" maxlength="100"
                                       value="<?php echo htmlspecialchars($_POST['cargo'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="fecha_ingreso">Fecha de Ingreso *</label>
                                <input type="date" id="fecha_ingreso" name="fecha_ingreso"
                                       value="<?php echo htmlspecialchars($_POST['fecha_ingreso'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="salario">Salario * <span class="info-text">(Solo números con decimales)</span></label>
                                <input type="number" id="salario" name="salario" 
                                       placeholder="Ej: 1500.50" step="0.01" min="0"
                                       value="<?php echo htmlspecialchars($_POST['salario'] ?? ''); ?>">
                            </div>
                        </div>

                        <!-- BOTONES -->
                        <div style="margin-top: 30px; display: flex; gap: 10px;">
                            <button type="submit">Registrar Empleado</button>
                            <button type="reset" class="btn-secondary">Limpiar Formulario</button>
                        </div>
                    </form>
                </div>

                <!-- TAB 2: LISTA DE EMPLEADOS -->
                <div id="lista" class="tab-content">
                    <h2>Lista de Empleados</h2>
                    
                    <?php if (count($empleados) > 0): ?>
                        <div style="overflow-x: auto;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Cédula</th>
                                        <th>Nombres</th>
                                        <th>Apellidos</th>
                                        <th>Teléfono</th>
                                        <th>Correo</th>
                                        <th>Cargo</th>
                                        <th>Fecha Ingreso</th>
                                        <th>Salario</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($empleados as $empleado): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($empleado['cedula']); ?></td>
                                            <td><?php echo htmlspecialchars($empleado['nombres']); ?></td>
                                            <td><?php echo htmlspecialchars($empleado['apellidos']); ?></td>
                                            <td><?php echo htmlspecialchars($empleado['telefono']); ?></td>
                                            <td><?php echo htmlspecialchars($empleado['correo']); ?></td>
                                            <td><?php echo htmlspecialchars($empleado['cargo']); ?></td>
                                            <td><?php echo htmlspecialchars($empleado['fecha_ingreso']); ?></td>
                                            <td>$<?php echo number_format($empleado['salario'], 2); ?></td>
                                            <td>
                                                <div class="acciones">
                                                    <button class="btn-editar" 
                                                            onclick="editarEmpleado(<?php echo $empleado['id_empleado']; ?>)">
                                                        Editar
                                                    </button>
                                                    <button class="btn-eliminar" 
                                                            onclick="if(confirm('¿Estás seguro?')) eliminarEmpleado(<?php echo $empleado['id_empleado']; ?>)">
                                                        Eliminar
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p style="text-align: center; color: #7f8c8d; padding: 20px;">
                            No hay empleados registrados. Crea el primero usando el formulario de arriba.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Cambiar entre tabs
        function cambiarTab(tab) {
            // Ocultar todos los tabs
            document.querySelectorAll('.tab-content').forEach(el => {
                el.classList.remove('active');
            });
            document.querySelectorAll('.tab-button').forEach(el => {
                el.classList.remove('active');
            });

            // Mostrar el tab seleccionado
            document.getElementById(tab).classList.add('active');
            event.target.classList.add('active');
        }

        // Validación de formulario en el cliente
        function validarFormulario(event) {
            event.preventDefault();

            // Obtener valores
            const cedula = document.getElementById('cedula').value.trim();
            const nombres = document.getElementById('nombres').value.trim();
            const apellidos = document.getElementById('apellidos').value.trim();
            const fecha_nac = document.getElementById('fecha_nacimiento').value;
            const sexo = document.getElementById('sexo').value;
            const telefono = document.getElementById('telefono').value.trim();
            const correo = document.getElementById('correo').value.trim();
            const cargo = document.getElementById('cargo').value.trim();
            const fecha_ingreso = document.getElementById('fecha_ingreso').value;
            const salario = document.getElementById('salario').value.trim();

            let errores = [];

            // Validar cédula ecuatoriana (exactamente 10 dígitos)
            if (!cedula) {
                errores.push("La cédula es obligatoria");
            } else if (!/^[0-9]{10}$/.test(cedula)) {
                errores.push("La cédula ecuatoriana debe contener exactamente 10 dígitos");
            }

            // Validar nombres
            if (!nombres) {
                errores.push("El nombre es obligatorio");
            } else if (!/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/.test(nombres)) {
                errores.push("El nombre solo debe contener letras y espacios");
            }

            // Validar apellidos
            if (!apellidos) {
                errores.push("El apellido es obligatorio");
            } else if (!/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/.test(apellidos)) {
                errores.push("El apellido solo debe contener letras y espacios");
            }

            // Validar fecha nacimiento
            if (!fecha_nac) {
                errores.push("La fecha de nacimiento es obligatoria");
            }

            // Validar sexo
            if (!sexo) {
                errores.push("El sexo es obligatorio");
            }

            // Validar teléfono
            if (!telefono) {
                errores.push("El teléfono es obligatorio");
            } else if (!/^[0-9\s\-\+\(\)]{7,20}$/.test(telefono)) {
                errores.push("El teléfono debe contener solo números");
            }

            // Validar correo
            if (!correo) {
                errores.push("El correo es obligatorio");
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
                errores.push("El correo no es válido");
            }

            // Validar cargo
            if (!cargo) {
                errores.push("El cargo es obligatorio");
            }

            // Validar fecha ingreso
            if (!fecha_ingreso) {
                errores.push("La fecha de ingreso es obligatoria");
            }

            // Validar salario
            if (!salario) {
                errores.push("El salario es obligatorio");
            } else if (!/^\d+(\.\d{1,2})?$/.test(salario)) {
                errores.push("El salario debe ser un número válido");
            }

            if (errores.length > 0) {
                alert("Por favor corrige los siguientes errores:\n\n" + errores.join("\n"));
                return false;
            }

            // Si todo es válido, enviar el formulario
            document.getElementById('formEmpleado').submit();
        }

        // Funciones para editar y eliminar (por implementar)
        function editarEmpleado(id) {
            alert('Función editar en desarrollo - ID: ' + id);
        }

        function eliminarEmpleado(id) {
            alert('Función eliminar en desarrollo - ID: ' + id);
        }

        // Limpiar mensaje después de 5 segundos
        window.addEventListener('load', function() {
            const mensaje = document.getElementById('mensaje');
            if (mensaje.classList.contains('exito') || mensaje.classList.contains('error')) {
                setTimeout(function() {
                    mensaje.style.display = 'none';
                }, 5000);
            }
        });
    </script>
</body>
</html>