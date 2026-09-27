# MarketLink - Phase 1 setup

Ye folder ek fresh Laravel + Breeze project ke upar copy karna hai.
Isme design system, poora database structure, models, role system,
teeno panels ka layout aur teeno dashboards hain.

## 1. Fresh project banao

```bash
composer create-project laravel/laravel marketlink
cd marketlink
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
npm install iconify-icon
```

Agar Breeze latest Laravel version pe install hone se mana kare, to error mujhe bhej dena.

## 2. Is folder ki files copy karo

Poora folder project ke root pe paste karo aur "replace" allow karo.
Ye Breeze ki in files ko replace karega:
`app/Models/User.php`, `routes/web.php`, `tailwind.config.js`,
`resources/css/app.css`, `resources/js/app.js`, `resources/views/layouts/guest.blade.php`,
`database/seeders/DatabaseSeeder.php`

## 3. Middleware register karo

`bootstrap/app.php` mein `withMiddleware` wala hissa aisa kar do:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        'active' => \App\Http\Middleware\EnsureAccountIsActive::class,
    ]);
})
```

## 4. Database

`.env` mein MySQL set karo:

```
DB_CONNECTION=mysql
DB_DATABASE=marketlink
DB_USERNAME=root
DB_PASSWORD=
MAIL_MAILER=log
```

Phir:

```bash
php artisan migrate:fresh --seed
php artisan storage:link
npm run dev
php artisan serve
```

## Scheduler (urgent orders auto-ready)

Green Valley Farm ne "Let MarketLink AI mark urgent orders ready" on kiya hua hai
(prep time 2 minute). Urgent order confirm hone ke 2 minute baad MarketLink AI use
"Ready for pickup" kar deta hai aur customer aur farmer dono ko notification jati hai.

Ye kaam har minute Laravel scheduler karta hai. `php artisan serve` ke saath
**ek alag terminal** mein ye chalao aur khula chhod do:

```bash
php artisan schedule:work
```

Production mein `schedule:work` ki jagah server pe ek cron entry lagti hai:

```
* * * * * cd /path/to/marketlink && php artisan schedule:run >> /dev/null 2>&1
```

Ek baar haath se chalana ho to: `php artisan urgent-orders:mark-ready`

Agar scheduler na chal raha ho tab bhi order atakta nahi: farmer ya customer jab apna
dashboard ya orders page (list ya order detail) kholta hai, us se pehle uske apne due
urgent orders ready ho jate hain. Bas notification thodi der se aayegi.

## 5. Images

`IMAGES.md` wali list ke hisaab se photos `public/images/` mein daalo.

## Demo logins

| Role | Email | Password |
|---|---|---|
| Super admin | admin@marketlink.test | Admin@1234 |
| Support admin | support@marketlink.test | Support@1234 |
| Community moderator | community-mod@marketlink.test | Community@1234 |
| Farmer (approved) - Dallas, Cowtown | greenvalley@marketlink.test | Farmer@1234 |
| Farmer (approved) - Houston, Pearl | sunrise@marketlink.test | Farmer@1234 |
| Farmer (approved) - Houston, Austin | desidairy@marketlink.test | Farmer@1234 |
| Farmer (approved) - Cowtown, Dallas | crustcrumb@marketlink.test | Farmer@1234 |
| Farmer (approved) - El Paso | mesillavalley@marketlink.test | Farmer@1234 |
| Farmer (pending approval) - El Paso | wildflower@marketlink.test | Farmer@1234 |
| Customer - Dallas | sara@marketlink.test | Customer@1234 |
| Customer - Houston | bilal@marketlink.test | Customer@1234 |
| Customer - Austin | ayesha@marketlink.test | Customer@1234 |

Login ke baad `/dashboard` khud role ke hisaab se sahi panel pe bhej deta hai.
Customer agar farmer ka URL khole to 403 milega (role middleware).

## Email (demo)

**Email validation:** `.env` mein `ABSTRACT_EMAIL_VALIDATION_API_KEY` daalo to sign-up aur
email badalne pe Abstract check karta hai ke email asli hai. Key na ho to bhi app chalti
hai - sirf Laravel ka basic email check (format + domain DNS) hota hai.

`MAIL_MAILER=log` pe koi email bheji nahi jati - har email (order notifications,
password reset waghera) `storage/logs/laravel.log` mein likh di jati hai.

- Naya account email verify kiye baghair seedha dashboard pe chala jata hai.
- **Forgot password:** login page pe "Forgot your password?" se email daalo, phir
  `storage/logs/laravel.log` kholo aur sab se neeche wali email mein
  `/reset-password/...` wala link copy karke browser mein kholo.

