<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel - Ajouter un Livre</title>
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --bg-color: #f8fafc;
            --text-color: #1e293b;
            --border-color: #d1d5db;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .card {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
        }

        h2 {
            margin-top: 0;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            text-align: center;
            color: #111827;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        /* Style unifié pour Input et Select */
        input,
        select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            box-sizing: border-box;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.2s;
            background-color: #fff;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        /* Boutons */
        .btn {
            display: block;
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
            /* Pour le lien */
            box-sizing: border-box;
        }

        .btn-submit {
            background-color: var(--primary-color);
            color: white;
            margin-bottom: 0.75rem;
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
        }

        .btn-back {
            background-color: transparent;
            color: #64748b;
            border: 1px solid var(--border-color);
        }

        .btn-back:hover {
            background-color: #f1f5f9;
            color: var(--text-color);
        }

        /* Toast Notification */
        .toast-notification {
            background-color: #dcfce7;
            color: #166534;
            padding: 1rem;
            border-radius: 0.5rem;
            border: 1px solid #bbf7d0;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.875rem;
        }

        .flex-row {
            display: flex;
            gap: 10px;
        }
    </style>
</head>

<body>

    <div class="card">
        <h2>Nouveau Livre</h2>

       <!-- @if(session('success'))
        <div id="toast" class="toast-notification">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
        @endif-->

        <form action="{{ route('storelivre') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="titre">Titre du livre</label>
                <input type="text" name="titre" id="titre" placeholder="ex: Germinal" required>
            </div>

            <div class="form-group">
                <label for="auteur">Nom de l'auteur</label>
                <input type="text" name="auteur" id="auteur" placeholder="ex: Émile Zola" required>
            </div>

            <div class="form-group">
                <label for="discipline">Discipline</label>
                <select name="discipline" id="discipline" required>
                    <option value="">-- Sélectionnez une discipline --</option>
                    @foreach($disciplines as $discipline)
                    <option value="{{ $discipline->id }}">{{ $discipline->nom }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex-row">
                <div class="form-group" style="flex: 1;">
                    <label for="prix">Prix (€)</label>
                    <input type="number" step="0.01" name="prix" id="prix" placeholder="0.00" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="nb_pages">Pages</label>
                    <input type="number" name="nb_pages" id="nb_pages" placeholder="ex: 450" required>
                </div>
            </div>

            <button type="submit" class="btn btn-submit">Enregistrer en base de données</button>
            <a href="{{ route('alllivres') }}" class="btn btn-back">Retour</a>
        </form>
    </div>

</body>

</html>
