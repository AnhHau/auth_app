<?php

namespace App\Helper;

class Validator
{
    protected $errors = [];
    protected $customMessages = [];

    public function validate(array $data, array $rules, array $messages = [])
    {
        $this->customMessages = $messages;
        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;
            foreach ($ruleSet as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }
        return empty($this->errors);
    }

    protected function applyRule($field, $value, $rule)
    {
        if (strpos($rule, ':') !== false) {
            list($ruleName, $parameter) = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $parameter = null;
        }

        switch ($ruleName) {
            case 'required':
                if (empty($value)) {
                    $this->addError($field, 'required');
                }
                break;
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'email');
                }
                break;
            case 'min':
                if (strlen($value) < $parameter) {
                    $this->addError($field, 'min', $parameter);
                }
                break;
            case 'max':
                if (strlen($value) > $parameter) {
                    $this->addError($field, 'max', $parameter);
                }
                break;
            case 'regex':
                if (!preg_match($parameter, $value)) {
                    $this->addError($field, 'regex');
                }
                break;
            case 'number':
                if (!is_numeric($value)) {
                    $this->addError($field, 'number');
                }
                break;
                // Add more rules as needed
        }
    }

    protected function addError($field, $rule, $parameter = null)
    {
        $message = $this->customMessages["$field.$rule"] ?? $this->getDefaultMessage($field, $rule, $parameter);
        $this->errors[$field][] = $message;
    }

    protected function getDefaultMessage($field, $rule, $parameter)
    {
        $messages = [
            'required' => "$field is required.",
            'email' => "$field must be a valid email address.",
            'min' => "$field must be at least $parameter characters.",
            'max' => "$field must not exceed $parameter characters.",
            'regex' => "$field format is invalid.",
            'number' => "$field must be a number.",
        ];
        return $messages[$rule] ?? "$field validation failed for rule: $rule.";
    }

    public function errors()
    {
        return $this->errors;
    }
}

/* // example
    $data = [
        'emails' => ['email@example.com', 'invalid-email', 'another@example.com'],
        'names' => ['John', 'Jane', 'Doe'],
        'ages' => ['25', 'invalid-age', '30']
    ];

    $rules = [
        'emails.*' => ['required', 'email'],
        'names.*' => ['required', 'max:50'],
        'ages.*' => ['required', 'number']
    ];

    $messages = [
        'emails.*.required' => 'Email is required.',
        'emails.*.email' => 'Email must be a valid email address.',
        'names.*.required' => 'Name is required.',
        'names.*.max' => 'Name must not exceed 50 characters.',
        'ages.*.required' => 'Age is required.',
        'ages.*.number' => 'Age must be a number.'
    ];

    $validator = new Validator();
    if ($validator->validate($data, $rules, $messages)) {
        echo "Validation passed!";
    } else {
        print_r($validator->errors());
    }
 */