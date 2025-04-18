FROM php:8.2-cli

# Installer les extensions nécessaires à Symfony + Doctrine
RUN apt-get update && apt-get install -y \
    unzip git zip libicu-dev libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Créer le répertoire de travail
WORKDIR /app

# Copier tout le projet
COPY . .

# Installer les dépendances PHP (en prod)
RUN composer install --no-dev --optimize-autoloader

# Exposer le port pour Render
EXPOSE 8000

# Commande de lancement (Render lit `startCommand`)
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
