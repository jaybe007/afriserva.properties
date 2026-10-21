<?php

namespace App\Core;

class Controller
{
    protected function render(string $view, array $data = [], ?string $layout = 'layouts/main'): string
    {
        return View::render($view, $data, $layout);
    }

    protected function json(Response $response, array $data, int $status = 200): void
    {
        $response->json($data, $status);
    }

    protected function redirect(Response $response, string $url): void
    {
        $response->redirect($url);
    }

    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $fieldRules) {
            $value = trim((string)($data[$field] ?? ''));
            $ruleList = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;

            foreach ($ruleList as $rule) {
                if ($rule === 'required' && empty($value)) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
                    break;
                }
                if ($rule === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = 'Please enter a valid email address.';
                    break;
                }
                if (str_starts_with($rule, 'min:') && !empty($value)) {
                    $min = (int)substr($rule, 4);
                    if (strlen($value) < $min) {
                        $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min} characters.";
                        break;
                    }
                }
                if (str_starts_with($rule, 'numeric') && !empty($value)) {
                    if (!is_numeric($value)) {
                        $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must be a numeric value.';
                        break;
                    }
                }
            }
        }
        return $errors;
    }
}
