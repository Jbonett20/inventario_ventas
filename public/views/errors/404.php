<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página no encontrada</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: #f5f6fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: 'Segoe UI', sans-serif; }
        .error-card { text-align: center; padding: 60px 40px; }
        .error-code { font-size: 120px; font-weight: 800; background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; line-height: 1; }
        .error-message { font-size: 18px; color: #666; margin: 20px 0 30px; }
        .btn-primary { background: linear-gradient(135deg, #667eea, #764ba2); border: none; padding: 12px 30px; border-radius: 10px; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-code">404</div>
        <h2 class="fw-bold">Página no encontrada</h2>
        <p class="error-message">La ruta solicitada no existe en el sistema.</p>
        <a href="/misproyectos/entorno/inventario_ventas/public/" class="btn btn-primary btn-lg">
            <i class="fas fa-home me-2"></i>Ir al inicio
        </a>
    </div>
</body>
</html>
