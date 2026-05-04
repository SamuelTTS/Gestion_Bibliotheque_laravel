<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <title>Login</title>

    <style>
        body {
            background: linear-gradient(135deg, #4f46e5, #6d28d9);
            height: 100vh;
        }

        .card {
            border-radius: 15px;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #4f46e5;
        }

        .btn-primary {
            background-color: #4f46e5;
            border: none;
        }

        .btn-primary:hover {
            background-color: #4338ca;
        }
    </style>
</head>

<body>

<div class="container d-flex justify-content-center align-items-center vh-100">

    <div class="card shadow-lg p-4" style="width: 400px;">

        <h2 class="text-center mb-4">🔐 Connexion</h2>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('loginuser') }}">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    placeholder="Entrer votre email"
                    required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <div class="input-group">
                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        placeholder="Entrer votre mot de passe"
                        required>

                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()">
                        👁️
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                Se connecter
            </button>

            <p class="text-center mt-3">
                Pas encore de compte ?
                <a href="{{ route('formregister') }}">S'inscrire</a>
            </p>

        </form>

    </div>

</div>

<script>
function togglePassword() {
    let input = document.getElementById("password");
    input.type = input.type === "password" ? "text" : "password";
}
</script>

</body>
</html>
