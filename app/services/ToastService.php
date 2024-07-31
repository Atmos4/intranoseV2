<?php

class ToastItem
{
    function __construct(
        public string $message = "",
        public ToastLevel $level = ToastLevel::Success
    ) {
    }
}

class Toast
{
    /** @var ToastItem[] */
    public static array $toasts = [];
    static function create($message, $level = ToastLevel::Success)
    {
        self::$toasts[] = new ToastItem($message, $level);
    }

    static function stash()
    {
        $_SESSION['toasts'] = self::$toasts;
    }

    static function popStash()
    {
        self::$toasts = array_merge(self::$toasts, $_SESSION['toasts'] ?? []);
    }

    static function clearStash()
    {
        unset($_SESSION['toasts']);
    }

    static function render()
    {
        self::popStash();
        $toasts = "";
        foreach (self::$toasts as $toast):
            $toasts .= <<<HTML
            <div class="toast show {$toast->level->value}" aria-live="polite" hx-on:animationend="htmx.remove(this)">
                $toast->message
            </div>
            HTML;
        endforeach;
        self::clearStash();
        return $toasts;
    }

    static function renderRoot()
    { ?>
        <div id="toast-root">
            <?= self::render() ?>
        </div>
    <?php }

    static function renderOob()
    { ?>
        <div id="toast-root" hx-swap-oob="afterbegin">
            <?= self::render() ?>
        </div>
    <?php }

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
    static function warning(string $message)
    {
        return self::create($message, ToastLevel::Warning);
    }
}

enum ToastLevel: string
{
    case Success = "success";
    case Error = "error";
    case Warning = "warning";
    case Info = "info";
}