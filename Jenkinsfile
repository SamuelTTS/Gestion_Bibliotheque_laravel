pipeline {
    agent any

    triggers {
        githubPush()
    }

    environment {
        EMAILS          = "stchablintete@gmail.com,ahmedbintihoudjrat@gmail.com,kamanta7605@gmail.com"
        REGISTRY        = "127.0.0.1:5000"
        IMAGE_NAME      = "${REGISTRY}/laravel-app"
        IMAGE_TAG       = "${env.GIT_COMMIT ? env.GIT_COMMIT.take(7) : env.BUILD_NUMBER}"
        DOCKER_NETWORK  = "devops-network"
        MAINTAINER_EMAILS = "stchablintete@gmail.com,ahmedbintihoudjrat@gmail.com,kamanta7605@gmail.com"
        STAGING_PORT    = "8081"
        PROD_PORT       = "8000"
        
        // DB Config
        DB_USERNAME     = "root"
        DB_PASSWORD     = "root"
        APP_KEY         = "base64:uP8SjVf7R6v7Z9S6K8J3W4L5M6N7P8Q9R0T1U2V3W4X=" 
    }

    options {
        timeout(time: 30, unit: 'MINUTES')
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
                    sh "docker build --target staging -t ${IMAGE_NAME}:staging ."
                    
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
                    # Nettoyage des anciens conteneurs de Staging
                    docker rm -f nginx-staging laravel-staging 2>/dev/null || true
                    
                    # 1. Lancement du conteneur applicatif (PHP-FPM)
                    docker run -d \
                        --name laravel-staging \
                        --network ${DOCKER_NETWORK} \
                        -w /var/www/html \
                        -e APP_ENV=staging \
                        -e APP_KEY=${APP_KEY} \
                        -e DB_CONNECTION=mysql \
                        -e DB_HOST=mysql \
                        -e DB_DATABASE=laravel_staging \
                        -e DB_USERNAME=${DB_USERNAME} \
                        -e DB_PASSWORD=${DB_PASSWORD} \
                        --restart unless-stopped \
                        ${IMAGE_NAME}:staging
                    
                    # 2. Préparation de la configuration Nginx pour le Staging
                    # On adapte dynamiquement le default.conf pour pointer vers laravel-staging au lieu de laravel-prod
                    mkdir -p ${WORKSPACE}/nginx_staging
                    sed 's/laravel-prod/laravel-staging/g' ${WORKSPACE}/nginx/default.conf > ${WORKSPACE}/nginx_staging/default.conf
                    
                    # 3. Lancement du serveur Web Nginx pour le Staging
                    echo "🌐 Lancement Nginx Staging..."
                    docker run -d \
                        --name nginx-staging \
                        --network ${DOCKER_NETWORK} \
                        -p ${STAGING_PORT}:80 \
                        nginx:alpine
                        
                    echo "📥 Copie de la config Nginx Staging..."
                    docker cp ${WORKSPACE}/nginx_staging/default.conf nginx-staging:/etc/nginx/conf.d/default.conf
                    
                    echo "🔄 Redémarrage de Nginx Staging..."
                    docker restart nginx-staging
                """
            }
        }

        stage('Integration Tests') {
            steps {
                script {
                   echo "⏳ Attente de démarrage des services (PHP-FPM & Nginx)..."
                    sleep 10
                    
                    try {
                        echo "------- Configuration de l'application -------"
                        sh "docker exec -e PROMETHEUS_STORAGE_DRIVER=memory laravel-staging php artisan config:clear"
                        sh "docker exec -e PROMETHEUS_STORAGE_DRIVER=memory laravel-staging php artisan migrate --force"
                        
                        echo "------- Exécution des Tests PHPUnit (Backend) -------"
                        sh "docker exec -e PROMETHEUS_ENABLE=false -e PROMETHEUS_STORAGE_DRIVER=memory laravel-staging php vendor/bin/phpunit"
                        
                        echo "------- Exécution des Tests d'Intégration Postman (Newman) -------"
                        
                        
                        echo "------- Exécution des Tests d'Intégration Postman (Newman) -------"
                        sh """
                           
                            docker rm -f newman-test || true
                            
                           
                            docker create --name newman-test \
                                --network ${DOCKER_NETWORK} \
                                postman/newman run /collection_staging.json \
                                --env-var "base_url=http://nginx-staging" \
                                --reporters cli
                            
                           
                            docker cp ${WORKSPACE}/tests/collection_staging.json newman-test:/collection_staging.json
                            
                          
                            docker start -a newman-test
                        """
                        
                        echo "✅ Tous les tests (PHPUnit + Postman) sont réussis !"
                    } catch (Exception e) {
                        sh "docker rm -f newman-test || true"
                        echo "------- Logs des conteneurs en cas d'erreur -------"
                        sh "docker logs laravel-staging"
                        sh "docker logs nginx-staging"
                        error "❌ Les tests d'intégration ont échoué"
                    }
                }
            }
        }

        stage('Deploy Production') {
            steps {
                echo "------- Attente de confirmation ---------"
                input message: "🚀 Déployer en PRODUCTION ?", ok: "Confirmer"
                
                sh """
                    docker start redis-prod || docker run -d --name redis-prod -p 6379:6379 redis:alpine
                    
                    docker rm -f nginx-prod 2>/dev/null || true
                    docker rm -f laravel-prod 2>/dev/null || true
                    
                    docker run -d \
                        --name laravel-prod \
                        --network ${DOCKER_NETWORK} \
                        -w /var/www/html \
                        -e PROMETHEUS_STORAGE_DRIVER=redis \
                        -e REDIS_HOST=host.docker.internal \
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

                    echo "🌐 Lancement Nginx..."
                    docker run -d \
                      --name nginx-prod \
                      --network ${DOCKER_NETWORK} \
                      -p ${PROD_PORT}:80 \
                      nginx:alpine
                    
                    echo "📥 Copie de la config Nginx..."
                    docker cp ${WORKSPACE}/nginx/default.conf nginx-prod:/etc/nginx/conf.d/default.conf
                    
                    echo "🔄 Restart Nginx..."
                    docker restart nginx-prod
                    
                    echo "✅ Déploiement terminé !"
                """
            }
        }
    }

    post {
        success {
            script {
                // Extraction des vraies données du commit Git
                def commitAuthor = sh(script: "git log -1 --pretty=format:'%an'", returnStdout: true).trim()
                def commitMessage = sh(script: "git log -1 --pretty=format:'%s'", returnStdout: true).trim()
                def buildDate = sh(script: "date '+%Y-%m-%d %H:%M:%S'", returnStdout: true).trim()

                emailext(
                    to: "${EMAILS}",
                    subject: "✅ SUCCESS - ${env.JOB_NAME} #${env.BUILD_NUMBER}",
                    mimeType: 'text/html',
                    body: """
                    <!DOCTYPE html>
                    <html>
                    <body style="font-family: Arial, sans-serif; background-color:#f4f4f4; padding:20px; color: #333;">
                    <div style="max-width:600px; margin:auto; background:white; padding:20px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1); border-top: 5px solid #28a745;">
                        
                        <h2 style="color:#28a745; margin-top:0;">✅ Pipeline Réussi avec Succès !</h2>
                        <p>Bonjour l'équipe, le build et le déploiement se sont déroulés sans accroc.</p>
                        
                        <hr style="border:0; border-top:1px solid #eee; margin:20px 0;">

                        <table style="width:100%; border-collapse: collapse; line-height: 2a;">
                            <tr><td style="width:40%;"><b>📦 Projet :</b></td><td>${env.JOB_NAME}</td></tr>
                            <tr><td><b>🔢 Numéro de Build :</b></td><td>#${env.BUILD_NUMBER}</td></tr>
                            <tr><td><b>👤 Développeur :</b></td><td style="color:#007bff; font-weight:bold;">${commitAuthor}</td></tr>
                            <tr><td><b>💬 Message de Commit :</b></td><td><i>"${commitMessage}"</i></td></tr>
                            <tr><td><b>🔖 SHA Commit :</b></td><td><code>${env.GIT_COMMIT?.take(7)}</code></td></tr>
                            <tr><td><b>🕒 Date du Build :</b></td><td>${buildDate}</td></tr>
                        </table>

                        <hr style="border:0; border-top:1px solid #eee; margin:20px 0;">
                        
                        <h4 style="margin-bottom:10px;">🌍 Environnements disponibles :</h4>
                        <ul style="padding-left:20px; margin-top:0;">
                            <li><a href="http://localhost:8081" target="_blank" style="color:#007bff; text-decoration:none; font-weight:bold;">💻 Accéder au Staging (Port 8081)</a></li>
                            <li><a href="http://localhost:8000" target="_blank" style="color:#28a745; text-decoration:none; font-weight:bold;">🚀 Accéder à la Production (Port 8000)</a></li>
                        </ul>

                        <br>
                        <div style="text-align:center;">
                            <a href="${env.BUILD_URL}" style="background:#28a745; color:white; padding:12px 24px; text-decoration:none; border-radius:5px; font-weight:bold; display:inline-block;">
                                🔎 Voir les détails sur Jenkins
                            </a>
                        </div>
                    </div>
                    </body>
                    </html>
                    """
                )
            }
        }
        
        failure {
            script {
                def commitAuthor = sh(script: "git log -1 --pretty=format:'%an'", returnStdout: true).trim()
                def commitMessage = sh(script: "git log -1 --pretty=format:'%s'", returnStdout: true).trim()
                def buildDate = sh(script: "date '+%Y-%m-%d %H:%M:%S'", returnStdout: true).trim()

                emailext(
                    to: "${EMAILS}",
                    subject: "❌ FAILURE - ${env.JOB_NAME} #${env.BUILD_NUMBER}",
                    mimeType: 'text/html',
                    body: """
                    <!DOCTYPE html>
                    <html>
                    <body style="font-family: Arial, sans-serif; background-color:#f4f4f4; padding:20px; color: #333;">
                    <div style="max-width:600px; margin:auto; background:white; padding:20px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1); border-top: 5px solid #dc3545;">
                        
                        <h2 style="color:#dc3545; margin-top:0;">❌ Échec de la Pipeline</h2>
                        <p style="color:#dc3545; font-weight:bold;">⚠️ Attention, une erreur a stoppé l'exécution de la pipeline.</p>
                        
                        <hr style="border:0; border-top:1px solid #eee; margin:20px 0;">

                        <table style="width:100%; border-collapse: collapse; line-height: 2a;">
                            <tr><td style="width:40%;"><b>📦 Projet :</b></td><td>${env.JOB_NAME}</td></tr>
                            <tr><td><b>🔢 Numéro de Build :</b></td><td>#${env.BUILD_NUMBER}</td></tr>
                            <tr><td><b>👤 Dernier Auteur :</b></td><td>${commitAuthor}</td></tr>
                            <tr><td><b>💬 Dernier Commit :</b></td><td><i>"${commitMessage}"</i></td></tr>
                            <tr><td><b>🕒 Date du Crash :</b></td><td>${buildDate}</td></tr>
                        </table>

                        <p style="margin-top:20px; background:#fff3cd; border-left:4px solid #ffc107; padding:10px;">
                            <b>Action requise :</b> Le déploiement a été annulé ou l'application a échoué aux tests automatiques (PHPUnit / PHP Lint).
                        </p>

                        <br>
                        <div style="text-align:center;">
                            <a href="${env.BUILD_URL}console" style="background:#dc3545; color:white; padding:12px 24px; text-decoration:none; border-radius:5px; font-weight:bold; display:inline-block;">
                                🛠️ Analyser les Logs d'Erreur
                            </a>
                        </div>
                    </div>
                    </body>
                    </html>
                    """
                )
            }
        }
        
        always {
            echo "📌 Nettoyage des anciennes images Docker suspendues..."
            sh 'docker image prune -f || true'
        }
    }
}
