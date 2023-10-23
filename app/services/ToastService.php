<?php

class Toast
{
    public static ToastLevel $level = ToastLevel::Success;
    public static string $message = "";
    static function create($message, $level = ToastLevel::Success)
    {
        self::$message = $message;
        self::$level = $level;
        header("HX-Trigger-After-Settle: toastMessage");
    }

    static function pop()
    {
        $message = self::$message;
        $level = self::$level;
        self::$message = "";
        self::$level = ToastLevel::Success;
        return [$message, $level];
    }

    static function exists()
    {
        return !!self::$message;
    }
}

enum ToastLevel: string
{
    case Success = "success";
    case Error = "error";
    case Warning = "warning";
    case Info = "info";
}