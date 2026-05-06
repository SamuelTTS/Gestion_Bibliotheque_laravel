pipeline {
    agent any

    triggers {
        githubPush()
    }

    environment {
        REGISTRY         = "registry:5000"
        IMAGE_NAME       = "${REGISTRY}/laravel-app"
        // Correction : Gestion plus sûre du tag de commit
        IMAGE_TAG        = "${env.GIT_COMMIT.take(7)}"
        DOCKER_NETWORK   = "devops-network"
        STAGING_PORT     = "8081"
        PROD_PORT        = "8000"
        NOTIFY_EMAIL     = "stchablintete@gmail.com"

        // DB Staging
        DB_HOST          = "mysql-staging"
        DB_DATABASE      = "laravel_staging"
        DB_USERNAME      = "laravel"
        DB_PASSWORD      = "root"
        
        // Correction : Clé d'application pour éviter l'erreur 500
        APP_KEY          = "base64:uP8SjVf7R6v7Z9S6K8J3W4L5M6N7P8Q9R0T1U2V3W4X=" 
    }

    options {
        timeout(time: 10, unit: 'MINUTES')
        buildDiscarder(logRotator(numToKeepStr: '90'))
        disableConcurrentBuilds()
        timestamps()
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Code Quality — PHP Lint') {
            steps {
                // Correction : Utilisation de ${WORKSPACE} pour la portabilité
                sh "docker run --rm -v ${WORKSPACE}:/app -w /app php:8.2-fpm-alpine find . -name '*.php' -exec php -l {} \\;"
            }
        }

        stage('Docker Build') {
            steps {
                sh '''
                    docker build \
                        --no-cache \
                        --target production \
                        --build-arg BUILD_DATE=$(date -u +"%Y-%m-%dT%H:%M:%SZ") \
                        -t ${IMAGE_NAME}:${IMAGE_TAG} \
                        -t ${IMAGE_NAME}:latest \
                        .
                '''
            }
        }

        stage('Push Registry') {
            steps {
                sh '''
                    docker push ${IMAGE_NAME}:${IMAGE_TAG}
                    docker push ${IMAGE_NAME}:latest
                '''
            }
        }

        stage('Deploy Staging') {
            steps {
                sh '''
                    # Suppression propre des anciens conteneurs
                    docker rm -f laravel-staging 2>/dev/null || true

                    docker run -d \
                        --name laravel-staging \
                        --network ${DOCKER_NETWORK} \
                        -p ${STAGING_PORT}:9000 \
                        -e APP_ENV=staging \
                        -e APP_DEBUG=true \
                        -e APP_KEY=${APP_KEY} \
                        -e DB_CONNECTION=mysql \
                        -e DB_HOST=${DB_HOST} \
                        -e DB_DATABASE=${DB_DATABASE} \
                        -e DB_USERNAME=${DB_USERNAME} \
                        -e DB_PASSWORD=${DB_PASSWORD} \
                        --restart unless-stopped \
                        ${IMAGE_NAME}:${IMAGE_TAG}
                '''
            }
        }

        stage('Integration Tests') {
            steps {
                script {
                    // Correction : Attendre que Laravel soit prêt avant de tester
                    sh 'sleep 10'
                    try {
                        sh "docker exec laravel-staging php artisan --version"
                        echo "✅ Laravel est opérationnel"
                    } catch (Exception e) {
                        sh "docker logs laravel-staging"
                        error "❌ Laravel n'a pas démarré correctement"
                    }
                }
            }
        }

        stage('Deploy Production') {
            when { branch 'main' }
            input {
                message "🚀 Déployer en PRODUCTION ?"
                ok "✅ Confirmer"
                submitter "admin"
            }
            steps {
                sh '''
                    docker rm -f laravel-prod 2>/dev/null || true
                    docker run -d \
                        --name laravel-prod \
                        --network ${DOCKER_NETWORK} \
                        -p ${PROD_PORT}:9000 \
                        -e APP_ENV=production \
                        -e APP_DEBUG=false \
                        -e APP_KEY=${APP_KEY} \
                        -e DB_CONNECTION=mysql \
                        -e DB_HOST=mysql-prod \
                        -e DB_DATABASE=laravel_prod \
                        -e DB_USERNAME=${DB_USERNAME} \
                        -e DB_PASSWORD=${DB_PASSWORD} \
                        --restart unless-stopped \
                        ${IMAGE_NAME}:${IMAGE_TAG}
                '''
            }
        }
    }

    post {
        always {
            // Correction : Nettoyage uniquement des images inutilisées ("dangling")
            sh 'docker image prune -f'
        }
        success {
            mail to: "${NOTIFY_EMAIL}",
                 subject: "✅ Build Réussi: ${env.JOB_NAME} #${env.BUILD_NUMBER}",
                 body: "Le déploiement de ${IMAGE_NAME}:${IMAGE_TAG} est terminé."
        }
        failure {
            mail to: "${NOTIFY_EMAIL}",
                 subject: "❌ Build Échoué: ${env.JOB_NAME} #${env.BUILD_NUMBER}",
                 body: "Vérifiez les logs ici : ${env.BUILD_URL}"
        }
    }
}
