<?php
/**
 * SRI plugin for Craft CMS
 *
 * SRI Service
 *
 * --snip--
 * All of your plugin’s business logic should go in services, including saving data, retrieving data, etc. They
 * provide APIs that your controllers, template variables, and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 * --snip--
 *
 * @author    Fred Carlsen
 * @copyright Copyright (c) 2016 Fred Carlsen
 * @link      http://sjelfull.no
 * @package   SRI
 * @since     1.0.0
 */

namespace Craft;

class SRIService extends BaseApplicationComponent
{
    protected $options;
    protected $hashCache;
    protected $hashCacheKey = 'sri_hash_cache';

    public function init ()
    {
        parent::init();

        $this->options = [
            'cache'         => true,
            'cacheDuration' => 3600
        ];

        $this->hashCache = craft()->cache->get($this->hashCacheKey);

        if ( !$this->hashCache ) {
            craft()->cache->set($this->hashCacheKey, [ ], $this->options['cacheDuration']);

            $this->hashCache = [ ];
        }
    }

    /**
     * This function can literally be anything you want, and you can have as many service functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     craft()->sRI->exampleService()
     */
    public function createSriForTag ($tag, $options = [ ])
    {
        $isScript     = strpos($tag, '<script') !== false;
        $isStylesheet = strpos($tag, '<link') !== false;

        // Detect type of asset
        if ( $isScript ) {
            $tag = $this->processScriptTag($tag);
        }
        elseif ( $isStylesheet ) {
            $tag = $this->processStylesheetTag($tag);
        }

        return TemplateHelper::getRaw($tag);
    }

    private function processScriptTag ($tag = false)
    {
        // Get url
        $output = $this->processTag($tag, $type = 'script');

        return $output;
    }

    private function processStylesheetTag ($tag = false)
    {
        // Get url
        $output = $this->processTag($tag, $type = 'stylesheet');

        return $output;
    }

    private function processTag ($tag, $type = 'script')
    {
        $dom = new \DOMDocument();
        $dom->loadHTML($tag, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Save
        $dom->saveHTML();

        $elements = $type === 'script' ? $dom->getElementsByTagName('script') : $dom->getElementsByTagName('link');

        if ( $elements->length > 0 && $elements->item(0) ) {
            $domElement = $elements->item(0);

            // Get file url
            $url = $type === 'script' ? $domElement->getAttribute('src') : $domElement->getAttribute('href');

            $sriAttributes = $this->attributesForTag($url);

            if ( $sriAttributes ) {

                // Add return attributes
                foreach ($sriAttributes as $key => $value) {
                    $domElement->setAttribute($key, $value);
                }
            }

            // Save
            $tag = $domElement->ownerDocument->saveHTML();
        }

        // Return unprocessed tag by default
        return $tag;
    }

    private function attributesForTag ($url)
    {
        $hash = $this->getCachedHash($url);

        if ( !$hash ) {

            // Get remote file
            $content = $this->getRemoteFile($url);

            if ( $content ) {
                // Generate hash
                $hash = $this->hashFile($content);

                // Cache hash for later
                $this->setCachedHash($url, $hash);
            }
        }

        if ( $hash ) {

            return [
                'crossorigin' => 'anonymous',
                'integrity'   => 'sha256-' . $hash,
            ];
        }

        return false;
    }

    private function getRemoteFile ($url)
    {
        $isRelative = 0 === strpos($url, '//');

        // Default to SSL version if protocol is relative
        $url = ($isRelative) ? 'https:' . $url : $url;

        $content = @file_get_contents($url);

        return $content;
    }

    public function hashFile ($content)
    {
        return base64_encode(hash('sha256', $content, true));
    }

    private function getCachedHash ($url)
    {
        $hash = array_key_exists($url, $this->hashCache) ? $this->hashCache[ $url ] : null;

        return $hash;
    }

    private function setCachedHash ($url, $hash)
    {
        $this->hashCache[ $url ] = $hash;

        $result = craft()->cache->set($this->hashCacheKey, $this->hashCache, $this->options['cacheDuration']);

        return $result;
    }

}