<?php

class ToastItem
{
    public function __construct(
        public string $message = "",
        public ToastLevel $level = ToastLevel::Success,
    ) {}
}

class Toast
{
    /** @var ToastItem[] */
    public static array $toasts = [];
    public static function create($message, $level = ToastLevel::Success)
    {
        self::$toasts[] = new ToastItem($message, $level);
    }

    public static function stash()
    {
        $_SESSION['toasts'] = self::$toasts;
    }

    public static function popStash()
    {
        self::$toasts = array_merge(self::$toasts, $_SESSION['toasts'] ?? []);
    }

    public static function clearStash()
    {
        unset($_SESSION['toasts']);
    }

    public static function render()
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

    public static function renderRoot()
    { ?>
        <div id="toast-root">
            <?= self::render() ?>
        </div>
    <?php }

    public static function renderOob()
    { ?>
        <div id="toast-root" hx-swap-oob="afterbegin">
            <?= self::render() ?>
        </div>
    <?php }

    public static function error(string $message)
    {
        return self::create($message, ToastLevel::Error);
    }
    public static function success(string $message)
    {
        return self::create($message, ToastLevel::Success);
    }
    public static function info(string $message)
    {
        return self::create($message, ToastLevel::Info);
    }
    public static function warning(string $message)
    {
        return self::create($message, ToastLevel::Warning);
    }

    public static function fromResult(Result $r)
    {
        return $r->success ? self::success($r->print()) : self::error($r->print());
    }
}

enum ToastLevel: string
{
    case Success = "success";
    case Error = "error";
    case Warning = "warning";
    case Info = "info";
}
