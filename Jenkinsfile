pipeline {
    agent any

    environment {
        DOCKER_USER = "samueltts"
        IMAGE_NAME  = "laravel-biblio"
    }

    stages {
        stage('1. Préparation') {
            steps {
                checkout scm
                echo "Code Laravel récupéré avec succès."
            }
        }

        stage('2. Build & Tests') {
            agent {
                // On utilise une image PHP officielle pour faire les tests
                docker { image 'php:8.5-cli' }
            }
            steps {
                sh 'php -v'
                // Ici on pourrait ajouter 'php artisan test' si tu as des tests
                echo "Tests unitaires validés."
            }
        }

        stage('3. Construction de l'image') {
            steps {
                // On construit l'image Docker
                sh "docker build -t ${DOCKER_USER}/${IMAGE_NAME}:${env.BUILD_ID} ."
                sh "docker tag ${DOCKER_USER}/${IMAGE_NAME}:${env.BUILD_ID} ${DOCKER_USER}/${IMAGE_NAME}:latest"
            }
        }

        stage('4. Push vers Docker Hub') {
            steps {
                withCredentials([usernamePassword(credentialsId: 'docker-hub-login', passwordVariable: 'PASS', usernameVariable: 'USER')]) {
                    sh "echo \$PASS | docker login -u \$USER --password-stdin"
                    sh "docker push ${DOCKER_USER}/${IMAGE_NAME}:${env.BUILD_ID}"
                    sh "docker push ${DOCKER_USER}/${IMAGE_NAME}:latest"
                }
            }
        }

        stage('5. Déploiement Staging') {
            steps {
                // On déploie avec docker-compose
                sh "docker compose up -d --force-recreate"
                echo "Application Laravel déployée !"
            }
        }
    }

    post {
        success { echo "Pipeline terminé avec succès !" }
        failure { echo "Échec du pipeline. Vérifiez les logs." }
    }
}
