<?php

namespace Database\Seeders;

use App\Models\FormTemplate;
use Illuminate\Database\Seeder;

/**
 * Loads every template file in database/templates. Safe to run again:
 * an existing slug + version is updated in place, a new version is added.
 */
class FormTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach (glob(database_path('templates/*.php')) as $file) {
            $template = require $file;

            FormTemplate::updateOrCreate(
                ['slug' => $template['slug'], 'version' => $template['version']],
                $template,
            );
        }
    }
}
