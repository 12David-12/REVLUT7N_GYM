<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REVOLUT7N GYM - Sistema Principal</title>
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
        
        .menu-section ul li {
            padding: 8px 0;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- SIDEBAR - MENÚ -->
        <div class="sidebar">
            <!-- SISTEMA -->
            <div class="menu-section">
                <h3>Sistema</h3>
                <ul>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="cerrar_sesion.php">Cerrar sesión</a></li>
                    <li><a href="salir.php">Salir</a></li>
                </ul>
            </div>
            
            <!-- PERSONAS -->
            <div class="menu-section">
                <h3>Personas</h3>
                <ul>
                    <li><a href="clientes.php">Clientes</a></li>
                    <li><a href="empleados.php">Empleados</a></li>
                    <li><a href="usuarios.php">Usuarios</a></li>
                </ul>
            </div>
            
            <!-- MEMBRESÍAS -->
            <div class="menu-section">
                <h3>Membresías</h3>
                <ul>
                    <li><a href="tipos_membresia.php">Tipos</a></li>
                    <li><a href="asignar_membresia.php">Asignar</a></li>
                </ul>
            </div>
            
            <!-- PAGOS -->
            <div class="menu-section">
                <h3>Pagos</h3>
                <ul>
                    <li><a href="registrar_pago.php">Registrar</a></li>
                    <li><a href="historial_pagos.php">Historial</a></li>
                </ul>
            </div>
            
            <!-- ENTRENAMIENTO -->
            <div class="menu-section">
                <h3>Entrenamiento</h3>
                <ul>
                    <li><a href="ejercicios.php">Ejercicios</a></li>
                    <li><a href="rutinas.php">Rutinas</a></li>
                </ul>
            </div>
            
            <!-- ASISTENCIA -->
            <div class="menu-section">
                <h3>Asistencia</h3>
                <ul>
                    <li><a href="entrada.php">Entrada</a></li>
                    <li><a href="salida.php">Salida</a></li>
                </ul>
            </div>
            
            <!-- INVENTARIO -->
            <div class="menu-section">
                <h3>Inventario</h3>
                <ul>
                    <li><a href="productos.php">Productos</a></li>
                    <li><a href="stock.php">Stock</a></li>
                </ul>
            </div>
            
            <!-- VENTAS -->
            <div class="menu-section">
                <h3>Ventas</h3>
                <ul>
                    <li><a href="nueva_venta.php">Nueva venta</a></li>
                    <li><a href="historial_ventas.php">Historial</a></li>
                </ul>
            </div>
            
            <!-- COMPRAS -->
            <div class="menu-section">
                <h3>Compras</h3>
                <ul>
                    <li><a href="proveedores.php">Proveedores</a></li>
                    <li><a href="nueva_compra.php">Nueva compra</a></li>
                </ul>
            </div>
            
            <!-- REPORTES -->
            <div class="menu-section">
                <h3>Reportes</h3>
                <ul>
                    <li><a href="reporte_ingresos.php">Ingresos</a></li>
                    <li><a href="reporte_clientes.php">Clientes</a></li>
                    <li><a href="reporte_inventario.php">Inventario</a></li>
                </ul>
            </div>
            
            <!-- CONFIGURACIÓN -->
            <div class="menu-section">
                <h3>Configuración</h3>
                <ul>
                    <li><a href="config_usuarios.php">Usuarios</a></li>
                    <li><a href="config_roles.php">Roles</a></li>
                    <li><a href="backup.php">Backup</a></li>
                </ul>
            </div>
        </div>
        
        <!-- CONTENIDO PRINCIPAL -->
        <div class="main-content">
            <div class="header">
                <h1>REVOLUT7N GYM - Sistema Principal</h1>
                <div class="user-info">
                    <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                    <a href="cerrar_sesion.php">Cerrar Sesión</a>
                </div>
            </div>
            
            <div class="content-box">
                <h2>Bienvenido al Sistema de Gestión</h2>
                <p>Selecciona una opción del menú lateral para comenzar.</p>
            </div>
        </div>
    </div>
</body>
</html>