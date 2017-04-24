<?php
/**
 * Load a DOM document from a string or file that was the result of a
 * SimpleXMLElement encoded as JSON.
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright Copyright (c) 2009-2014 Bastian Feder, Thomas Weinert
 */

namespace FluentDOM\Loader\Json {

  use FluentDOM\Document;
  use FluentDOM\Element;
  use FluentDOM\Loadable;
  use FluentDOM\Loader\Result;
  use FluentDOM\Loader\Supports;

  /**
   * Load a DOM document from a string or file that was the result of a
   * SimpleXMLElement encoded as JSON.
   */
  class SimpleXML implements Loadable {

    use Supports\Json;

    const XMLNS = 'urn:carica-json-dom.2013';

    /**
     * Maximum recursions
     *
     * @var int
     */
    private $_recursions = 100;

    /**
     * @return string[]
     */
    public function getSupported() {
      return ['text/simplexml', 'text/simplexml+json', 'application/simplexml+json'];
    }

    /**
     * Load the json string into an DOMDocument
     *
     * @param mixed $source
     * @param string $contentType
     * @param array|\Traversable|Options $options
     * @return Document|Result|NULL
     */
    public function load($source, $contentType, $options = []) {
      if (FALSE !== ($json = $this->getJson($source, $contentType, $options))) {
        $document = new Document('1.0', 'UTF-8');
        $document->appendChild(
          $root = $document->createElementNS(self::XMLNS, 'json:json')
        );
        $this->transferTo($root, $json);
        return $document;
      }
      return NULL;
    }

    /**
     * @param \DOMNode|\DOMElement $node
     * @param mixed $json
     */
    protected function transferTo(\DOMNode $node, $json) {
      /** @var Document $dom */
      $dom = $node->ownerDocument ?: $node;
      if ($json instanceof \stdClass) {
        foreach ($json as $name => $data) {
          if ($name == '@attributes') {
            if ($data instanceof \stdClass) {
              foreach ($data as $attributeName => $attributeValue) {
                $node->setAttribute($attributeName, $this->getValueAsString($attributeValue));
              }
            }
          } else {
            $this->transferChildTo($node, $name, $data);
          }
        }
      } elseif (is_scalar($json)) {
        $node->appendChild(
          $dom->createTextNode($this->getValueAsString($json))
        );
      }
    }

    /**
     * @param \DOMNode $node
     * @param string $name
     * @param mixed $data
     * @return array
     */
    protected function transferChildTo(\DOMNode $node, $name, $data) {
      /** @var Document $dom */
      $dom = $node->ownerDocument ?: $node;
      if (!is_array($data)) {
        $data = [$data];
      }
      foreach ($data as $dataChild) {
        $child = $node->appendChild($dom->createElement($name));
        $this->transferTo($child, $dataChild);
      }
      return $data;
    }
  }
}