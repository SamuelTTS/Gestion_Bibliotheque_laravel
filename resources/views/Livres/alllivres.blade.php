<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyLibrary Pro</title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        :root {
            --primary: #6366f1;
            --primary-dark: #4338ca;
            --secondary: #8b5cf6;

            --success: #10b981;
            --danger: #ef4444;

            --bg: #eef2ff;
            --card: rgba(255, 255, 255, 0.88);

            --text: #0f172a;
            --muted: #64748b;

            --border: rgba(226, 232, 240, 0.7);

            --shadow:
                0 10px 25px rgba(15, 23, 42, 0.08),
                0 4px 12px rgba(15, 23, 42, 0.05);

            --radius: 20px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            padding: 40px 20px;

            background:
                radial-gradient(circle at top left,
                    rgba(99, 102, 241, 0.2),
                    transparent 25%),
                radial-gradient(circle at bottom right,
                    rgba(139, 92, 246, 0.18),
                    transparent 20%),
                var(--bg);

            color: var(--text);
        }

        /* =========================
           LOGOUT FIXÉ
        ========================= */

        .logout-container {
            position: fixed;
            top: 20px;
            right: 25px;
            z-index: 999;
        }

        .btn-logout {
            background: linear-gradient(135deg, #ef4444, #b91c1c);
            color: white;

            padding: 12px 20px;

            border-radius: 14px;

            font-weight: 700;
            text-decoration: none;

            box-shadow:
                0 10px 25px rgba(239, 68, 68, 0.25);

            transition: all 0.25s ease;
        }

        .btn-logout:hover {
            transform: translateY(-3px);

            box-shadow:
                0 18px 35px rgba(239, 68, 68, 0.35);
        }

        /* =========================
           CONTAINER
        ========================= */

        .container {
            max-width: 1150px;
            margin: 0 auto 25px auto;

            background: var(--card);

            backdrop-filter: blur(12px);

            border-radius: var(--radius);

            padding: 28px;

            box-shadow: var(--shadow);

            border: 1px solid rgba(255, 255, 255, 0.6);
        }

        /* =========================
           SEARCH BAR
        ========================= */

        #search-form {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        #search-input {
            flex: 1;
            min-width: 240px;

            padding: 14px 18px;

            border-radius: 14px;
            border: 1px solid var(--border);

            background: white;

            font-size: 0.95rem;

            transition: all 0.25s ease;
        }

        #search-input:focus {
            outline: none;

            border-color: var(--primary);

            box-shadow:
                0 0 0 4px rgba(99, 102, 241, 0.15);

            transform: translateY(-1px);
        }

        /* =========================
           BUTTONS
        ========================= */

        button,
        .btn,
        .btn-add,
        .btn-reset {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            padding: 12px 18px;

            border-radius: 14px;

            border: none;

            cursor: pointer;

            font-weight: 600;
            font-size: 0.92rem;

            text-decoration: none;

            transition: all 0.25s ease;
        }

        .btn-primary,
        .btn-add {
            background:
                linear-gradient(135deg,
                    var(--primary),
                    var(--secondary));

            color: white;

            box-shadow:
                0 8px 18px rgba(99, 102, 241, 0.25);
        }

        .btn-primary:hover,
        .btn-add:hover {
            transform: translateY(-3px);

            box-shadow:
                0 14px 30px rgba(99, 102, 241, 0.35);
        }

        .btn-reset {
            background: white;

            color: var(--muted);

            border: 1px solid var(--border);
        }

        .btn-reset:hover {
            background: #eef2ff;

            color: var(--primary);
        }

        .btn-edit {
            background: rgba(245, 158, 11, 0.12);
            color: #b45309;

            padding: 8px 14px;
        }

        .btn-edit:hover {
            background: #f59e0b;
            color: white;
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.12);
            color: #b91c1c;

            padding: 8px 14px;
        }

        .btn-delete:hover {
            background: var(--danger);
            color: white;
        }

        /* =========================
           HEADER PERSONNALISÉ
        ========================= */

        .header {
            position: relative;

            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;

            padding: 35px;

            border-radius: 25px;

            overflow: hidden;

            background:
                linear-gradient(135deg,
                    rgba(99, 102, 241, 0.95),
                    rgba(139, 92, 246, 0.92));

            box-shadow:
                0 15px 35px rgba(99, 102, 241, 0.25);
        }

        .header::before {
            content: "";

            position: absolute;

            width: 320px;
            height: 320px;

            background: rgba(255, 255, 255, 0.08);

            border-radius: 50%;

            top: -120px;
            right: -80px;
        }

        .header h1 {
            position: relative;

            font-size: 2.4rem;
            font-weight: 800;

            color: white;

            z-index: 2;
        }

        .subtitle {
            position: relative;

            margin-top: 8px;

            color: rgba(255, 255, 255, 0.85);

            font-size: 0.95rem;

            z-index: 2;
        }

        .header-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            z-index: 2;
        }

        /* =========================
           TABLE
        ========================= */

        table {
            width: 100%;
            max-width: 1150px;

            margin: 0 auto;

            border-collapse: collapse;

            overflow: hidden;

            border-radius: var(--radius);

            background: rgba(255, 255, 255, 0.9);

            box-shadow: var(--shadow);
        }

        thead {
            background:
                linear-gradient(135deg,
                    var(--primary),
                    var(--secondary));
        }

        th {
            padding: 18px 16px;

            text-align: center;

            color: white;

            font-size: 0.8rem;
            font-weight: 700;

            text-transform: uppercase;
            letter-spacing: 1px;
        }

        td {
            padding: 18px 16px;

            text-align: center;

            border-bottom: 1px solid rgba(226, 232, 240, 0.6);

            transition: 0.2s ease;
        }

        tbody tr {
            transition: all 0.25s ease;
        }

        tbody tr:hover {
            background: rgba(99, 102, 241, 0.05);
            transform: scale(1.002);
        }

        tr:last-child td {
            border-bottom: none;
        }

        td:last-child {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* =========================
           BADGE
        ========================= */

        .badge {
            background:
                linear-gradient(135deg,
                    #dcfce7,
                    #bbf7d0);

            color: #166534;

            padding: 6px 12px;

            border-radius: 999px;

            font-weight: 700;
            font-size: 0.85rem;
        }

        .pages {
            color: var(--muted);
            font-style: italic;
        }

        /* =========================
           TOAST
        ========================= */

        .toast-notification {
            position: fixed;
            top: 25px;
            right: 25px;

            background:
                linear-gradient(135deg,
                    var(--success),
                    #059669);

            color: white;

            padding: 18px 24px;

            border-radius: 16px;

            display: flex;
            align-items: center;
            gap: 12px;

            box-shadow:
                0 15px 30px rgba(16, 185, 129, 0.25);

            z-index: 1000;

            animation: toastIn 0.5s ease;
        }

        .toast-fade-out {
            animation: toastOut 0.5s ease forwards;
        }

        @keyframes toastIn {
            from {
                opacity: 0;
                transform: translateX(120px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes toastOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }

            to {
                opacity: 0;
                transform: translateX(120px);
            }
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 900px) {

            body {
                padding: 20px 10px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            table {
                display: block;
                overflow-x: auto;
            }

            td:last-child {
                flex-direction: column;
            }

            .btn,
            .btn-add,
            .btn-primary,
            .btn-reset {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <!-- TOAST -->

    @if(session('success'))
    <div id="toast" class="toast-notification">
        <span>✅</span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- LOGOUT -->

    <div class="logout-container">
        <a href="{{route('logout')}}" class="btn-logout">
            ⎋ Logout
        </a>
    </div>



    <!-- HEADER -->

    <div class="container">

        <div class="header">

            <div>
                <h1>📚 MyLibrary Pro</h1>
                <p class="subtitle">
                    Gestion moderne et intelligente de votre bibliothèque
                </p>
            </div>

            <div class="header-actions">

                <a href="{{ route('formlivre') }}" class="btn-add">
                    + Ajouter un livre
                </a>

                <a href="{{ route('adddisc') }}" class="btn-add">
                    + Ajouter une discipline
                </a>

            </div>

        </div>

    </div>

    <!-- SEARCH -->

    <div class="container">

        <form action="{{ route('findlivre') }}" method="POST" id="search-form">

            @csrf

            <input type="text"
                name="research"
                placeholder="🔍 Rechercher un livre..."
                id="search-input">

            <button class="btn-primary" type="submit">
                Rechercher
            </button>

            <a class="btn-reset" href="{{ route('alllivres') }}">
                Réinitialiser
            </a>

        </form>
    </div>
    
    <!-- TABLE -->

    <table>

        <thead>

            <tr>

                <th>ID</th>
                <th>Titre</th>
                <th>Auteur</th>
                <th>Pages</th>
                <th>Discipline</th>
                <th>Prix</th>

                @if($user == 'ad')
                <th>Actions</th>
                @endif

            </tr>

        </thead>

        <tbody>

            @forelse ($livres as $livre)

            <tr>

                <td>#{{ $livre->id }}</td>

                <td>
                    <strong>{{ $livre->titre }}</strong>
                </td>

                <td>{{ $livre->auteur }}</td>

                <td class="pages">
                    {{ $livre->nb_pages }} p.
                </td>

                <td>
                    {{ $livre->discipline->nom }}
                </td>

                <td>
                    <span class="badge">
                        {{ $livre->prix }} €
                    </span>
                </td>

                @if($user == 'ad')

                <td>

                    <a href="{{ route('updateform', $livre->id) }}"
                        class="btn btn-edit">

                        ✏️ Modifier

                    </a>

                    <a href="{{ route('deletelivre', $livre->id) }}"
                        class="btn btn-delete"
                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce livre {{ $livre->titre }} ?');">

                        🗑️ Supprimer

                    </a>

                </td>

                @endif

            </tr>

            @empty

            <tr>

                <td colspan="7"
                    style="padding:40px; text-align:center; color:#94a3b8;">

                    <img width="180"
                        src="{{asset('images/empty.png')}}"
                        alt="Aucun résultat">

                    <br><br>

                    Aucun livre trouvé dans la base.

                </td>

            </tr>

            @endforelse

        </tbody>

    </table>

    <!-- SCRIPT -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const toast = document.getElementById('toast');

            if (toast) {

                setTimeout(() => {

                    toast.classList.add('toast-fade-out');

                    setTimeout(() => toast.remove(), 500);

                }, 4000);
            }
        });
    </script>

</body>

</html>
