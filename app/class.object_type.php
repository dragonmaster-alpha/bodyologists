<?php


namespace App;


class ObjectType
{
    public const PROFILE = 1;
    public const DEAL = 2;
    public const EVENT = 3;
    public const ARTICLE = 4;

    public static function name($value): string
    {
        switch ($value){
            case 1: return 'Profile';
            case 2: return 'Deals';
            case 3: return 'Events';
            case 4: return 'Articles';
            default: return 'Unknown';
        }

    }
}