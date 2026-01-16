<?php
// index.php - SELECCIÓN DE PORTAL
session_start();

// Si ya tiene sesión activa, redirigir al portal correspondiente
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol'] == 'admin_general') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: ingeniero/dashboard.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriManage - Sistema de Gestión Agrícola</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #198754 0%, #0d6938 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .portal-container {
            max-width: 900px;
            width: 100%;
            padding: 20px;
        }
        
        .logo-section {
            text-align: center;
            color: white;
            margin-bottom: 3rem;
        }
        
        .logo-section i {
            font-size: 5rem;
            margin-bottom: 1rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .logo-section h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .logo-section p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .portal-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            height: 100%;
        }
        
        .portal-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        }
        
        .portal-admin {
            border-top: 5px solid #dc3545;
        }
        
        .portal-ingeniero {
            border-top: 5px solid #198754;
        }
        
        .portal-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }
        
        .portal-admin .portal-icon {
            color: #dc3545;
        }
        
        .portal-ingeniero .portal-icon {
            color: #198754;
        }
        
        .portal-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .portal-description {
            color: #6c757d;
            margin-bottom: 1.5rem;
        }
        
        .btn-portal {
            width: 100%;
            padding: 0.75rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.2s;
        }
        
        .btn-admin {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
        }
        
        .btn-admin:hover {
            background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
            transform: scale(1.02);
        }
        
        .btn-ingeniero {
            background: linear-gradient(135deg, #198754 0%, #0d6938 100%);
            border: none;
        }
        
        .btn-ingeniero:hover {
            background: linear-gradient(135deg, #0d6938 0%, #198754 100%);
            transform: scale(1.02);
        }
        
        .features {
            list-style: none;
            padding: 0;
            margin-bottom: 1.5rem;
        }
        
        .features li {
            padding: 0.5rem 0;
            color: #495057;
        }
        
        .features i {
            margin-right: 0.5rem;
            color: #198754;
        }
        
        .footer {
            text-align: center;
            color: white;
            margin-top: 3rem;
        }
    </style>
</head>
<body>
    <div class="portal-container">
        <!-- Logo y Título -->
        <div class="logo-section">
            <i class="bi bi-trees"></i>
            <h1>AgriManage</h1>
            <p>Sistema de Gestión Agrícola Multiempresa</p>
        </div>
        
        <!-- Selección de Portal -->
        <div class="row g-4">
            <!-- Portal Administrador -->
            <div class="col-md-6">
                <div class="portal-card portal-admin" onclick="window.location.href='admin/login.php'">
                    <div class="text-center">
                        <i class="bi bi-shield-lock-fill portal-icon"></i>
                        <h2 class="portal-title">Portal Administrador</h2>
                        <p class="portal-description">
                            Gestión completa del sistema
                        </p>
                    </div>
                    
                    <ul class="features">
                        <li><i class="bi bi-check-circle-fill"></i> Crear empresas agroquímicas</li>
                        <li><i class="bi bi-check-circle-fill"></i> Gestionar usuarios</li>
                        <li><i class="bi bi-check-circle-fill"></i> Reportes globales</li>
                        <li><i class="bi bi-check-circle-fill"></i> Auditoría del sistema</li>
                    </ul>
                    
                    <a href="admin/login.php" class="btn btn-danger btn-admin">
                        <i class="bi bi-box-arrow-in-right"></i> Acceder como Administrador
                    </a>
                </div>
            </div>
            
            <!-- Portal Ingeniero -->
            <div class="col-md-6">
                <div class="portal-card portal-ingeniero" onclick="window.location.href='ingeniero/login.php'">
                    <div class="text-center">
                        <i class="bi bi-clipboard-data portal-icon"></i>
                        <h2 class="portal-title">Portal Ingeniero</h2>
                        <p class="portal-description">
                            Gestión de clientes y cultivos
                        </p>
                    </div>
                    
                    <ul class="features">
                        <li><i class="bi bi-check-circle-fill"></i> Gestionar clientes</li>
                        <li><i class="bi bi-check-circle-fill"></i> Registrar lotes y cultivos</li>
                        <li><i class="bi bi-check-circle-fill"></i> Aplicar productos</li>
                        <li><i class="bi bi-check-circle-fill"></i> Reportes de producción</li>
                    </ul>
                    
                    <a href="ingeniero/login.php" class="btn btn-success btn-ingeniero">
                        <i class="bi bi-box-arrow-in-right"></i> Acceder como Ingeniero
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Información adicional -->
        <div class="footer">
            <p class="mb-2">
                <i class="bi bi-info-circle"></i>
                Seleccione el portal según su rol en el sistema
            </p>
            <small>AgriManage v1.0.0 &copy; 2024 - Todos los derechos reservados</small>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>