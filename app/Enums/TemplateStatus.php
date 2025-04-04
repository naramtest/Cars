<?php

namespace App\Enums;

enum TemplateStatus: string
{
    case APPROVED = "APPROVED";
    case IN_APPEAL = "IN_APPEAL";
    case PENDING = "PENDING";
    case REJECTED = "REJECTED";
    case PENDING_DELETION = "PENDING_DELETION";
    case DELETED = "DELETED";
    case DISABLED = "DISABLED";
    case PAUSED = "PAUSED";
    case LIMIT_EXCEEDED = "LIMIT_EXCEEDED";
    case ARCHIVED = "ARCHIVED";
}
