<?php

namespace ecstsy\EssentialsX\Utils;

use ecstsy\essentialsx\Utils\Utils;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class LanguageManager {

    private Config $config;
    private string $filePath;

    public function __construct(PluginBase $plugin, string $languageKey) {
        $pluginDataDir = $plugin->getDataFolder();
        $localeDir = $pluginDataDir . '/locale/';
        $this->filePath = $localeDir . $languageKey . '.yml';
        
        if (!file_exists($this->filePath)) {
            throw new \RuntimeException("Language file not found for language key '$languageKey' at: " . $this->filePath);
        }
        
        $this->config = Utils::getConfiguration($plugin, "/locale/" . $languageKey . ".yml");
    }

    public function get(string $key): string {
        return $this->config->get($key, "Translation not found: " . $key);
    }

    public function getNested(string $key): mixed {
        return $this->config->getNested($key, "Translation not found: " . $key);
    }
    
    public function reload(): void {
        $this->config->reload();
    }

    public function getAll(): array {
        return $this->config->getAll();
    }
    
    public function getFilePath(): string {
        return $this->filePath;
    }
}