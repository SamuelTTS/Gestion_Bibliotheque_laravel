<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <title>Inscription</title>

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

            <h2 class="text-center mb-4">📝 Inscription</h2>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Nom</label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        class="form-control"
                        placeholder="Votre nom"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        placeholder="Votre email"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="Mot de passe"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirmer mot de passe</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        class="form-control"
                        placeholder="Confirmer mot de passe"
                        required>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    S'inscrire
                </button>

            </form>

            <p class="text-center mt-3">
                Déjà un compte ?
                <a href="{{ route('login') }}">Se connecter</a>
            </p>

        </div>

    </div>

</body>

</html>
