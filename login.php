<?php
session_start();
require_once __DIR__ . '/db.php';

// Verificar si ya está logueado
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($usuario) || empty($password)) {
        $error = 'Por favor ingrese usuario y contraseña';
    } else {
        $pdo = db();
        // Buscar usuario por usuario y contraseña en texto plano
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ? AND password = ? AND activo = 1");
        $stmt->execute([$usuario, $password]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['user_rol'] = $user['rol'];
            $_SESSION['user_usuario'] = $user['usuario'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Farmacia Central</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 40px 40px;
            animation: moveBackground 20s linear infinite;
        }

        @keyframes moveBackground {
            0% { transform: translate(0, 0); }
            100% { transform: translate(40px, 40px); }
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            z-index: 1;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 32px;
            padding: 3rem 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .logo-icon span {
            font-size: 3rem;
            color: white;
        }

        .logo h1 {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logo p {
            color: #666;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        .input-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
            font-size: 0.85rem;
        }

        .input-icon {
            position: relative;
        }

        .input-icon .material-icons-round:first-child {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 1.2rem;
            z-index: 1;
        }

        .input-icon input {
            width: 100%;
            padding: 0.9rem 1rem 0.9rem 3rem;
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            font-size: 1rem;
            transition: all 0.3s;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .input-icon input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            background: transparent;
            border: none;
            padding: 0;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .toggle-password .material-icons-round {
            font-size: 1.2rem;
            color: #999;
            transition: color 0.3s;
        }

        .toggle-password:hover .material-icons-round {
            color: #667eea;
        }

        .validation-rules {
            margin-top: 0.5rem;
            padding: 0.75rem;
            background: #f9fafb;
            border-radius: 12px;
            font-size: 0.75rem;
        }

        .validation-rules p {
            margin: 0.25rem 0;
            color: #666;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .validation-rules .valid {
            color: #10b981;
        }

        .validation-rules .invalid {
            color: #ef4444;
        }

        .btn-login {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(102, 126, 234, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 0.75rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 600;
            animation: shake 0.5s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .footer {
            text-align: center;
            margin-top: 2rem;
            color: #999;
            font-size: 0.75rem;
        }

        .demo-credentials {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #f0fdf4;
            border-radius: 12px;
            font-size: 0.8rem;
            text-align: center;
            border: 1px solid #bbf7d0;
        }

        .demo-credentials p {
            margin: 0.25rem 0;
            color: #166534;
        }

        .demo-credentials strong {
            color: #15803d;
        }

        .demo-credentials .title {
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: #065f46;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <div class="logo-icon">
                    <span class="material-icons-round">local_pharmacy</span>
                </div>
                <h1>Farmacia Central</h1>
                <p>Sistema de Gestión Integral</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <span class="material-icons-round" style="font-size: 1rem;">error</span>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm">
                <div class="input-group">
                    <label>Usuario</label>
                    <div class="input-icon">
                        <span class="material-icons-round">person</span>
                        <input type="text" name="usuario" id="usuario" required placeholder="Ingrese su usuario" autocomplete="off">
                    </div>
                </div>

                <div class="input-group">
                    <label>Contraseña</label>
                    <div class="input-icon password-wrapper">
                        <span class="material-icons-round">lock</span>
                        <input type="password" name="password" id="password" required placeholder="Ingrese su contraseña">
                        <button type="button" class="toggle-password" id="togglePasswordBtn">
                            <span class="material-icons-round">visibility_off</span>
                        </button>
                    </div>
                    <div class="validation-rules" id="validationRules">
                        <p id="ruleLength">
                            <span class="material-icons-round" style="font-size: 0.9rem;">info</span>
                            Mínimo 6 caracteres
                        </p>
                        <p id="ruleLetter">
                            <span class="material-icons-round" style="font-size: 0.9rem;">info</span>
                            Al menos una letra
                        </p>
                        <p id="ruleNumber">
                            <span class="material-icons-round" style="font-size: 0.9rem;">info</span>
                            Al menos un número
                        </p>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <span class="material-icons-round">login</span>
                    Iniciar Sesión
                </button>
            </form>

            

            <div class="footer">
                <p>Farmacia Central © 2024 | Sistema de Gestión Profesional</p>
            </div>
        </div>
    </div>

    <script>
        // ============================================
        // FUNCIÓN PARA MOSTRAR/OCULTAR CONTRASEÑA
        // ============================================
        const togglePasswordBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');

        togglePasswordBtn.addEventListener('click', function() {
            const icon = this.querySelector('.material-icons-round');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.textContent = 'visibility';
                icon.style.color = '#667eea';
            } else {
                passwordInput.type = 'password';
                icon.textContent = 'visibility_off';
                icon.style.color = '#999';
            }
        });

        // ============================================
        // VALIDACIONES DE CONTRASEÑA EN TIEMPO REAL
        // ============================================
        const ruleLength = document.getElementById('ruleLength');
        const ruleLetter = document.getElementById('ruleLetter');
        const ruleNumber = document.getElementById('ruleNumber');

        function updateValidation(password) {
            const lengthIcon = ruleLength.querySelector('.material-icons-round');
            const letterIcon = ruleLetter.querySelector('.material-icons-round');
            const numberIcon = ruleNumber.querySelector('.material-icons-round');
            
            // Regla: mínimo 6 caracteres
            if (password.length >= 6) {
                ruleLength.classList.add('valid');
                ruleLength.classList.remove('invalid');
                lengthIcon.textContent = 'check_circle';
            } else {
                ruleLength.classList.remove('valid');
                ruleLength.classList.add('invalid');
                lengthIcon.textContent = 'cancel';
            }
            
            // Regla: al menos una letra
            if (/[a-zA-Z]/.test(password)) {
                ruleLetter.classList.add('valid');
                ruleLetter.classList.remove('invalid');
                letterIcon.textContent = 'check_circle';
            } else {
                ruleLetter.classList.remove('valid');
                ruleLetter.classList.add('invalid');
                letterIcon.textContent = 'cancel';
            }
            
            // Regla: al menos un número
            if (/[0-9]/.test(password)) {
                ruleNumber.classList.add('valid');
                ruleNumber.classList.remove('invalid');
                numberIcon.textContent = 'check_circle';
            } else {
                ruleNumber.classList.remove('valid');
                ruleNumber.classList.add('invalid');
                numberIcon.textContent = 'cancel';
            }
        }

        passwordInput.addEventListener('input', function() {
            updateValidation(this.value);
        });

        // ============================================
        // VALIDACIÓN AL ENVIAR EL FORMULARIO
        // ============================================
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const password = passwordInput.value;
            if (password.length < 6 || !/[a-zA-Z]/.test(password) || !/[0-9]/.test(password)) {
                e.preventDefault();
                alert('⚠️ La contraseña debe cumplir con todos los requisitos de seguridad:\n\n✓ Mínimo 6 caracteres\n✓ Al menos una letra\n✓ Al menos un número');
            }
        });

        // ============================================
        // ANIMACIÓN DE ENTRADA
        // ============================================
        const inputs = document.querySelectorAll('.input-icon input');
        inputs.forEach((input, index) => {
            input.style.animation = `slideUp 0.5s ease-out ${index * 0.1}s both`;
        });
    </script>
</body>
</html>