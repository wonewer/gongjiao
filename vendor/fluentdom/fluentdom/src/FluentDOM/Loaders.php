<?php
/**
 * FluentDOM\Loaders is a list of loaders that allow to import data sources into
 * a DOM document.
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright Copyright (c) 2009-2014 Bastian Feder, Thomas Weinert
 */

namespace FluentDOM {

  use FluentDOM\Loader\Result;

  /**
   * FluentDOM\Loaders is a list of loaders that allow to import data sources into
   * a DOM document.
   *
   * The list is iterated until a valid document is returned by the loader
   *
   */
  class Loaders implements \IteratorAggregate, Loadable {

    private $_list = array();

    /**
     * Store the a list of loaders if provided.
     *
     * @param array|\Traversable $list
     */
    public function __construct($list = NULL) {
      if (is_array($list) || $list instanceOf \Traversable) {
        foreach ($list as $loader) {
          $this->add($loader);
        }
      }
    }

    /**
     * Add a loader to the list
     *
     * @param Loadable $loader
     */
    public function add(Loadable $loader) {
      $this->_list[spl_object_hash($loader)] = $loader;
    }

    /**
     * Remove a loader to the list
     *
     * @param Loadable $loader
     */
    public function remove($loader) {
      $key = spl_object_hash($loader);
      if (isset($this->_list[$key])) {
        unset($this->_list[$key]);
      }
    }

    /**
     * Allow to iterate all added loaders
     *
     * @return \Traversable
     */
    public function getIterator() {
      return new \ArrayIterator(array_values($this->_list));
    }

    /**
     * Validate if the list contains a loader that supports the given content type
     *
     * @param string $contentType
     * @return boolean
     */
    public function supports($contentType) {
      foreach ($this as $loader) {
        /**
         * @var Loadable $loader
         */
        if ($loader->supports($contentType)) {
          return TRUE;
        }
      }
      return FALSE;
    }

    /**
     * Load a data source, the content type allows the loader to decide if it supports
     * the data source
     *
     * @param mixed $source
     * @param string $contentType
     * @param array|\Traversable|Options $options
     * @return \DOMDocument|Result|NULL
     */
    public function load($source, $contentType, $options = []) {
      $dom = NULL;
      foreach ($this as $loader) {
        /**
         * @var Loadable $loader
         */
        if ($loader->supports($contentType) && ($dom = $loader->load($source, $contentType, $options))) {
          break;
        }
      }
      return ($dom instanceOf \DOMDocument || $dom instanceof Result) ? $dom : NULL;
    }

    /**
     * Load a data source as a fragment, the content type allows the loader to decide if it supports
     * the data source
     *
     * @param mixed $source
     * @param string $contentType
     * @param array|\Traversable|Options $options
     * @return \DOMDocumentFragment|NULL
     */
    public function loadFragment($source, $contentType, $options = []) {
      $fragment = NULL;
      foreach ($this as $loader) {
        /**
         * @var Loadable $loader
         */
        if (
          $loader->supports($contentType) &&
          ($fragment = $loader->loadFragment($source, $contentType, $options))
        ) {
          break;
        }
      }
      return ($fragment instanceOf \DOMDocumentFragment) ? $fragment : NULL;
    }
  }
}