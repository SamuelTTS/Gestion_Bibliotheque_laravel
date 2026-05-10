pipeline {
    agent any

    triggers {
        githubPush()
    }

    environment {
        REGISTRY        = "127.0.0.1:5000"
        IMAGE_NAME      = "${REGISTRY}/laravel-app"
        
        // On s'assure que GIT_COMMIT existe (fallback sur BUILD_NUMBER si vide)
        IMAGE_TAG       = "${env.GIT_COMMIT ? env.GIT_COMMIT.take(7) : env.BUILD_NUMBER}"
        DOCKER_NETWORK  = "devops-network"
        STAGING_PORT    = "8081"
        PROD_PORT       = "8000"
        
        // DB Config
        DB_USERNAME     = "root"
        DB_PASSWORD     = "root"
        APP_KEY         = "base64:uP8SjVf7R6v7Z9S6K8J3W4L5M6N7P8Q9R0T1U2V3W4X=" 
    }

    options {
        timeout(time: 15, unit: 'MINUTES') // Augmenté un peu car PHP est lent à compiler
        buildDiscarder(logRotator(numToKeepStr: '10'))
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
                // On utilise l'image CLI pour scanner le code
                sh "docker run --rm -v ${WORKSPACE}:/app -w /app php:8.5-cli-alpine find . -name '*.php' -exec php -l {} \\;"
            }
        }

        stage('Docker Build') {
            steps {
                script {
                    // Suppression du --no-cache pour gagner du temps au quotidien
                    sh """
                        docker build \
                            --target production \
                            --build-arg BUILD_DATE=\$(date -u +"%Y-%m-%dT%H:%M:%SZ") \
                            -t ${IMAGE_NAME}:${IMAGE_TAG} \
                            -t ${IMAGE_NAME}:latest \
                            .
                    """
                }
            }
        }

        stage('Push Registry') {
            steps {
                sh """
                    docker push ${IMAGE_NAME}:${IMAGE_TAG}
                    docker push ${IMAGE_NAME}:latest
                """
            }
        }

        stage('Deploy Staging') {
            steps {
                sh """
                    docker rm -f laravel-staging 2>/dev/null || true
                    docker run -d \
                        --name laravel-staging \
                        --network ${DOCKER_NETWORK} \
                        -p ${STAGING_PORT}:9000 \
                        -e APP_ENV=staging \
                        -e APP_KEY=${APP_KEY} \
                        -e DB_CONNECTION=mysql \
                        -e DB_HOST=mysql \
                        -e DB_DATABASE=laravel_staging \
                        -e DB_USERNAME=${DB_USERNAME} \
                        -e DB_PASSWORD=${DB_PASSWORD} \
                        --restart unless-stopped \
                        ${IMAGE_NAME}:${IMAGE_TAG}
                """
            }
        }

        stage('Integration Tests') {
            steps {
                script {
                    echo "⏳ Attente du démarrage des services..."
                    sleep 10
                    try {
                        // Test de connexion DB + Version
                        sh "docker exec laravel-staging php artisan migrate:status"
                        sh "docker exec laravel-staging php artisan --version"
                        echo "✅ Staging est opérationnel"
                    } catch (Exception e) {
                        sh "docker logs laravel-staging"
                        error "❌ Les tests d'intégration ont échoué"
                    }
                }
            }
        }

        stage('Deploy Production') {
            when { branch 'main' }
            steps {
                // L'input bloque le pipeline jusqu'à validation manuelle
                input message: "🚀 Déployer en PRODUCTION ?", ok: "Confirmer"
                
                sh """
                    docker rm -f laravel-prod 2>/dev/null || true
                    docker run -d \
                        --name laravel-prod \
                        --network ${DOCKER_NETWORK} \
                        -p ${PROD_PORT}:9000 \
                        -e APP_ENV=production \
                        -e APP_DEBUG=false \
                        -e APP_KEY=${APP_KEY} \
                        -e DB_CONNECTION=mysql \
                        -e DB_HOST=mysql \
                        -e DB_DATABASE=laravel-prod \
                        -e DB_USERNAME=${DB_USERNAME} \
                        -e DB_PASSWORD=${DB_PASSWORD} \
                        --restart unless-stopped \
                        ${IMAGE_NAME}:${IMAGE_TAG}
                """
            }
        }
    }

    post {
        always {
            // Nettoyage des images de build pour éviter de saturer le disque de l'agent
            sh 'docker image prune -f'
        }
    }
}
