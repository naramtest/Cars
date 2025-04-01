<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SocialNetwork: string implements HasLabel
{
    case FACEBOOK = "facebook";
    case YOUTUBE = "youtube";
    case TWITTER = "twitter";
    case TIKTOK = "tiktok";
    case INSTAGRAM = "instagram";
    case WHATSAPP = "whatsapp";

    public function getLabel(): ?string
    {
        return match ($this) {
            self::FACEBOOK => "Facebook",
            self::YOUTUBE => "Youtube",
            self::TWITTER => "Twitter",
            self::INSTAGRAM => "Instagram",
            self::TIKTOK => "TikTok",
            self::WHATSAPP => "WhatsApp",
        };
    }
}
