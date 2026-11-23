<?php
declare(strict_types=1);

namespace Redsys;

/** A form the payer's browser posts: to the TPV Virtual to pay (connection by redirection), or to the card issuer to pass a challenge. */
final readonly class RedirectForm
{
    /** @param array<string, string> $fields */
    public function __construct(public string $action, public array $fields)
    {
    }

    /** The form as HTML, every value escaped. With `$autoSubmit` the page sends it as soon as it loads. */
    public function html(bool $autoSubmit = true): string
    {
        $html = '<form id="redsys" action="' . self::escape($this->action) . '" method="POST">';
        foreach ($this->fields as $name => $value) {
            $html .= '<input type="hidden" name="' . self::escape($name) . '" value="' . self::escape($value) . '">';
        }
        $html .= '<noscript><input type="submit" value="Continue"></noscript></form>';
        return $autoSubmit ? $html . '<script>document.getElementById("redsys").submit();</script>' : $html;
    }

    private static function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
