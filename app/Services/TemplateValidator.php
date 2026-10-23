<?php

namespace App\Services;

/**
 * Checks a template's sections before staff can save a new version, so a
 * typo in the admin panel can't break copy-assist for every user.
 */
class TemplateValidator
{
    public const FORMATS = ['as_is', 'upper', 'date_dmy', 'digits', 'option_en'];

    /** @return list<string> problems, empty when the template is valid */
    public function errors(mixed $sections): array
    {
        if (! is_array($sections) || ! array_is_list($sections) || $sections === []) {
            return ['Sections must be a non-empty JSON array.'];
        }

        $errors = [];
        $keys = [];
        $profileKeys = array_keys(array_filter(config('formsathi.fields'), fn ($f) => $f['profile'] ?? false));

        foreach ($sections as $s => $section) {
            $where = 'Section '.($s + 1);

            foreach (['key', 'bn', 'en'] as $prop) {
                if (! is_string($section[$prop] ?? null) || trim($section[$prop]) === '') {
                    $errors[] = "{$where}: \"{$prop}\" is required.";
                }
            }

            if (! is_array($section['fields'] ?? null) || $section['fields'] === []) {
                $errors[] = "{$where}: needs at least one field.";

                continue;
            }

            foreach ($section['fields'] as $f => $field) {
                $at = "{$where}, field ".($f + 1);
                $key = $field['key'] ?? null;

                if (! is_string($key) || ! preg_match('/^[a-z0-9_]+$/', $key)) {
                    $errors[] = "{$at}: \"key\" must be lowercase letters, digits and _.";
                } elseif (isset($keys[$key])) {
                    $errors[] = "{$at}: key \"{$key}\" is used twice.";
                } else {
                    $keys[$key] = true;
                }

                if (! is_string($field['site'] ?? null) || trim($field['site']) === '') {
                    $errors[] = "{$at}: \"site\" (the label on the official form) is required.";
                }

                $profile = $field['profile'] ?? null;
                if ($profile !== 'account_mobile' && ! in_array($profile, $profileKeys, true)) {
                    $errors[] = "{$at}: \"profile\" must be a profile field or account_mobile.";
                }

                if (! in_array($field['format'] ?? null, self::FORMATS, true)) {
                    $errors[] = "{$at}: \"format\" must be one of ".implode(', ', self::FORMATS).'.';
                } elseif ($field['format'] === 'option_en' && ! isset(config("formsathi.fields.{$profile}")['options'])) {
                    $errors[] = "{$at}: option_en only works with a dropdown profile field.";
                }

                if (! is_bool($field['required'] ?? null)) {
                    $errors[] = "{$at}: \"required\" must be true or false.";
                }
            }
        }

        return $errors;
    }
}
