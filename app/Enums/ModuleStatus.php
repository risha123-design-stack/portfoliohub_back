<?php

namespace App\Enums;

enum ModuleStatus: string
{
    case PENDING = 'pending';
    case DRAFT = 'draft';
    case COMPLETED = 'completed';
}