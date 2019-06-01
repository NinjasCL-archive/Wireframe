<?php

namespace wireframe;

/**
 * Container for View Placeholders
 * 
 * @version 0.0.3
 * @author Teppo Koivula <teppo.koivula@gmail.com>
 * @license Mozilla Public License v2.0 http://mozilla.org/MPL/2.0/
 */
class ViewPlaceholders {
    
    /**
     * The Page instance associated with current placeholders object
     *
     * @var ProcessWire\Page
     */
    private $page;
    
    /**
     * Directory containing view scripts
     *
     * @var string
     */
    private $scripts;

    /**
     * Template name
     *
     * Optional. If provided, this overrides the template of the Page instance.
     *
     * @var string|null
     */
    private $template;
    
    /**
     * View script extension
     *
     * @var string
     */
    private $ext;

    /**
     * Container for data
     *
     * @var array
     */
    private $data = [];
    
    /**
     * Constructor method
     * 
     * @param ProcessWire\Page $page
     * @param string $scripts_dir Scripts directory
     * @param string $ext Extension for view scripts
     * @param string|null $template Template name (optional)
     * @throws Exception if view scripts directory is missing or unreadable
     * @throws Exception if invalid format is used for view script extension
     * @throws Exception if invalid format is used for template name
     * @throws Exception if template view scripts directory is missing or unreadable
     */
    public function __construct(\ProcessWire\Page $page, string $scripts, string $ext, $template = null) {

        // Set page
        $this->page = $page;

        // Set, validate, and format view scripts directory
        if (!is_dir($scripts)) {
            throw new \Exception(sprintf(
                'Missing or unreadable view scripts directory: "%"',
                $scripts
            ));
        }
        if (strrpos($scripts, '/') !== 0) {
            $scripts .= "/";
        }
        $this->scripts = $scripts;

        // Set, validate, and format view script extension
        $this->ext = $ext;
        if (basename($this->ext) !== $this->ext) {
            throw new \Exception(sprintf(
                'View script extension does not match expected format: "%s".',
                $this->ext
            ));
        }
        if (strpos($this->ext, '.') !== 0) {
            $this->ext = '.' . $this->ext;
        }

        // Set and validate template
        $this->template = $template ?: $page->template;
        if ($this->template !== null && basename($this->template) != $this->template) {
            throw new \Exception(sprintf(
                'Template name does not match expected format: "%s".',
                $this->template
            ));
        }

    }
    
    /**
     * Generic getter method
     *
     * Return content from a named view placeholder, or markup generated by
     * rendering the page using a view script matching the placeholder name
     * 
     * @param string $key Name of a view placeholder or view script
     * @return mixed Content stored in a view placeholder, or rendered output of a view script
     */
    public function __get(string $key) {
        $return = $this->data[$key] ?? null;
        if (is_null($return) && basename($key) === $key) {
            if (is_file($this->scripts . $this->template . '/' . $key . $this->ext)) {
                $page_layout = $this->page->layout();
                $page_view = $this->page->view();
                $this->page->_wireframe_context = 'placeholder';
                $return = $this->page->layout('')->view($key)->render();
                unset($this->page->_wireframe_context);
                if ($page_layout !== '') {
                    $this->page->layout($page_layout);
                }
                if ($page_view !== $key) {
                    $this->page->view($page_view);
                }
            }
        }
        return $return;
    }

    /**
     * Store values to the protected $data array
     * 
     * @param string $key Name of a view placeholder
     * @param mixed $value Value to store in a view placeholder
     * @return ViewPlaceholders Current instance
     */
    public function __set(string $key, $value): ViewPlaceholders {
        $this->data[$key] = $value;
        return $this;
    }
    
}