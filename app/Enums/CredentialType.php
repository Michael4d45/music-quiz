<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CredentialType: string implements HasColor, HasLabel
{
    use EnumUtil;

    case ApiKey = 'api_key';
    case OauthToken = 'oauth_token';
    case ClientSecret = 'client_secret';

    public function getColor(): string
    {
        return match ($this) {
            self::ApiKey => 'primary',
            self::OauthToken => 'info',
            self::ClientSecret => 'danger',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::ApiKey => 'API Key',
            self::OauthToken => 'OAuth Token',
            self::ClientSecret => 'Client Secret',
        };
    }
}
