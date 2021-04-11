<?php

declare(strict_types=1);

namespace carmelosantana\RoKMonster;

use DirectoryIterator;

class Templates
{
    const TITLE = 'TITLE';
    
    public array $allowed_image_types = [
        'png',
        'jpg',
        'jpeg',
        'gif',
    ];

    private string $dir;

    private array $templates = [];

    public function __construct($dir = null)
    {
        
        $this->dir = $dir;

        $this->load_templates($dir);

        return $this;
    }

    public function get($template = null)
    {
        if ($template)
            return $this->templates[$template] ?? false;

        return $this->templates;
    }


    private function load_templates()
    {
        if (!is_dir($this->dir)) {
            $this->templates = [];
            return false;
        }

        foreach (new DirectoryIterator($this->dir) as $di) {
            if ($di->isDot() or $di->isLink()) continue;

            if (is_dir($di->getPathname())) {
                $template_file = $di->getPathname() . DIRECTORY_SEPARATOR . $di->getBasename() . '.json';
                $template_sample = $di->getPathname() . DIRECTORY_SEPARATOR . 'sample';

                // template filename must match parent folder name
                if (!is_file($template_file))
                    continue;

                // perform checks before adding
                $tmp = json_decode(file_get_contents($template_file), true);

                // check if sample is defined and exists
                if (isset($tmp['sample']) and TinyCLI::is_enabled($tmp['sample'])) {
                    if (!$tmp['sample'] = $this->check_allowed_image_types($template_sample))
                        continue;
                }

                // if passed checks, add to available templates
                $this->templates[$di->getBasename()] = $tmp;
            }
        }
    }

    private function check_allowed_image_types($base = null)
    {
        foreach ($this->allowed_image_types as $ext) {
            $image = $base . '.' . $ext;
            if (is_file($image))
                return $image;
        }

        return false;
    }
}
