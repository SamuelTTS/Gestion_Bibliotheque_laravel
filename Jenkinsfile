pipeline {
    agent any

    environment {
        DOCKER_USER = "samueltts"
        IMAGE_NAME  = "laravel-biblio"
    }

    stages {
        stage('1. Checkout') {
            steps {
                checkout scm
            }
        }

        stage('2. Build & Static Analysis') {
            agent {
                // On utilise exactement la version 8.5 pour les tests
                docker { image 'php:8.5-cli' }
            }
            steps {
                sh 'php -v'
                // Vérification de la syntaxe du code
                sh 'find . -name "*.php" -print0 | xargs -0 -n1 php -l'
                echo "Analyse syntaxique terminée avec succès."
            }
        }

        stage('3. Dockerize') {
            steps {
                // Construction de l'image avec le Dockerfile ci-dessus
                sh "docker build -t ${DOCKER_USER}/${IMAGE_NAME}:${env.BUILD_ID} ."
                sh "docker tag ${DOCKER_USER}/${IMAGE_NAME}:${env.BUILD_ID} ${DOCKER_USER}/${IMAGE_NAME}:latest"
            }
        }

        stage('4. Push Registry') {
            steps {
                withCredentials([usernamePassword(credentialsId: 'docker-hub-login', passwordVariable: 'PASS', usernameVariable: 'USER')]) {
                    sh "echo \$PASS | docker login -u \$USER --password-stdin"
                    sh "docker push ${DOCKER_USER}/${IMAGE_NAME}:${env.BUILD_ID}"
                    sh "docker push ${DOCKER_USER}/${IMAGE_NAME}:latest"
                }
            }
        }

        stage('5. Deploy') {
            steps {
                // Déploiement via Docker Compose
                sh "docker compose up -d --force-recreate"
            }
        }
    }
}
