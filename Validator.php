<?php
declare(strict_types=1);

namespace App\Core;

final class Validator
{
    public const RULE_REQUIRED = 'required';
    public const RULE_EMAIL = 'email';
    public const RULE_MAX = 'max';
    public const RULE_MIN = 'min';
    public const RULE_SAME = 'same';

    private static array $RULES_CALLBACK = [
        self::RULE_REQUIRED => "requiredValidate",
        self::RULE_EMAIL => "emailValidate",
        self::RULE_MAX => "maxValidate",
        self::RULE_MIN => "minValidate",
        self::RULE_SAME => "sameValidate",
    ];

    private static array $RULES_ERRORS = [
        self::RULE_REQUIRED => "This field is required",
        self::RULE_EMAIL => "This field must be valid email address",
        self::RULE_MAX => "Max length of this field must be {max}",
        self::RULE_MIN => "Min length of this field must be {min}",
        self::RULE_SAME => "This field must be the same as {same}",
    ];

    private function requiredValidate(string|null $value): bool
    {
        return isset($value) && strlen(trim($value));
    }

    private function emailValidate(string $value): bool
    {
        $emailRegex = "/^([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$/";
        return !!preg_match($emailRegex, $value);
    }

    private function maxValidate(string $value, array $params): bool
    {
        $maxNumber = $params["max"];
        return strlen($value) <= $maxNumber;
    }

    private function minValidate(string $value, array $params): bool
    {
        $minNumber = $params["min"];
        return strlen($value) >= $minNumber;
    }

    private function sameValidate(string $value, array $params): bool
    {
        $field = $params["same"];
        $fieldValue = $this->data[$field];
        return $value === $fieldValue;
    }

    public array $errors = [];

    public function __construct(private readonly array $data)
    {

    }

    private function addErrorByRule(string $property, string $rule, array $params = []): void
    {
        $message = self::$RULES_ERRORS[$rule];
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $message = str_replace("{{$key}}", (string)$value, $message);
            }
        }
        $this->errors[$property][] = $message;
    }

    public function validate(array $rules): array
    {
        foreach ($rules as $property => $propertyRules) {
            $value = $this->data[$property];

            foreach ($propertyRules as $rule) {
                $ruleName = $rule;
                $ruleParams = [];

                if (is_array($rule)) {
                    [$ruleName, $ruleParams] = $rule;
                }

                $callback = self::$RULES_CALLBACK[$ruleName];

                if ($this->{$callback}($value, $ruleParams) === false) {
                    $this->addErrorByRule($property, $ruleName, $ruleParams);
                }
            }
        }

        return $this->errors;
    }

    public function validated(): bool
    {
        return empty($this->errors);
    }
}
