<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CurrentPathExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('current_path', [$this, 'currentPath'], ['is_safe' => ['html'], 'needs_context' => true])
        ];
    }

    public function currentPath(array $context, string $name): string
    {
        if (($context['menu'] ?? null) === $name) {
            return 'aria-current="page"';
        }

        return '';
    }
}