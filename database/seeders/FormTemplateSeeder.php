<?php

namespace Database\Seeders;

use App\Models\FormTemplate;
use Illuminate\Database\Seeder;

/**
 * Loads every template file in database/templates. Runs on every deploy,
 * so it only adds versions the database doesn't have yet: an existing
 * version (and staff edits to it, like "checked against the official
 * form") is never overwritten. To change a template, bump its version.
 */
class FormTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach (glob(database_path('templates/*.php')) as $file) {
            $template = require $file;

            FormTemplate::firstOrCreate(
                ['slug' => $template['slug'], 'version' => $template['version']],
                $template,
            );
        }
    }
}
