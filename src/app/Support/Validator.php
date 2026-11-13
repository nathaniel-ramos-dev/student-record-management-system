<?php

declare(strict_types=1);

namespace App\Support;

use DateTimeImmutable;

/**
 * Minimal, dependency-free validator for request payloads.
 */
final class Validator
{
    /** @var array<string, string> */
    private array $errors = [];

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(private readonly array $data)
    {
    }

    public function required(string $field, string $label): self
    {
        if ($this->value($field) === '') {
            $this->add($field, sprintf('%s is required.', $label));
        }

        return $this;
    }

    public function email(string $field, string $label): self
    {
        $value = $this->value($field);

        if ($value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->add($field, sprintf('%s must be a valid e-mail address.', $label));
        }

        return $this;
    }

    public function max(string $field, string $label, int $max): self
    {
        if (mb_strlen($this->value($field)) > $max) {
            $this->add($field, sprintf('%s may not be longer than %d characters.', $label, $max));
        }

        return $this;
    }

    public function minLength(string $field, string $label, int $min): self
    {
        $value = $this->value($field);

        if ($value !== '' && mb_strlen($value) < $min) {
            $this->add($field, sprintf('%s must be at least %d characters.', $label, $min));
        }

        return $this;
    }

    /**
     * @param list<string> $allowed
     */
    public function in(string $field, string $label, array $allowed): self
    {
        $value = $this->value($field);

        if ($value !== '' && !in_array($value, $allowed, true)) {
            $this->add($field, sprintf('%s contains an invalid value.', $label));
        }

        return $this;
    }

    public function date(string $field, string $label): self
    {
        $value = $this->value($field);

        if ($value === '') {
            return $this;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        if ($date === false || $date->format('Y-m-d') !== $value) {
            $this->add($field, sprintf('%s must be a valid date.', $label));
        }

        return $this;
    }

    public function notInFuture(string $field, string $label): self
    {
        $value = $this->value($field);

        if ($value === '') {
            return $this;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        if ($date !== false && $date > new DateTimeImmutable('today')) {
            $this->add($field, sprintf('%s may not be in the future.', $label));
        }

        return $this;
    }

    public function integerBetween(string $field, string $label, int $min, int $max): self
    {
        $value = $this->value($field);

        if ($value === '') {
            return $this;
        }

        if (!ctype_digit($value) || (int) $value < $min || (int) $value > $max) {
            $this->add($field, sprintf('%s must be between %d and %d.', $label, $min, $max));
        }

        return $this;
    }

    public function regex(string $field, string $label, string $pattern, string $message): self
    {
        $value = $this->value($field);

        if ($value !== '' && preg_match($pattern, $value) !== 1) {
            $this->add($field, sprintf('%s %s', $label, $message));
        }

        return $this;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    /**
     * @return array<string, string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    public function value(string $field): string
    {
        $value = $this->data[$field] ?? '';

        return is_scalar($value) ? trim((string) $value) : '';
    }

    private function add(string $field, string $message): void
    {
        $this->errors[$field] ??= $message;
    }
}
