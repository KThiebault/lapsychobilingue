<?php

declare(strict_types=1);

namespace App\Twig;

use Psr\Cache\CacheItemPoolInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AssetLoaderExtension extends AbstractExtension
{
    private ?array $manifestData = null;
    const CACHE_KEY = 'asset_manifest';

    public function __construct(
        private string $environment,
        private string $manifest,
        private CacheItemPoolInterface $cacheInterface
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('asset_loader', [$this, 'assetLoader'], ['is_safe' => ['html']])
        ];
    }

    public function assetLoader(string $entry):string
    {
        if ($this->environment === 'dev') {
            return $this->assetLoaderDev($entry);
        }

        return $this->assetLoaderProd($entry);
    }

    private function assetLoaderDev(string $entry):string
    {
        return <<<HTML
        <script type="module" src="http://localhost:3000/assets/{$entry}" defer></script>
HTML;
    }

    private function assetLoaderProd(string $entry):string
    {
        if ($this->manifestData === null) {
            $item = $this->cacheInterface->getItem(self::CACHE_KEY);

            if ($item->isHit()) {
                $this->manifestData = $item->get();
            } else {
                $this->manifestData = json_decode(file_get_contents($this->manifest), true);
                $item->set($this->manifestData);
                $this->cacheInterface->save($item);
            }
        }

        $file = $this->manifestData[$entry]['file'];
        $css = $this->manifestData[$entry]['css'] ?? [];
        $imports = $this->manifestData[$entry]['imports'] ?? [];

        $html = <<<HTML
        <script type="module" src="/assets/{$file}" defer></script>
HTML;

        foreach ($css as $cssFiles) {
            $html .= <<<HTML
        <link rel="stylesheet" media="screen" href="/assets/{$cssFiles}"/>
HTML;
        }

        foreach ($imports as $import) {
            $html .= <<<HTML
        <link rel="modulepreload"  href="/assets/{$import}"/>
HTML;
        }

        return $html;
    }
}