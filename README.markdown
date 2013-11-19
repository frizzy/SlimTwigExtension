# Slim Framework Twig Extension

## Usage

Add the following to your root composer.json file:

    {
        "require": {
            "frizzy/twig-slim": "0.*"
        }
    }

Add the extension to your Twig environment:
    
    <?php
    
    $twig = new \Twig_Environment(new \Twig_Loader_Filesystem(...));
    
    $twig->addExtension(new \Frizzy\Slim\Twig\Extension);
    
    ?>
    
Functions available in Twig:

* render_route_name
* render_route_path
* render_template
* path
* url