<?php

namespace App;


class Config
{

    private $config = null;
    /**
     * Get a key or the whole config file 'site.settings.conf'
     *
     * @param string|null $key
     * @return mixed
     */
    public function __invoke(string $key = null)
    {
        if (! $this->config) {
            $filename = 'config/site.settings.conf';

            $file = file_get_contents($filename, true);
            if (!$file) {
                new Log("FATAL ERROR: Config file '{$filename}' not found!");

                header('HTTP/1.1 500 Internal Server Error');
                echo
                "<h1>There was a fatal error</h1>
                Please let us know at <a href='mailto:contact@bodyologists.com'>contact@bodyologists.com</a><br>
                <br>
                Thank you.";

                die;
            }

            try {
                $this->config = json_decode($file, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $exception) {
                return null;
            }
        }

        if ($key) {
            return $this->config[$key] ?? null;
        }

        return $this->config;
    }
}