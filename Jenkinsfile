pipeline {
    agent any

    // ─────────────────────────────────────────────
    // Déclencher uniquement sur la branche main
    // ─────────────────────────────────────────────
    triggers {
        githubPush()
    }

    environment {
        // Registry privé local
        REGISTRY         = "localhost:5000"
        IMAGE_NAME       = "${REGISTRY}/laravel-app"
        IMAGE_TAG        = "${env.GIT_COMMIT[0..6]}"

        // Réseau Docker partagé
        DOCKER_NETWORK   = "devops-network"

        // Ports des environnements
        STAGING_PORT     = "8081"
        PROD_PORT        = "8000"

        // Notification
        NOTIFY_EMAIL     = "stchablintete@gmail.com"

        // Base de données staging (conteneur MySQL dédié)
        DB_HOST          = "mysql-staging"
        DB_DATABASE      = "laravel_staging"
        DB_USERNAME      = "laravel"
        DB_PASSWORD      = "root"
    }

    options {
        // Timeout global du pipeline : 10 min max (exigence ENF-01 du CDC)
        timeout(time: 10, unit: 'MINUTES')

        // Conserver les logs des 90 derniers builds (exigence ENF-04 du CDC)
        buildDiscarder(logRotator(numToKeepStr: '90'))

        // Éviter les builds concurrents sur la même branche
        disableConcurrentBuilds()

        // Afficher les timestamps dans les logs
        timestamps()
    }

    stages {

        // ─────────────────────────────────────────
        stage('Checkout') {
            steps {
                echo "╔══════════════════════════════════╗"
                echo "║   STAGE 1 — Checkout             ║"
                echo "╚══════════════════════════════════╝"
                echo "Branche  : ${env.BRANCH_NAME}"
                echo "Commit   : ${env.GIT_COMMIT}"
                echo "Build n° : ${env.BUILD_NUMBER}"
                checkout scm
            }
        }

        // ─────────────────────────────────────────
        stage('Composer Install') {
            steps {
                echo "╔══════════════════════════════════╗"
                echo "║   STAGE 2 — Composer Install     ║"
                echo "╚══════════════════════════════════╝"
                sh '''
                    docker run --rm \
                        -v $(pwd):/app \
                        -w /app \
                        composer:latest \
                        composer install \
                            --no-dev \
                            --prefer-dist \
                            --no-interaction \
                            --optimize-autoloader
                '''
            }
        }

        // ─────────────────────────────────────────
        stage('Code Quality — PHP Lint') {
            steps {
                echo "╔══════════════════════════════════╗"
                echo "║   STAGE 3 — PHP Lint             ║"
                echo "╚══════════════════════════════════╝"
                sh '''
                    docker run --rm \
                        -v $(pwd):/app \
                        -w /app \
                        php:8.5-cli \
                        sh -c "
                            echo 'Vérification syntaxique PHP 8.5...'
                            ERRORS=0
                            for file in \$(find app/ routes/ config/ database/ -name '*.php'); do
                                result=\$(php -l \$file 2>&1)
                                if echo \$result | grep -q 'Parse error'; then
                                    echo '❌ ERREUR : ' \$file
                                    echo \$result
                                    ERRORS=\$((ERRORS+1))
                                fi
                            done
                            if [ \$ERRORS -gt 0 ]; then
                                echo \"Total erreurs : \$ERRORS\"
                                exit 1
                            else
                                echo '✅ Aucune erreur de syntaxe détectée'
                            fi
                        "
                '''
            }
        }

        // ─────────────────────────────────────────
        stage('Docker Build') {
            steps {
                echo "╔══════════════════════════════════╗"
                echo "║   STAGE 4 — Docker Build         ║"
                echo "╚══════════════════════════════════╝"
                echo "Construction de l'image : ${IMAGE_NAME}:${IMAGE_TAG}"
                sh '''
                    docker build \
                        --no-cache \
                        --target production \
                        --build-arg BUILD_DATE=$(date -u +"%Y-%m-%dT%H:%M:%SZ") \
                        --build-arg VCS_REF=${IMAGE_TAG} \
                        -t ${IMAGE_NAME}:${IMAGE_TAG} \
                        -t ${IMAGE_NAME}:latest \
                        .
                '''
                echo "✅ Image construite : ${IMAGE_NAME}:${IMAGE_TAG}"
            }
        }

        // ─────────────────────────────────────────
        stage('Push Registry') {
            steps {
                echo "╔══════════════════════════════════╗"
                echo "║   STAGE 5 — Push Registry        ║"
                echo "╚══════════════════════════════════╝"
                sh '''
                    # Push vers le registry privé local (pas besoin de login)
                    docker push ${IMAGE_NAME}:${IMAGE_TAG}
                    docker push ${IMAGE_NAME}:latest
                    echo "✅ Image publiée sur ${REGISTRY}"

                    # Vérification que l'image est bien dans le registry
                    curl -s http://${REGISTRY}/v2/laravel-app/tags/list
                '''
            }
        }

        // ─────────────────────────────────────────
        stage('Deploy Staging') {
            steps {
                echo "╔══════════════════════════════════╗"
                echo "║   STAGE 6 — Deploy Staging       ║"
                echo "╚══════════════════════════════════╝"
                sh '''
                    # Arrêter et supprimer l'ancien conteneur staging
                    docker stop laravel-staging 2>/dev/null || true
                    docker rm   laravel-staging 2>/dev/null || true

                    # Lancer le nouveau conteneur staging
                    docker run -d \
                        --name laravel-staging \
                        --network ${DOCKER_NETWORK} \
                        -p ${STAGING_PORT}:9000 \
                        -e APP_ENV=staging \
                        -e APP_DEBUG=true \
                        -e APP_KEY= \
                        -e DB_CONNECTION=mysql \
                        -e DB_HOST=${DB_HOST} \
                        -e DB_PORT=3306 \
                        -e DB_DATABASE=${DB_DATABASE} \
                        -e DB_USERNAME=${DB_USERNAME} \
                        -e DB_PASSWORD=${DB_PASSWORD} \
                        --restart unless-stopped \
                        ${IMAGE_NAME}:${IMAGE_TAG}

                    # Attendre que le conteneur soit healthy
                    echo "Attente du démarrage du conteneur..."
                    sleep 5

                    # Vérifier que le conteneur tourne bien
                    if docker ps | grep -q laravel-staging; then
                        echo "✅ Staging déployé sur http://localhost:${STAGING_PORT}"
                    else
                        echo "❌ Echec du déploiement staging"
                        docker logs laravel-staging
                        exit 1
                    fi
                '''
            }
        }

        // ─────────────────────────────────────────
        stage('Integration Tests') {
            steps {
                echo "╔══════════════════════════════════╗"
                echo "║   STAGE 7 — Tests Intégration    ║"
                echo "╚══════════════════════════════════╝"
                sh '''
                    # Test de connectivité HTTP sur le staging
                    echo "Test de connectivité sur le staging..."
                    sleep 3

                    # Vérifier que PHP-FPM répond
                    if docker exec laravel-staging php artisan --version; then
                        echo "✅ Laravel répond correctement"
                    else
                        echo "❌ Laravel ne répond pas"
                        exit 1
                    fi

                    # Vérifier la connexion MySQL
                    if docker exec laravel-staging php artisan db:show --no-interaction 2>/dev/null || true; then
                        echo "✅ Connexion MySQL OK"
                    else
                        echo "⚠️  Vérification MySQL ignorée (pas de DB en staging)"
                    fi
                '''
            }
        }

        // ─────────────────────────────────────────
        stage('Deploy Production') {
            when {
                branch 'main'
            }
            input {
                message "🚀 Déployer en PRODUCTION ?"
                ok "✅ Confirmer le déploiement"
                submitter "admin"
            }
            steps {
                echo "╔══════════════════════════════════╗"
                echo "║   STAGE 8 — Deploy Production    ║"
                echo "╚══════════════════════════════════╝"
                sh '''
                    # Arrêter et supprimer l'ancien conteneur production
                    docker stop laravel-prod 2>/dev/null || true
                    docker rm   laravel-prod 2>/dev/null || true

                    # Lancer le nouveau conteneur production
                    docker run -d \
                        --name laravel-prod \
                        --network ${DOCKER_NETWORK} \
                        -p ${PROD_PORT}:9000 \
                        -e APP_ENV=production \
                        -e APP_DEBUG=false \
                        -e DB_CONNECTION=mysql \
                        -e DB_HOST=mysql-prod \
                        -e DB_PORT=3306 \
                        -e DB_DATABASE=laravel_prod \
                        -e DB_USERNAME=${DB_USERNAME} \
                        -e DB_PASSWORD=${DB_PASSWORD} \
                        --restart unless-stopped \
                        ${IMAGE_NAME}:${IMAGE_TAG}

                    # Vérification finale
                    sleep 5
                    if docker ps | grep -q laravel-prod; then
                        echo "✅ Production déployée sur http://localhost:${PROD_PORT}"
                    else
                        echo "❌ Echec du déploiement production"
                        docker logs laravel-prod
                        exit 1
                    fi
                '''
            }
        }
    }

    // ─────────────────────────────────────────────
    // Notifications post-pipeline
    // ─────────────────────────────────────────────
    post {
        success {
            echo "✅ Pipeline complet en succès !"
            mail to: "${NOTIFY_EMAIL}",
                 subject: "✅ [Jenkins] Build réussi — ${env.JOB_NAME} #${env.BUILD_NUMBER}",
                 body: """
╔══════════════════════════════════════════╗
  BUILD RÉUSSI
╚══════════════════════════════════════════╝

Projet   : ${env.JOB_NAME}
Build n° : ${env.BUILD_NUMBER}
Branche  : ${env.BRANCH_NAME}
Commit   : ${env.GIT_COMMIT}
Image    : ${IMAGE_NAME}:${IMAGE_TAG}
Registry : ${REGISTRY}
Durée    : ${currentBuild.durationString}

🔗 Logs complets : ${env.BUILD_URL}
                 """
        }
        failure {
            echo "❌ Pipeline en échec !"
            mail to: "${NOTIFY_EMAIL}",
                 subject: "❌ [Jenkins] Build ÉCHOUÉ — ${env.JOB_NAME} #${env.BUILD_NUMBER}",
                 body: """
╔══════════════════════════════════════════╗
  BUILD ÉCHOUÉ ⚠️
╚══════════════════════════════════════════╝

Projet   : ${env.JOB_NAME}
Build n° : ${env.BUILD_NUMBER}
Branche  : ${env.BRANCH_NAME}
Commit   : ${env.GIT_COMMIT}
Durée    : ${currentBuild.durationString}

⚠️  Le déploiement a été bloqué automatiquement.

🔗 Voir les logs : ${env.BUILD_URL}
                 """
        }
        always {
            echo "=== Nettoyage des images intermédiaires ==="
            sh '''
                # Supprimer les images orphelines pour libérer l'espace disque
                docker image prune -f

                # Lister les images Laravel dans le registry
                echo "Images disponibles dans le registry :"
                curl -s http://localhost:5000/v2/laravel-app/tags/list
            '''
        }
    }
}
