<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $startTime = microtime(true);

        $locales = ['en', 'fr', 'es'];
        $batchSize = 5000;
        $totalRecords = 100000;
        $tags = ['web', 'mobile'];

        $groups = [
            'auth.login' => [
                'title' => ['en' => 'Welcome Back', 'fr' => 'Bon retour', 'es' => 'Bienvenido'],
                'email' => ['en' => 'Email Address', 'fr' => 'Adresse e-mail', 'es' => 'Correo electrónico'],
                'password' => ['en' => 'Password', 'fr' => 'Mot de passe', 'es' => 'Contraseña'],
                'submit' => ['en' => 'Sign In', 'fr' => 'Se connecter', 'es' => 'Iniciar sesión'],
            ],
            'auth.signup' => [
                'title' => ['en' => 'Create Account', 'fr' => 'Créer un compte', 'es' => 'Crear cuenta'],
                'first_name' => ['en' => 'First Name', 'fr' => 'Prénom', 'es' => 'Nombre'],
                'last_name' => ['en' => 'Last Name', 'fr' => 'Nom de famille', 'es' => 'Apellido'],
                'button' => ['en' => 'Register', 'fr' => 'S\'inscrire', 'es' => 'Registrarse'],
            ],
            'dashboard.sidebar' => [
                'home' => ['en' => 'Home', 'fr' => 'Accueil', 'es' => 'Inicio'],
                'settings' => ['en' => 'Settings', 'fr' => 'Paramètres', 'es' => 'Configuración'],
                'logout' => ['en' => 'Logout', 'fr' => 'Déconnexion', 'es' => 'Cerrar sesión'],
            ],
            'profile.account' => [
                'header' => ['en' => 'Account Details', 'fr' => 'Détails du compte', 'es' => 'Detalles de la cuenta'],
                'bio' => ['en' => 'Your Bio', 'fr' => 'Votre biographie', 'es' => 'Tu biografía'],
            ]
        ];

        $data = [];
        foreach ($groups as $prefix => $keys) {
            foreach ($keys as $key => $values) {
                foreach ($locales as $locale) {
                    $data[] = [
                        'key' => "{$prefix}.{$key}",
                        'locale' => $locale,
                        'value' => $values[$locale],
                        'tags' => json_encode($tags),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        DB::table('contents')->insert($data);
        $fakers = [
            'en' => Factory::create('en_US'),
            'fr' => Factory::create('fr_FR'),
            'es' => Factory::create('es_ES'),
        ];

        $this->command->info("Pre-generating real language text pools...");
        $sentencePool = [];
        foreach ($locales as $locale) {
            for ($p = 0; $p < 50; $p++) {
                $sentencePool[$locale][] = $fakers[$locale]->realText(50);
            }
        }

        $this->command->info("Inserting 100,000 records...");

        for ($i = 0; $i < ($totalRecords / $batchSize); $i++) {
            $batch = [];
            for ($j = 0; $j < $batchSize; $j++) {
                $locale = $locales[array_rand($locales)];
                $group = ['marketing', 'legal', 'system', 'api'][$j % 4];

                $batch[] = [
                    'key' => "{$group}.item." . ($i * $batchSize + $j),
                    'locale' => $locale,
                    'value' => $sentencePool[$locale][array_rand($sentencePool[$locale])],
                    'tags' => json_encode($tags),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('contents')->insert($batch);
        }

        $executionTime = round(microtime(true) - $startTime, 2);

        $this->command->info("Seeding completed in {$executionTime}s");
    }
}
