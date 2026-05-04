<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une discipline</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Formulaire */
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 400px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        input:focus {
            border-color: #007bff;
            outline: none;
        }

        /* Bouton */
        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>

</head>

<body>

    <div class="form-container">

        <form action="{{ route('storedisci') }}" method="POST">
            @csrf

            <h2>Ajouter une discipline</h2>

            <div class="form-group">
                <label for="nom">Nom de la discipline</label>
                <input type="text" name="nom" id="nom" placeholder="Ex: Informatique">
            </div>

            <div class="form-group">
                <label for="description">Description de la discipline</label>
                <input type="text" name="description" id="description" placeholder="Description...">
            </div>

            <button type="submit">Ajouter la discipline</button>

        </form>

    </div>

</body>

</html>
