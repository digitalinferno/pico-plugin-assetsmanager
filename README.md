# Assets Manager
Manage extra assets for your theme, including external files and inline scripts. Pico is a stupidly simple, blazing fast, flat file CMS. See https://picocms.org/ for more info.

This plugin add a custom trigger event `onAssetsLoading` that allows you to easily add CSS or JS files in the haed or footer section of your theme.

## Install

If you're using one of [Pico's pre-built release packages](https://github.com/picocms/Pico/releases/latest), you need to first create an empty `plugins/AssetsManager` directory in Pico's installation directory on your server. Next, download the latest source package of `AssetsManager` and upload `AssetsManager.php` into the aforementioned `plugins/AssetsManager` directory.

That's all.

## Assets Manager for theme designer

This plugin returns 3 twig variables which must be placed in the correct sections of your theme: `css_head`, `js_head`, and `js_footer`.

```twig
<!-- After your meta section -->

{% block css_head %}
    {{ css_head|raw }}
{% endblock %}

{% block js_head %}
    {{ js_head|raw }}
{% endblock %}

</head>
```
```twig
<!-- Footer section -->

{% block footer %}
    {{ js_footer|raw }}
{% endblock %}

</body>    
</html>
```

## Assets Manager for developers

In your plugin, don't forget to declare the following dependency:
```php
protected $dependsOn = array('AssetsManager');
``` 

After `onThemeLoaded`, you can trigger `onAssetsLoading` and add your assets to the `$assets` array.

```php
$assets[] = [
  'source' => "", // Asset URL or inline code
  'priority' => 50, // Higher numbers mean higher priority [ 1 - 100 ]
  'group' => 'head', // Where to include the asset [ head | footer ]
  'type' => 'css' // Explicit asset type [ css | js | inline ]
  'defer' => 'true' // If applicable, adds the `defer` attribute to scripts
  'async' => 'true' // If applicable, adds the `async` attribute to scripts  
];
```
Here a small working exemple:
```php
/**
 * Triggered after the theme has been loaded and assets can be registered
 *
 * @see    AssetsManager::onThemeLoaded()
 * @param  array &$assets list of assets to be registered
 * @return void
 */
public function onAssetsLoading(array &$assets)
{
    // CSS inside the plugin dir
    $assets[] = [
        'source' => basename(__DIR__)."/styles/zero.css",
        'priority' => 50,
        'group' => 'head',
        'type' => 'css'
    ];

    // JS with absolute path
    $assets[] = [
        'source' => "https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js",
        'priority' => 50,
        'group' => 'footer',
        'type' => 'js'
    ];

    // JS inline
    $assets[] = [
        'source' => 'alert("JS inline code loaded");',
        'priority' => 50,
        'group' => 'head',
        'type' => 'inline'
    ];
}
```
