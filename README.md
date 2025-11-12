# Innomiate

Innomiate is a web platform that manages registration for AI hackathons (.e.g: **Miathon**) organized by **MIA (Maison d’Intelligence Artificielle)**.  
It streamlines participant registration, team formation, and competition management, reducing the organizational challenges of handling hackathons manually.

---

## 🚀 Purpose
Managing Miathon competitions offline takes time and creates organizational problems. Innomiate helps participants **register, verify, and organize their teams before the event begins**.

---

## 👥 Target Audience
- AI & IT enthusiasts  
- Developers  
- Students  
- Professors  

---

## 🛠️ Tech Stack
- **Backend**: Symfony (PHP)  
- **Frontend**: Twig, HTML, CSS, Vanilla JS  
- **Database**: MySQL  
- **APIs**: Google reCAPTCHA (for signup/login security)  
- **Other**: Lottie animations  

---

## ✨ Features
- 🔑 Sign up / login with CAPTCHA  
- 📧 Email verification before accessing core features  
- 👤 User accounts with participant profiles  
- 👥 Team creation & management  
- 📩 Invitations: send/accept requests to join teams  
- 📢 Pitch system: post ideas to look for teammates or teams  
- 🆘 Support page  
- 🎨 Theme customization  
- 🛠️ Admin panel with full control over the platform  

---

## 🏗️ Architecture
The project follows **MVC (Model-View-Controller)**.  
Coding standards PSR-12.

**Entities / Models:**
- `User`  
- `Participant`  
- `Team`  
- `Competition`  
- `Invitation`  
- `Pitch`  

**Workflow:**
1. User registers an account  
2. Verifies email  
3. Can join competition individually  
4. Can then join/create a team → both phases complete the registration process  

---

## ⚙️ Installation & Setup (For next Developers)
1. Clone the repo:
```bash
   git clone https://github.com/MoadLemrani/innomiate.git
   cd innomiate
```
2. Install dependencies:
```bash
   composer install
```
3. Configure environment variables:
   Database connection in .env
   Mailer for email verification
   reCAPTCHA API keys
4. Run database migrations:
```bash
   php bin/console doctrine:migrations:migrate
```
5. Start the development server:
```bash
   symfony server:start
```
---

## 🔒 Authentication & Verification
- Users can log in immediately after creating an account.
- Core features (registration, invitations, pitches) are locked until email verification is completed.

---

## ⚠️ Limitations
- Registration currently supports one competition only (hard-coded).
- JavaScript only executes properly after reloading (known bug).
- No server-side logging for errors (only flash messages are shown to users).

--- 

## 🛤️ Roadmap
- Make competition registration fully dynamic (multi-competition support).
- Add server-side logging for better debugging.
- Fix JavaScript initialization bug.
- Add likes & comments entities for pitches.
- Leader can send invitations.

---

## 🤝 Contribution
Future contributions may follow:
- Branch per feature
- Pull request reviews before merging
- Symfony/PSR-12 coding style

---

## 🚀 Guide de Déploiement en Production

Ce guide explique comment déployer **Innomiate** sur un serveur de production.

---

### 1. Prérequis Serveur
- **PHP** : >= 8.1 avec extensions `ctype`, `iconv`, `pdo_mysql`
- **Base de données** : MySQL 8.0+ ou MariaDB 10.5+
- **Serveur Web** : Apache ou Nginx
- **Composer** : v2.x

---

### 2. Récupération du code & installation
```bash
git clone https://github.com/MoadLemrani/innomiate.git
cd innomiate
composer install --no-dev --optimize-autoloader
```

---

### 3. Variables d'environnement

Créer un fichier `.env.local` :

```env
APP_ENV=prod
APP_DEBUG=0

# Base de données
DATABASE_URL="mysql://DB_USER:DB_PASSWORD@127.0.0.1:3306/DB_NAME?serverVersion=8.0"

# Mailer (pour vérification email)
MAILER_DSN=smtp://USERNAME:PASSWORD@HOST:PORT

# Google reCAPTCHA
RECAPTCHA_SITE_KEY=your_site_key
RECAPTCHA_SECRET_KEY=your_secret_key
```

---

### 4. Base de données

Exécuter les migrations :

```bash
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
```

---

### 5. Optimisation Symfony

```bash
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

---

### 6. Configuration Serveur Web

#### 🔹 Exemple Nginx

```nginx
server {
    server_name votre-domaine.com;
    root /var/www/innomiate/public;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

#### 🔹 Exemple Apache

Activez les modules nécessaires :

```bash
a2enmod rewrite proxy_fcgi setenvif
```

VirtualHost :

```apache
<VirtualHost *:80>
    ServerName votre-domaine.com
    DocumentRoot /var/www/innomiate/public

    <Directory /var/www/innomiate/public>
        AllowOverride All
        Order Allow,Deny
        Allow from All

        <IfModule mod_rewrite.c>
            Options -MultiViews
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^ index.php [QSA,L]
        </IfModule>
    </Directory>

    <FilesMatch \.php$>
        SetHandler "proxy:unix:/var/run/php/php8.1-fpm.sock|fcgi://localhost/"
    </FilesMatch>
</VirtualHost>
```

---

### 7. Tâches en arrière-plan (Messenger/Emails)

Si vous utilisez Messenger pour l'envoi d'emails :

```bash
php bin/console messenger:consume async -vv --env=prod
```

👉 À configurer comme service **systemd** ou via **Supervisor** pour rester actif.

---

### 8. Checklist Sécurité

```bash
# Vérifier les configurations critiques
APP_DEBUG=0
# Forcer HTTPS (Let's Encrypt conseillé)
# Vérifier les permissions sur var/ et public/
# Mettre à jour régulièrement les dépendances
composer update --no-dev
```

---
```