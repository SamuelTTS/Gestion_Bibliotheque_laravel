pipeline {
    agent any

    environment {
        DOCKER_USER = "samueltts"
        IMAGE_NAME  = "laravel-biblio"
        CONTAINER_NAME = "laravel_app" // Le nom de ton conteneur déjà créé
        NETWORK_NAME = "devops-network"     // Le nom de ton réseau Docker existant
    }

    stages {
        stage('1. Checkout') {
            steps {
                checkout scm
                echo"code recuperer avec succes"
            }
        }

        stage('2. Build Image') {
            steps {
                sh "docker build -t ${DOCKER_USER}/${IMAGE_NAME}:${env.BUILD_ID} ."
                sh "docker tag ${DOCKER_USER}/${IMAGE_NAME}:${env.BUILD_ID} ${DOCKER_USER}/${IMAGE_NAME}:latest"
            }
        }

        stage('3. Push to Docker Hub') {
            steps {
                withCredentials([usernamePassword(credentialsId: 'docker-hub-login', passwordVariable: 'PASS', usernameVariable: 'USER')]) {
                    sh "echo \$PASS | docker login -u \$USER --password-stdin"
                    sh "docker push ${DOCKER_USER}/${IMAGE_NAME}:${env.BUILD_ID}"
                    sh "docker push ${DOCKER_USER}/${IMAGE_NAME}:latest"
                }
            }
        }

        stage('4. Mise à jour du Conteneur') {
            steps {
                echo "Arrêt et suppression de l'ancien conteneur..."
                // On ignore l'erreur si le conteneur n'existe pas encore
                sh "docker stop ${CONTAINER_NAME} || true"
                sh "docker rm ${CONTAINER_NAME} || true"

                echo "Lancement du nouveau conteneur sur le réseau existant..."
                // On relance le conteneur avec les mêmes paramètres que tu as utilisé manuellement
                sh """
                    docker run -d \
                    --name ${CONTAINER_NAME} \
                    --network ${NETWORK_NAME} \
                    -p 8000:8000 \
                    ${DOCKER_USER}/${IMAGE_NAME}:latest
                """
            }
        }

        stage('5. Migrations') {
            steps {
                echo "Exécution des migrations de base de données..."
                // On force la migration à l'intérieur du nouveau conteneur
                sh "docker exec ${CONTAINER_NAME} php artisan migrate --force"
            }
        }
    }
}
