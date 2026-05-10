pipeline {
    agent any

    triggers {
        githubPush()
    }

    environment {
        REGISTRY        = "127.0.0.1:5000"
        IMAGE_NAME      = "${REGISTRY}/laravel-app"
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
        timeout(time: 15, unit: 'MINUTES')
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
                sh "docker run --rm -v ${WORKSPACE}:/app -w /app php:8.5-cli-alpine find . -name '*.php' -exec php -l {} \\;"
            }
        }

        stage('Docker Build') {
            steps {
                script {
                    echo "------- Construction de l'image de STAGING (avec dev dependencies) -------"
                    sh "docker build --target builder -t ${IMAGE_NAME}:staging ."
                    
                    echo "------- Construction de l'image de PRODUCTION (optimisée) -------"
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
                        --entrypoint /bin/sh \
                        --network ${DOCKER_NETWORK} \
                        -w /var/www \
                        -p ${STAGING_PORT}:9000 \
                        -e APP_ENV=staging \
                        -e APP_KEY=${APP_KEY} \
                        -e DB_CONNECTION=mysql \
                        -e DB_HOST=mysql \
                        -e DB_DATABASE=laravel_staging \
                        -e DB_USERNAME=${DB_USERNAME} \
                        -e DB_PASSWORD=${DB_PASSWORD} \
                        --restart unless-stopped \
                        ${IMAGE_NAME}:staging \
                        -c "while true; do sleep 3600; done"
                """
                //sh -c "rm -f bootstrap/cache/*.php && php-fpm"
            }
        }

        stage('Integration Tests') {
            steps {
                script {
                    echo "------- Le conteneur est stable, on répare -------"
                    // Suppression du cache qui cause le crash
                    sh "docker exec laravel-staging rm -rf /var/www/bootstrap/cache/config.php /var/www/bootstrap/cache/services.php /var/www/bootstrap/cache/packages.php"
                    
                    // On vérifie où on est et ce qu'on a
                    sh "docker exec laravel-staging ls -la /var/www"
                    
                    try {
                        echo "------- Tentative de commande Artisan -------"
                        // On force la génération d'un nouveau cache propre
                        sh "docker exec laravel-staging php artisan config:cache"
                        sh "docker exec laravel-staging php artisan migrate --force"
                        
                        echo "------- Tests -------"
                        sh "docker exec laravel-staging php vendor/bin/phpunit"
                        
                        echo "✅ Enfin réussi !"
                    } catch (Exception e) {
                        echo "------- Logs du conteneur en cas d'erreur -------"
                        sh "docker logs laravel-staging"
                        error "❌ Les tests d'intégration ont échoué"
                    }
                }
            }
        }

        stage('Deploy Production') {
            when { branch 'main' }
            steps {
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
                        -e DB_DATABASE=biblio \
                        -e DB_USERNAME=${DB_USERNAME} \
                        -e DB_PASSWORD=${DB_PASSWORD} \
                        --restart unless-stopped \
                        ${IMAGE_NAME}:${IMAGE_TAG}
                """
                echo "🚀 Déploiement en production terminé sur le port ${PROD_PORT}"
            }
        }
    }

    post {
        always {
            sh 'docker image prune -f'
        }
    }
}
