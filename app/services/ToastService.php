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

    static function render(bool $oob = false)
    {
        if ($oob) {
            header('HX-Retarget: #toast-root');
            header('HX-Reswap: afterbegin');
        }
        if (self::$message): ?>
            <div class="toast show <?= self::$level->value ?>" aria-live="polite" hx-on:animationend="htmx.remove(this)">
                <?= self::$message ?>
            </div>
        <?php endif;
    }

    static function error(string $message)
    {
        return self::create($message, ToastLevel::Error);
    }
    static function success(string $message)
    {
        return self::create($message, ToastLevel::Success);
    }
    static function info(string $message)
    {
        return self::create($message, ToastLevel::Info);
    }
}

enum ToastLevel: string
{
    case Success = "success";
    case Error = "error";
    case Warning = "warning";
    case Info = "info";
}