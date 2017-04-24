<?php
/**
 * FluentDOM\Data is used for the FluentDOM::data property and FluentDOM::data() method, providing an
 * interface html5 data properties of a node.
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright Copyright (c) 2009-2014 Bastian Feder, Thomas Weinert
 */

namespace FluentDOM\Query {

  use FluentDOM\Query as Query;

  /**
   * FluentDOM\Data is used for the FluentDOM::data property and FluentDOM::data() method, providing an
   * interface html5 data properties of a node.
   */
  class Data implements \IteratorAggregate, \Countable {

    /**
     * Attached element node
     *
     * @var \DOMElement
     */
    private $_node = NULL;

    /**
     * Create object with attached element node.
     *
     * @param \DOMElement $node
     */
    public function __construct(\DOMElement $node) {
      $this->_node = $node;
    }

    /**
     * Convert data attributes into an array
     *
     * @return array
     */
    public function toArray() {
      $result = array();
      foreach ($this->_node->attributes as $attribute) {
        if ($this->isDataProperty($attribute->name)) {
          $result[$this->decodeName($attribute->name)] = $this->decodeValue($attribute->value);
        }
      }
      return $result;
    }

    /**
     * IteratorAggregate Interface: allow to iterate the data attributes
     *
     * @return \Iterator
     */
    public function getIterator() {
      return new \ArrayIterator($this->toArray());
    }

    /**
     * countable Interface: return the number of data attributes
     *
     * @return integer
     */
    public function count() {
      $result = 0;
      foreach ($this->_node->attributes as $attribute) {
        if ($this->isDataProperty($attribute->name)) {
          ++$result;
        }
      }
      return $result;
    }

    /**
     * Validate if the attached node has the data attribute.
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name) {
      return $this->_node->hasAttribute($this->encodeName($name));
    }

    /**
     * Change a data attribute on the attached node.
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value) {
      $this->_node->setAttribute($this->encodeName($name), $this->encodeValue($value));
    }

    /**
     * Read a data attribute from the attached node.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
      $name = $this->encodeName($name);
      if ($this->_node->hasAttribute($name)) {
        return $this->decodeValue($this->_node->getAttribute($name));
      }
      return NULL;
    }

    /**
     * Remove a data attribute from the attached node.
     *
     * @param string $name
     */
    public function __unset($name) {
      $this->_node->removeAttribute($this->encodeName($name));
    }

    /**
     * Validate if the given attribute name is a data property name
     *
     * @param string $name
     * @return bool
     */
    private function isDataProperty($name) {
      return (0 === strpos($name, 'data-') && $name === strtolower($name));
    }

    /**
     * Normalize a property name from camel case to lowercase with hyphens.
     *
     * @param string $name
     * @return string
     */
    private function encodeName($name) {
      if (preg_match('(^[a-z][a-z\d]*([A-Z]+[a-z\d]*)+$)DS', $name)) {
        $camelCasePattern = '((?:[a-z][a-z\d]+)|(?:[A-Z][a-z\d]+)|(?:[A-Z]+(?![a-z\d])))S';
        if (preg_match_all($camelCasePattern, $name, $matches)) {
          $name = implode('-', $matches[0]);
        }
      }
      return 'data-'.strToLower($name);
    }

    /**
     * Convert the given attribute name with hyphens to camel case.
     *
     * @param string $name
     * @return string
     */
    private function decodeName($name) {
      $parts = explode('-', strToLower(substr($name, 5)));
      $result = array_shift($parts);
      foreach ($parts as $part) {
        $result .= ucFirst($part);
      }
      return $result;
    }

    /**
     * Decode the attribute value into a php variable/array/object
     *
     * @param string $value
     * @return mixed
     */
    private function decodeValue($value) {
      switch (TRUE) {
      case ($value === 'true') :
        return TRUE;
      case ($value === 'false') :
        return FALSE;
      case (in_array(substr($value, 0, 1), array('{', '['))) :
        if ($json = json_decode($value)) {
          return $json;
        } else {
          return NULL;
        }
      default :
        return $value;
      }
    }

    /**
     * Encode php variable into a string. Array or Objects will be serialized using json encoding.
     * Boolean use the strings yes/no.
     *
     * @param mixed $value
     * @return string
     */
    private function encodeValue($value) {
      if (is_bool($value)) {
        return ($value) ? 'true' : 'false';
      } elseif (is_object($value) || is_array($value)) {
        return json_encode($value);
      } else {
        return (string)$value;
      }
    }
  }
}