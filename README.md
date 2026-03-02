# EduGuide SN — Installation PHP + MySQL

## 📁 Structure du projet
```
eduguide-sn/
├── index.php              ← Page d'accueil
├── ecoles.php             ← Liste des écoles
├── ecole.php              ← Détail d'une école (?slug=esp)
├── concours.php           ← Liste des concours
├── epreuves.php           ← Liste des épreuves + téléchargement
├── orientation.php        ← Outil d'orientation
├── database.sql           ← Script SQL à importer
├── includes/
│   ├── config.php         ← ⚙️ Configuration BDD
│   ├── functions.php      ← Fonctions PHP
│   ├── header.php         ← Navbar partagée
│   └── footer.php         ← Footer partagé
├── assets/
│   └── css/style.css      ← Feuille de styles
└── uploads/
    └── epreuves/          ← Dossier pour les fichiers PDF
```

## 🚀 Installation

### 1. Prérequis
- PHP 8.0+
- MySQL 5.7+ ou MariaDB 10.3+
- Serveur web : Apache (XAMPP/WAMP/Laragon) ou Nginx

### 2. Base de données
```sql
-- Dans phpMyAdmin ou MySQL CLI :
SOURCE /chemin/vers/database.sql;
```
Ou importer le fichier `database.sql` via phpMyAdmin.

### 3. Configuration
Ouvrir `includes/config.php` et modifier :
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'eduguide_sn');
define('DB_USER', 'root');       // votre utilisateur MySQL
define('DB_PASS', '');           // votre mot de passe MySQL
```

### 4. Déploiement
Copier le dossier dans votre répertoire web :
- XAMPP : `C:/xampp/htdocs/eduguide-sn/`
- WAMP  : `C:/wamp64/www/eduguide-sn/`
- Linux : `/var/www/html/eduguide-sn/`

Accéder via : `http://localhost/eduguide-sn/`

## 📄 Ajouter des épreuves PDF
1. Déposer les fichiers PDF dans `uploads/epreuves/`
2. Dans la base de données, mettre à jour le champ `fichier` de la table `epreuves` :
```sql
UPDATE epreuves SET fichier = 'math-esp-2024.pdf' WHERE id = 1;
```

## 🗄️ Tables de la base de données

| Table | Description |
|-------|-------------|
| `ecoles` | Établissements scolaires |
| `concours` | Concours d'entrée |
| `epreuves` | Anciennes épreuves avec compteur de téléchargements |

## 🔗 URLs
| URL | Description |
|-----|-------------|
| `/index.php` | Accueil avec statistiques dynamiques |
| `/ecoles.php?q=esp&type=public` | Filtrer les écoles |
| `/ecole.php?slug=esp` | Détail d'une école |
| `/concours.php?annee=2024&statut=ouvert` | Filtrer les concours |
| `/epreuves.php?matiere=Mathématiques&annee=2023` | Filtrer les épreuves |
| `/epreuves.php?download=1` | Télécharger une épreuve (incrémente le compteur) |
