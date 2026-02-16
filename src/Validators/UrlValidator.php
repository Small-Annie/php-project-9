<?php

namespace App\Validators;

use Valitron\Validator;

class UrlValidator
{
    public static function validate(array $data): array
    {
        $validator = new Validator($data);

        $validator->rule('required', 'name')
            ->message('URL не должен быть пустым');

        $validator->rule('lengthMax', 'name', 255)
            ->message('URL слишком длинный');

        $validator->rule('url', 'name')
            ->message('Некорректный URL');

        if ($validator->validate()) {
            return [];
        }

        return $validator->errors();
    }
}
