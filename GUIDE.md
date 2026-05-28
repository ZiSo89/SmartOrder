# SmartOrder — Οδηγός Εκτέλεσης (Local & Online/ngrok)

> Πρακτικός οδηγός για να τρέχεις το project στον υπολογιστή σου (local) και να το εκθέτεις
> online μέσω ngrok στο `https://ointment-diabetic-stank.ngrok-free.dev/el`.
> Για αρχιτεκτονική/πρόοδο δες το `.cursor-progress.md`. Για κανόνες ανάπτυξης δες το `.cursorrules`.

---

## 1. Τι είναι το έργο (σύντομα)

**SmartOrder** = SaaS πλατφόρμα QR-code παραγγελιοληψίας για εστίαση, χτισμένη πάνω στο
**QuickQR v2.4** (Laravel 10). Ο πελάτης σκανάρει QR στο τραπέζι → βλέπει το ψηφιακό μενού
(πολυγλωσσικό: EL/EN/BG/TR) → παραγγέλνει/πληρώνει → ο ιδιοκτήτης βλέπει τις παραγγελίες σε
live dashboard. Στόχος επέκτασης: σύνδεση με τοπικά POS, 100% προπληρωμή (Stripe/Viva).

- **Backend:** Laravel 10 / PHP 8.1–8.2
- **DB:** MySQL, table prefix `qr_`
- **i18n:** EL/EN/BG/TR
- **Web root:** `vanilla files/Quickqr-version-2.4/script/script/`
- **Laravel app root:** `…/script/script/core/`

---

## 2. Προαπαιτούμενα (στον υπολογιστή σου)

| Εργαλείο | Έκδοση | Σημείωση |
|---|---|---|
| PHP | 8.1 ή 8.2 | με extensions: `pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json, fileinfo, gd, curl, zip` |
| MySQL / MariaDB | 5.7+ / 10.4+ | |
| Composer | 2.x | για dependencies (το `vendor/` υπάρχει ήδη committed, αλλά καλό είναι να τρέξεις `install`) |
| ngrok | τελευταία | για online έκθεση |
| (προαιρετικά) Redis | 6+ | θα χρειαστεί στα μελλοντικά features (POS agent) |

> 💡 Στα Windows το ευκολότερο είναι **Laragon** ή **XAMPP** (έχουν PHP+MySQL+Apache έτοιμα).
> Σε macOS/Linux: Homebrew / apt.

---

## 3. Local εγκατάσταση — βήμα-βήμα

### 3.1 Πάρε τον κώδικα
```bash
git clone https://github.com/ZiSo89/SmartOrder.git
cd SmartOrder
```

### 3.2 Μπες στον φάκελο της εφαρμογής
Όλες οι εντολές `artisan`/`composer` τρέχουν μέσα στο **`core/`**:
```bash
cd "vanilla files/Quickqr-version-2.4/script/script/core"
```

### 3.3 Dependencies (προαιρετικό — το vendor υπάρχει ήδη)
```bash
composer install
```

### 3.4 Ρύθμιση `.env`
Το `.env` υπάρχει ήδη ρυθμισμένο (`core/.env`). Επιβεβαίωσε/προσάρμοσε:
```dotenv
APP_NAME="QuickQR"
APP_INSTALLED=true
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_ADMIN=admin               # => admin panel στο /admin

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quickqr
DB_PREFIX=qr_                  # ΟΛΟΙ οι πίνακες έχουν prefix qr_
DB_USERNAME=quickqr
DB_PASSWORD=quickqr123

DEFAULT_LANGUAGE=en
THEME_NAME=classic
```
Αν λείπει το `APP_KEY`:
```bash
php artisan key:generate
```

### 3.5 Δημιουργία βάσης & χρήστη MySQL
```sql
CREATE DATABASE quickqr CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'quickqr'@'127.0.0.1' IDENTIFIED BY 'quickqr123';
GRANT ALL PRIVILEGES ON quickqr.* TO 'quickqr'@'127.0.0.1';
FLUSH PRIVILEGES;
```

### 3.6 Migrations (δημιουργία πινάκων `qr_*`)
```bash
php artisan migrate
```
> Όλα τα migrations είναι idempotent (`if (!Schema::hasTable(...))`), οπότε είναι ασφαλές να ξανατρέξουν.

### 3.7 Δικαιώματα φακέλων (Linux/macOS)
```bash
chmod -R 775 storage bootstrap/cache
# και ο public storage φάκελος στο web root:
chmod -R 775 ../storage
```

### 3.8 Τρέξε τον server

**ΣΗΜΑΝΤΙΚΟ:** Το front controller (`index.php`) είναι στο **web root** (`script/script/`), **όχι** σε `core/public`.
Γι' αυτό το `php artisan serve` **δεν** δουλεύει εδώ. Διάλεξε έναν από τους 2 τρόπους:

#### Τρόπος Α — Apache (XAMPP / Laragon) — προτεινόμενο
Όρισε ως **DocumentRoot** τον φάκελο:
```
…/SmartOrder/vanilla files/Quickqr-version-2.4/script/script
```
Το `.htaccess` εκεί κάνει ήδη το URL rewriting στο `index.php`. Άνοιξε `http://localhost`.

#### Τρόπος Β — PHP built-in server (γρήγορο, χωρίς Apache)
Ο ενσωματωμένος server της PHP δεν ξέρει για τα pretty URLs, οπότε χρειάζεται ένας μικρός router.
Φτιάξε ένα αρχείο `router.php` **μέσα στο web root** (`script/script/router.php`):
```php
<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
// Σέρβιρε τα static αρχεία (assets, storage) απευθείας
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}
// Όλα τα υπόλοιπα -> Laravel front controller
require __DIR__ . '/index.php';
```
Και τρέξε από το web root:
```bash
cd "vanilla files/Quickqr-version-2.4/script/script"
php -S 127.0.0.1:8000 router.php
```
Άνοιξε: `http://127.0.0.1:8000`

### 3.9 Πρόσβαση
- **Public αρχική / landing:** `http://localhost:8000/`
- **Admin panel:** `http://localhost:8000/admin` (το path ορίζεται από `APP_ADMIN`)
- **Δημόσιο μενού εστιατορίου:** `http://localhost:8000/{slug-εστιατορίου}`
- Στοιχεία admin: αυτά που όρισες κατά το install· αν δεν υπάρχουν, δες §7 (reset password).

---

## 4. Online μέσω ngrok (`…ngrok-free.dev/el`)

Το ngrok «προωθεί» έναν δημόσιο HTTPS σύνδεσμο στον local σου server. Δηλαδή πρώτα τρέχει η εφαρμογή τοπικά (§3.8), και μετά την εκθέτεις.

### 4.1 Εγκατάσταση & σύνδεση ngrok
```bash
ngrok config add-authtoken <TO_AUTHTOKEN_SOU>
```
(Το authtoken το παίρνεις από το dashboard του ngrok.)

### 4.2 Εκκίνηση τούνελ στο reserved domain
Με τη νέα σύνταξη:
```bash
ngrok http --url=https://ointment-diabetic-stank.ngrok-free.dev 8000
```
ή με την παλιότερη:
```bash
ngrok http --domain=ointment-diabetic-stank.ngrok-free.dev 8000
```
> Το `8000` = η πόρτα που τρέχει ο local server (§3.8 Τρόπος Β). Αν χρησιμοποιείς Apache στην 80, βάλε `80`.

### 4.3 Ρυθμίσεις Laravel για HTTPS/σωστό domain
Για να φορτώνουν σωστά assets/links κάτω από το ngrok (HTTPS), στο `core/.env`:
```dotenv
APP_URL=https://ointment-diabetic-stank.ngrok-free.dev
```
Και **εμπιστεύσου τον proxy** του ngrok ώστε το Laravel να καταλαβαίνει ότι είναι HTTPS.
Στο `core/app/Http/Middleware/TrustProxies.php` βάλε:
```php
protected $proxies = '*';
```
> Χωρίς αυτό, το Laravel θεωρεί τη σύνδεση HTTP και παράγει `http://` links → «mixed content»/σπασμένα assets.

Καθάρισε cache μετά από αλλαγές `.env`:
```bash
php artisan optimize:clear
# ή μέσω browser:  http://localhost:8000/clear-cache
```

### 4.4 Το `/el` στο URL (γλώσσα)
Το `/el` είναι το **locale prefix** της πολυγλωσσικής δρομολόγησης (πακέτο `mcamara/laravel-localization`).
- Εμφανίζεται όταν είναι ενεργό το `settings.include_language_code` (ρύθμιση στο admin → Settings/Languages).
- Τότε όλα τα URLs έχουν πρόθεμα γλώσσας: `/el/...`, `/en/...`, `/bg/...`, `/tr/...`.
- Για να είναι τα **Ελληνικά** προεπιλογή, όρισε `DEFAULT_LANGUAGE=el` στο `.env` και βεβαιώσου ότι η EL είναι ενεργή/πρώτη στο admin → Languages.

Άνοιξε: `https://ointment-diabetic-stank.ngrok-free.dev/el`

---

## 5. Πολυγλωσσικότητα (i18n) — γρήγορη αναφορά

- **UI strings:** αρχεία `core/lang/<code>/lang.php` (υπάρχουν EL/EN/BG/TR + ar/fr).
- **Περιεχόμενο μενού (τίτλοι/περιγραφές):** αποθηκεύεται σε JSON στήλη `translations` ανά είδος,
  και επιλέγεται βάσει cookie `Quick_user_lang_code`.
- Διαχείριση γλωσσών: **admin → Languages** (ενεργοποίηση, σειρά, μετάφραση strings).

---

## 6. Χρήσιμες εντολές

```bash
# (μέσα στο core/)
php artisan migrate            # τρέξε migrations
php artisan optimize:clear     # καθάρισμα όλων των cache (config/route/view)
php artisan route:list         # δες όλα τα routes
php artisan tinker             # interactive shell

# Χωρίς terminal (μέσω browser):
#   /clear-cache                -> καθαρίζει cache
#   /cronjob                    -> τρέχει το scheduler (για περιοδικές εργασίες)
```

---

## 7. Troubleshooting

| Σύμπτωμα | Αιτία / Λύση |
|---|---|
| Σπασμένο CSS/JS πάνω από ngrok | Όρισε `APP_URL=https://…ngrok-free.dev` + `$proxies='*'` στο `TrustProxies` (§4.3), μετά `optimize:clear`. |
| `419 Page Expired` σε φόρμες | Το domain δεν ταιριάζει· βεβαιώσου ότι μπαίνεις μέσω του ngrok URL (όχι localhost) όταν είσαι online. Έλεγξε `SESSION_DRIVER`. |
| `No application encryption key` | `php artisan key:generate`. |
| 404 σε όλα τα URLs (built-in server) | Λείπει ο `router.php` ή δεν τρέχεις από το web root (§3.8 Τρόπος Β). |
| `SQLSTATE… Access denied` | Λάθος DB creds/πρόσβαση· έλεγξε §3.5 και το `.env` (`DB_PREFIX=qr_`). |
| Δεν μπορώ να μπω στο admin | Reset κωδικού admin: `php artisan tinker` → `\App\Models\User::where('email','EMAIL')->update(['password'=>bcrypt('NEO_PASS')])`. |
| Permission denied σε `storage` | `chmod -R 775 storage bootstrap/cache` και τον public `../storage`. |
| Αλλαγές `.env` δεν «πιάνουν» | `php artisan optimize:clear` (ή `config:clear`). |

---

## 8. Cheat-sheet διαδρομών

| Τι | URL |
|---|---|
| Local αρχική | `http://localhost:8000/` |
| Local admin | `http://localhost:8000/admin` |
| Online (EL) | `https://ointment-diabetic-stank.ngrok-free.dev/el` |
| Online admin | `https://ointment-diabetic-stank.ngrok-free.dev/admin` |
| Μενού εστιατορίου | `…/{slug}` |
| Καθάρισμα cache | `…/clear-cache` |

---

## 9. Σειρά εκκίνησης (TL;DR)

```bash
# 1) MySQL & (αργότερα) Redis up
# 2) Local server:
cd "vanilla files/Quickqr-version-2.4/script/script"
php -S 127.0.0.1:8000 router.php          # (αφού φτιάξεις τον router.php της §3.8)
# 3) Σε άλλο terminal — ngrok:
ngrok http --url=https://ointment-diabetic-stank.ngrok-free.dev 8000
# 4) Άνοιξε: https://ointment-diabetic-stank.ngrok-free.dev/el
```
