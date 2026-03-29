<?php

namespace App\Enums;

/**
 * Application user roles for event-booking RBAC.
 */
enum UserRole: string
{
    case Admin = 'admin';
    case Organizer = 'organizer';
    case Customer = 'customer';
}
