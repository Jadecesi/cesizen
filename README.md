# Guide d'installation du projet CESIZen

## 🔧 Prérequis

Avant d'installer le projet, assurez-vous d'avoir les outils suivants installés :

- [Git](https://git-scm.com/)
- Un IDE de votre choix (VSCode, PHPStorm, etc.)
- PHP (version **8.0 ou supérieure**)
- [Composer](https://getcomposer.org/)
- [Docker](https://www.docker.com/) + Docker Compose
- [Symfony CLI](https://symfony.com/download)
- [Flutter SDK](https://flutter.dev/)

### Outils optionnels :

- [DBeaver](https://dbeaver.io/) (interface de gestion de base de données)
- [Postman](https://www.postman.com/) (tests d’API)

---

## 🚀 Installation du site web (Symfony)

### 1. Cloner le dépôt GitHub

```bash
git clone https://github.com/Jadecesi/cesizen
cd cesizen
```

### 2. Installer les dépendances PHP

```bash
composer install
```

### 3. Lancer les conteneurs Docker (base de données)

```bash
docker-compose up -d
```

### 4. Configurer l’environnement

Créez un fichier `.env.local` à la racine du projet et ajoutez la configuration suivante (exemple pour MariaDB avec Docker) :

```env
DATABASE_URL="mysql://user:password@127.0.0.1:3306/cesizen?serverVersion=mariadb-10.5"
```

### 5. Créer la base de données

```bash
php bin/console doctrine:database:create
```

### 6. Appliquer les migrations

```bash
php bin/console doctrine:migrations:migrate
```

### 7. Charger des données de test (optionnel)

```bash
php bin/console doctrine:fixtures:load
```

### 8. Lancer le serveur Symfony

- Pour un usage simple :

```bash
symfony server:start
```

- Pour un accès depuis Flutter ou autre appareil :

```bash
php -S 0.0.0.0:8000 -t public
```

---

## 📱 Installation de l’application mobile (Flutter)

### 1. Cloner le dépôt GitHub

```bash
git clone https://github.com/Jadecesi/cesizenMobile
cd cesizenMobile
```

### 2. Installer les dépendances Flutter

```bash
flutter pub get
```

### 3. Configurer l’URL de l’API Symfony

Modifier le fichier `api_service.dart` :

```dart
static const String _baseUrl = "http://192.168.x.x:8000";
```

> Remplacez `192.168.x.x` par l’IP de votre machine ou `localhost` si l'application mobile est lancée sur le même appareil.

### 4. Brancher un téléphone ou démarrer un émulateur

### 5. Lancer l’application

```bash
flutter run
```

---

## ❗ Problèmes courants

- **Port 8000 occupé** : vérifiez que le port n’est pas déjà utilisé par un autre service.
- **Erreur SQL / Migration** : assurez-vous que Docker est bien lancé et que les migrations ont été appliquées correctement avec :

```bash
php bin/console doctrine:migrations:migrate
```
