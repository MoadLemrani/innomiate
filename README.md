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
   git clone https://github.com/MoadLemrani/innomiate.git
   cd innomiate
2. Install dependencies:
   composer install
3. Configure environment variables:
   Database connection in .env
   Mailer for email verification
   reCAPTCHA API keys
4. Run database migrations:
   php bin/console doctrine:migrations:migrate
5. Start the development server:
   symfony server:start

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