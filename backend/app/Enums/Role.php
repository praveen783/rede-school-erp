<?php

namespace App\Enums;

final class Role
{
    public const SUPER_ADMIN   = 'super_admin';
    public const SCHOOL_ADMIN  = 'school_admin';
    public const TEACHER       = 'teacher';
    public const STUDENT       = 'student';
    public const PARENT        = 'parent';
    public const ACCOUNTANT    = 'accountant';

    /**
     * Return all roles as array
     */
    public static function all(): array
    {
        return [
            self::SUPER_ADMIN,
            self::SCHOOL_ADMIN,
            self::TEACHER,
            self::STUDENT,
            self::PARENT,
            self::ACCOUNTANT,
        ];
    }
}
