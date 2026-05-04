<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ma Bibliothèque</title>
    <style>
        /* Variables de couleurs pour une maintenance facile */
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg-body: #f8fafc;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-body);
            margin: 0;
            padding: 40px 20px;
            color: var(--text-main);
        }

        .container {
            max-width: 1000px;
            margin: 0 auto 25px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        /* --- Barre de recherche améliorée --- */
        #search-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        #search-input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            outline: none;
            transition: border-color 0.2s;
        }

        #search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        /* --- Base commune pour Boutons ET Liens --- */
        button,
        .btn,
        .btn-add {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            /* Enlève le souligné des liens <a> */
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
            white-space: nowrap;
        }

        /* --- Couleurs et Variantes --- */
        .btn-primary,
        .btn-add {
            background-color: #4f46e5;
            color: white !important;
            /* Force la couleur même si c'est un lien */
        }

        .btn-primary:hover,
        .btn-add:hover {
            background-color: #4338ca;
            transform: translateY(-1px);
        }

        /* Style spécifique pour le bouton Réinitialiser */
        .btn-reset {
            background-color: #5a6ebe;
            /* Gris très clair */
            color: #322f3c;
            /* Texte gris ardoise */
            border: 1px solid #e2e8f0;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s;
        }

        .btn-reset:hover {
            background-color: #4f1bdd;
            color: #ffffff;
        }

        .btn-edit {
            background-color: #fef3c7;
            color: #92400e;
            padding: 6px 12px;
        }

        .btn-edit:hover {
            background-color: #ff5e00;
            color: #fef3c7;
        }

        .btn-delete {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 6px 12px;
        }

        .btn-delete:hover {
            background-color: #ff0000;
            color: #fee2e2;
        }

        /* --- Tableau Moderne --- */
        table {
            text-align: center;
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        tbody tr:hover {
            background: #f1f5f9;
        }

        th {
            text-align: center;
            background-color: #f8fafc;
            color: var(--text-muted);
            padding: 14px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #e2e8f0;
        }

        td {
            text-align: center;
            padding: 16px 14px;
            border-bottom: 1px solid #f1f5f9;
        }

        td:last-child {
            display: flex;
            gap: 8px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .badge {
            background: #ecfdf5;
            color: #065f46;
            padding: 4px 10px;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .pages {
            color: var(--text-muted);
            font-style: italic;
        }

        /* --- Notifications Toast --- */
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--success);
            color: white;
            padding: 16px 24px;
            border-radius: 10px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes slideIn {
            from {
                transform: translateX(150%);
            }

            to {
                transform: translateX(0);
            }
        }
    </style>
</head>

<body>


    @if(session('success'))
    <div id="toast" class="toast-notification">
        <span>✅</span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="container">
        <form action="{{ route('findlivre') }}" method="POST" id="search-form">
            @csrf
            <input type="text" name="research" placeholder="Rechercher un livre..." id="search-input">

            <button class="btn-primary" type="submit">Rechercher</button>

            <a class="btn-reset" href="{{ route('alllivres') }}">Réinitialiser</a>

        </form>
    </div>
    <div class="conainer">
        <a href="{{route('logout')}}" class="btn-logout">Logout</a>
    </div>

    <div class="container">
        <div class="header">
            <h1>📚 Bibliothèque Digitale</h1>
            <a href="{{ route('formlivre') }}" class="btn-add">+ Ajouter un livre</a>
            <a href="{{ route('adddisc') }}" class="btn-add">+ Ajouter une discipline</a>

        </div>
    </div>

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
                <td><strong>{{ $livre->titre }}</strong></td>
                <td>{{ $livre->auteur }}</td>
                <td class="pages">{{ $livre->nb_pages }} p.</td>
                <td>{{ $livre->discipline->nom }}</td>
                <td><span class="badge">{{ $livre->prix }} €</span></td>
                @if($user == 'ad')
                <td>
                    <a href="{{ route('updateform', $livre->id) }}" class="btn btn-edit">✏️ Modifier</a>
                    <a href="{{ route('deletelivre', $livre->id) }}" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce livre {{ $livre->titre }} ?');">🗑️ Supprimer</a>
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #94a3b8;"><img width="20%" height="10%" src="{{asset('images/empty.png')}}" alt="Aucun résultat"><br>Aucun livre trouvé dans la base.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <script>
        // On attend que la page soit chargée
        setTimeout(function() {
            let message = document.getElementById('success-message');
            if (message) {
                // Option 1 : Disparition brutale
                // message.style.display = 'none';

                // Option 2 : Disparition en douceur (plus joli)
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 500); // On le retire du HTML après l'effet
            }
        }, 3000); // 3000 millisecondes = 3 secondes


        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.getElementById('toast');

            if (toast) {
                // Au bout de 4 secondes, on lance la sortie
                setTimeout(() => {
                    toast.classList.add('toast-fade-out');

                    // On le supprime du HTML après l'animation (0.5s)
                    setTimeout(() => toast.remove(), 500);
                }, 4000);
            }
        });
    </script>
</body>

</html>
