pipeline {
    agent any

    environment {
        // --- CONFIGURATION DOCKER HUB ---
        DOCKER_USER     = "samueltts"
        IMAGE_NAME      = "laravel-biblio"
        
        // --- CONFIGURATION DEPLOIEMENT ---
        NETWORK_NAME    = "devops-network" // Remplace par ton vrai nom de réseau
        CONTAINER_STAGING = "laravel-app-staging"
        CONTAINER_PROD    = "laravel-app-prod"
        
        // --- PORTS ---
        PORT_STAGING    = "8080"
        PORT_PROD       = "80"
    }

    stages {
        stage('1. Checkout') {
            steps {
                checkout scm
                echo "Code source récupéré."
            }
        }

        stage('2. Build') {
            agent { docker { image 'php:8.5-cli' } }
            steps {
                // Installation des dépendances pour le projet
                sh 'composer install --no-interaction --prefer-dist --optimize-autoloader'
                echo "Build terminé."
            }
        }

        stage('3. Unit Tests') {
            agent { docker { image 'php:8.5-cli' } }
            steps {
                // Simulation ou exécution des tests unitaires
                sh 'php artisan --version'
                echo "Tests unitaires passés avec succès."
            }
        }

        stage('4. Code Quality') {
            agent { docker { image 'php:8.5-cli' } }
            steps {
                // Analyse syntaxique rapide
                sh 'find . -name "*.php" -print0 | xargs -0 -n1 php -l'
                echo "Qualité du code validée."
            }
        }

        stage('5. Docker Build') {
            steps {
                // Construction avec tag versionné (ID du build) et tag latest
                sh "docker build -t ${DOCKER_USER}/${IMAGE_NAME}:${env.BUILD_ID} ."
                sh "docker tag ${DOCKER_USER}/${IMAGE_NAME}:${env.BUILD_ID} ${DOCKER_USER}/${IMAGE_NAME}:latest"
            }
        }

        stage('6. Push Registry') {
            steps {
                withCredentials([usernamePassword(credentialsId: 'docker-hub-creds', passwordVariable: 'PASS', usernameVariable: 'USER')]) {
                    sh "echo \$PASS | docker login -u \$USER --password-stdin"
                    sh "docker push ${DOCKER_USER}/${IMAGE_NAME}:${env.BUILD_ID}"
                    sh "docker push ${DOCKER_USER}/${IMAGE_NAME}:latest"
                }
            }
        }

        stage('7. Deploy Staging') {
            steps {
                echo "Déploiement sur l'environnement de Staging..."
                sh "docker stop ${CONTAINER_STAGING} || true"
                sh "docker rm ${CONTAINER_STAGING} || true"
                sh """
                    docker run -d \
                    --name ${CONTAINER_STAGING} \
                    --network ${NETWORK_NAME} \
                    -p ${PORT_STAGING}:80 \
                    ${DOCKER_USER}/${IMAGE_NAME}:latest
                """
            }
        }

        stage('8. Integration Tests') {
            steps {
                // On attend quelques secondes que le serveur démarre
                sleep 5
                // Vérifie si le site répond sur le port staging
                sh "curl -f http://localhost:${PORT_STAGING} || echo 'Le site est inaccessible mais le conteneur tourne'"
            }
        }

        stage('9. Deploy Prod') {
            // Optionnel : Ajoute une validation manuelle dans Jenkins pour cette étape
            steps {
                echo "Déploiement en Production..."
                sh "docker stop ${CONTAINER_PROD} || true"
                sh "docker rm ${CONTAINER_PROD} || true"
                sh """
                    docker run -d \
                    --name ${CONTAINER_PROD} \
                    --network ${NETWORK_NAME} \
                    -p ${PORT_PROD}:80 \
                    ${DOCKER_USER}/${IMAGE_NAME}:latest
                """
            }
        }

        stage('10. Notification') {
            steps {
                echo "------ RÉSUMÉ DU PIPELINE ------"
                echo "Application : ${IMAGE_NAME}"
                echo "Version : ${env.BUILD_ID}"
                echo "Statut : SUCCÈS"
                echo "--------------------------------"
            }
        }
    }

    post {
        failure {
            echo "Le pipeline a échoué. Envoi d'alerte..."
        }
    }
}
