<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Kreait\Firebase\Auth\ActionCodeSettings\ValidatedActionCodeSettings;
use Kreait\Firebase\Value\Email;
use Stringable;

/**
 * @internal
 */
final class CreateActionLink
{
    /**
     * @readonly
     */
    private ?string $tenantId;
    /**
     * @readonly
     */
    private ?string $locale;
    /**
     * @readonly
     */
    private string $type;
    /**
     * @readonly
     */
    private string $email;
    /**
     * @readonly
     */
    private ActionCodeSettings $settings;
    private function __construct(?string $tenantId, ?string $locale, string $type, string $email, ActionCodeSettings $settings)
    {
        $this->tenantId = $tenantId;
        $this->locale = $locale;
        $this->type = $type;
        $this->email = $email;
        $this->settings = $settings;
    }
    /**
     * @param Stringable|string $email
     */
    public static function new(string $type, $email, ActionCodeSettings $settings, ?string $tenantId = null, ?string $locale = null): self
    {
        $email = Email::fromString((string) $email)->value;

        return new self($tenantId, $locale, $type, $email, $settings);
    }

    public function type(): string
    {
        return $this->type;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function settings(): ActionCodeSettings
    {
        return $this->settings ?? ValidatedActionCodeSettings::empty();
    }

    public function tenantId(): ?string
    {
        return $this->tenantId;
    }

    public function locale(): ?string
    {
        return $this->locale;
    }
}
