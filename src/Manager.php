<?php declare(strict_types=1);
/**
 * Shortcodes management library
 * 
 * @package Gvre.Shortcodes
 * @version 1.0
 * @author Giannis Vrentzos <gvre@gvre.gr>
 * @licence http://opensource.org/licenses/MIT MIT
 * 
 */

namespace Gvre\Shortcodes;

class Manager
{
    /**
     * @var $shortcodes 
     */
    private $shortcodes = [];

    /**
     * Parses attributes string and returns them in an array.
     * Attributes must have key="value" format.
     * Multiple attributes must be separated by space.
     *
     * @static
     *
     * @param string $attributes
     *
     * @return array
     */
    private static function parseAttributes(string $attributes): array
    {
        $attributes = trim($attributes);
        if (!preg_match_all('#(\w+)="([^"]+)"#s', $attributes, $matches, PREG_SET_ORDER))
            return [];

        $res = [];
        foreach($matches as $m)
            $res[$m[1]] = $m[2];
        return $res;
    }

    /**
     * Adds a shortcode.
     * 
     * @param string $name Shortcode name.
     * @param callable|array A callable function or an array with 2 elements (class, method).
     *
     * @return \Gvre\Shortcodes\Manager Current instance.
     */
    public function add(string $name, $callback): self
    {
        $this->shortcodes[$name] = $callback;
        return $this;
    }

    /**
     * Removes the $name shortcode.
     *
     * @param string $name
     *
     * @return \Gvre\Shortcodes\Manager Current instance.
     */
    public function remove(string $name): self
    {
        unset($this->shortcodes[$name]);
        return $this;
    }

    /**
     * Removes all shortcodes
     *
     * @return \Gvre\Shortcodes\Manager Current instance.
     */
    public function removeAll(): self
    {
        $this->shortcodes = [];
        return $this;
    }

    /**
     * Checks if shortcode exists.
     *
     * @param tring $name
     *
     * @return bool
     */
    public function exists(string $name): bool
    {
        return isset($this->shortcodes[$name]);
    }

    /**
     * Parses the string $str and executes the shortcodes it contains.
     * Skips escaped shortcodes (e.g. [[shortcode_name]])
     *
     * @param string $str
     *
     * @return string
     *
     */
    public function execute(string $str): string
    {
        if (strpos($str, '[') === false)
            return $str;

        // Parse shortcodes with closing tag (e.g. [shortcode][/shortcode])
        if (preg_match_all('#\[\[?(\w+)([^\]]*)\](.+?)\[/\1\]\]?#si', $str, $matches, PREG_SET_ORDER)) {
            foreach($matches as $m) {
                $fullmatch = $m[0];
                $name = $m[1];
                if (substr($fullmatch, 0, 2) == '[[' || !$this->exists($name))
                    continue;

                $attributes = self::parseAttributes($m[2]);
                $attributes['content'] = $m[3];
                $str = str_replace($m[0], $this->shortcodes[$name]($attributes), $str); 
            }
        }
        
        // Parse shortcodes without closing tag (e.g. [shortcode])
        if (preg_match_all('#\[\[?(\w+)([^\]]*)\]\]?#si', $str, $matches, PREG_SET_ORDER)) {
            foreach($matches as $m) {
                $fullmatch = $m[0];
                $name = $m[1];
                if (substr($fullmatch, 0, 2) == '[[' || !$this->exists($name))
                    continue;

                $attributes = self::parseAttributes($m[2]);
                $str = str_replace($m[0], $this->shortcodes[$name]($attributes), $str);
            }
        }

        return $str;
    }
}

