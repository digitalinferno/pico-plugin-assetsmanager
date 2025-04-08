<?php
/**
 * AssetsManager for Pico CMS.
 *
 * Manage additional assets in your Pico site, including external files and
 * inline scripts, in a structured and centralized way.
 * 
 * @author  Giovanni Forte <giovanni@digitalinfenro.it>
 * @link    https://digitalinferno.it
 * @license https://opensource.org/licenses/MIT The MIT License
 * @version 0.2
 */
class AssetsManager extends AbstractPicoPlugin
{
    const API_VERSION = 3;

    protected $enabled = true;

    public function onThemeLoaded($theme, $themeApiVersion, array &$themeConfig)
    {
        $assets = [];
        
        // Trigger the event, letting all plugins modify $assets
        $this->pico->triggerEvent('onAssetsLoading', [&$assets]);

        // Register all assets collected
        foreach ($assets as $asset) {
            $this->registerAsset($asset);
        }
    }

    /**
     * Register an asset
     */
    protected function registerAsset(array $asset)
    {
        $type     = $asset['type'] ?? 'inline'; // Explicit asset type [ css | js | inline ]
        $group    = $asset['group'] ?? 'head'; // Where to include the asset [ head | footer ]
        $source   = $asset['source'] ?? null; // Asset URL or inline code
        $priority = $asset['priority'] ?? 50; // Higher numbers mean higher priority [ 1 - 100 ]
        $defer    = $asset['defer'] ?? false; // If applicable, adds the `defer` attribute to scripts
        $async    = $asset['async'] ?? false; // If applicable, adds the `async` attribute to scripts    
        
        // Searching for local path 
        if ($type != 'inline' && !filter_var($source, FILTER_VALIDATE_URL)) {
            $source = $this->pico->getBaseUrl() . 'plugins/' . $source;
        }
        
        // Register the asset into the appropriate group
        $this->assets[$type][$group][] = [
            'source'   => $source,
            'priority' => $priority,
            'defer'    => $defer,
            'async'    => $async
        ];
    }

    /**
     * Retrieve and format the assets for a specific type and group.
     */
    public function getAssets(string $type, string $group): string
    {
        if (!isset($this->assets[$type][$group])) {
            return '';
        }

        // Sort by priority
        usort($this->assets[$type][$group], function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });

        $output = '';
        foreach ($this->assets[$type][$group] as $asset) {
            // Build HTML output
            if ($type === 'css') {
                $output .= '<link rel="stylesheet" href="' . htmlspecialchars($asset['source'], ENT_QUOTES, 'UTF-8') . '">';
            } elseif ($type === 'js') {
                $deferAttr = $asset['defer'] ? ' defer' : '';
                $asyncAttr = $asset['async'] ? ' async' : '';
                $output .= '<script src="' . htmlspecialchars($asset['source'], ENT_QUOTES, 'UTF-8') . '"' . $deferAttr . $asyncAttr . '></script>';
            } elseif ($type === 'inline') {
                $output .= '<script>' . $asset['source'] . '</script>';
            }
        }

        return $output;
    }

    public function onPageRendering(&$templateName, array &$twigVariables)
    {
        $twigVariables['css_head'] = $this->getAssets('css', 'head');
        $twigVariables['js_head'] = $this->getAssets('js', 'head') . $this->getAssets('inline', 'head');
        $twigVariables['js_footer'] = $this->getAssets('js', 'footer') . $this->getAssets('inline', 'footer');
    }
}
?>
