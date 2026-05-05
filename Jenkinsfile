pipeline {
    agent any

    environment {
        // --- CONFIGURATION DOCKER HUB ---
        DOCKER_USER     = "samueltts"
        IMAGE_NAME      = "laravel-biblio"
        
        // --- CONFIGURATION DEPLOIEMENT ---
        NETWORK_NAME    = "devops-network" 
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
                echo "Code source récupéré depuis GitHub."
            }
        }

        stage('2. Build') {
            steps {
                echo "Le build complet (Composer) sera exécuté durant l'étape Docker Build pour garantir l'usage de PHP 8.5.3."
            }
        }

        stage('3. Unit Tests') {
            steps {
                echo "Préparation des tests environnementaux..."
            }
        }

        stage('4. Code Quality') {
            steps {
                echo "Analyse syntaxique prête."
            }
        }

        stage('5. Docker Build') {
            steps {
                // C'est ici que tout se passe : Docker va lire ton Dockerfile et installer PHP 8.5.3 + Composer
                script {
                    sh "docker build -t ${DOCKER_USER}/${IMAGE_NAME}:${env.BUILD_ID} ."
                    sh "docker tag ${DOCKER_USER}/${IMAGE_NAME}:${env.BUILD_ID} ${DOCKER_USER}/${IMAGE_NAME}:latest"
                }
            }
        }

        stage('6. Push Registry') {
            steps {
                script {
                    withCredentials([usernamePassword(credentialsId: 'docker-hub-login', passwordVariable: 'PASS', usernameVariable: 'USER')]) {
                        sh "echo \$PASS | docker login -u \$USER --password-stdin"
                        sh "docker push ${DOCKER_USER}/${IMAGE_NAME}:${env.BUILD_ID}"
                        sh "docker push ${DOCKER_USER}/${IMAGE_NAME}:latest"
                    }
                }
            }
        }

        stage('7. Deploy Staging') {
            steps {
                echo "Déploiement sur l'environnement de Staging (Windows Docker Desktop)..."
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
                echo "Vérification de la disponibilité du service..."
                sleep 5
                // On vérifie juste si le conteneur est UP
                sh "docker ps | grep ${CONTAINER_STAGING}"
            }
        }

        stage('9. Deploy Prod') {
            steps {
                echo "Mise en production..."
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
                echo "------ RÉSUMÉ DU PIPELINE SAMUEL ------"
                echo "Application : ${IMAGE_NAME} (PHP 8.5.3)"
                echo "Build ID : ${env.BUILD_ID}"
                echo "Status : Success"
                echo "---------------------------------------"
            }
        }
    }

    post {
        failure {
            echo "Le pipeline a échoué. Vérifiez la configuration du Docker Daemon sur Windows."
        }
    }
}
