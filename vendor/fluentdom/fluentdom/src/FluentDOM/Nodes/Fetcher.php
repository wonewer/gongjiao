<?php
/**
 * Fetches dom nodes for the current context.
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright Copyright (c) 2009-2014 Bastian Feder, Thomas Weinert
 */

namespace FluentDOM\Nodes {

  use FluentDOM\Constraints;
  use FluentDOM\Nodes;

  /*
   * Fetches dom nodes for the current context.
   */
  class Fetcher {

    /** reverse the order of the fetched nodes */
    const REVERSE = 1;
    /** include the node at the stop */
    const INCLUDE_STOP = 2;
    /** unique and sort nodes */
    const UNIQUE = 4;
    /** ignore the current context (use the document context) */
    const IGNORE_CONTEXT = 8;
    /** ignore the current context (use the document context) */
    const FORCE_SORT = 16;

    /**
     * @var Nodes
     */
    private $_nodes = NULL;

    /**
     * @param Nodes $nodes
     */
    public function __construct(Nodes $nodes) {
      $this->_nodes = $nodes;
    }

    /**
     * @param string $expression
     * @param callable $filter
     * @param callable $stopAt
     * @param int $options
     * @throws \InvalidArgumentException
     * @return array
     */
    public function fetch(
      $expression, callable $filter = NULL, callable $stopAt = NULL, $options = 0
    ) {
      if ($this->validateContextIgnore($expression, $options)) {
        $nodes = $this->fetchFor(
          $expression, NULL, $filter, $stopAt, $options
        );
      } else {
        $nodes = array();
        foreach ($this->_nodes->toArray() as $context) {
          $nodes = array_merge(
            $nodes,
            $this->fetchFor(
              $expression, $context, $filter, $stopAt, $options
            )
          );
        }
      }
      return $this->unique($nodes, $options);
    }

    /**
     * Validate if the context can be ignored.
     *
     * @param string $expression
     * @param int $options
     * @return bool
     */
    private function validateContextIgnore($expression, $options) {
      if (!is_string($expression) || empty($expression)) {
        throw new \InvalidArgumentException(
          'Invalid selector/expression.'
        );
      }
      return
        Constraints::hasOption($options, self::IGNORE_CONTEXT) ||
        (strpos($expression, '/') === 0);
    }

    /**
     * Make nodes unique if needed or forced.
     *
     * @param array $nodes
     * @param int $options
     * @return array
     */
    private function unique($nodes, $options) {
      if (
        Constraints::hasOption($options, self::FORCE_SORT) ||
        (count($this->_nodes) > 1 && Constraints::hasOption($options, self::UNIQUE))
      ) {
        $nodes = $this->_nodes->unique($nodes);
      }
      return $nodes;
    }

    /**
     * Fetch the nodes for the provided context node. If $context
     * ist NULL the document context is used. Use $filter and
     * $stopAt to reduce the returned nodes.
     *
     * @throws \InvalidArgumentException
     * @param string $expression
     * @param \DOMNode $context
     * @param callable $filter
     * @param callable $stopAt
     * @param int $options
     * @return array|bool|\DOMNodeList|float|string
     */
    private function fetchFor(
      $expression,
      \DOMNode $context = NULL,
      callable $filter = NULL,
      callable $stopAt = NULL,
      $options = 0
    ) {
      $nodes = $this->fetchNodes($expression, $context, $options);
      if ($filter || $stopAt) {
        return $this->filterNodes($nodes, $filter, $stopAt, $options);
      }
      return $nodes;
    }

    /**
     * Fetch the nodes for the provided context node. If $context
     * ist NULL the document context is used.
     *
     * @throws \InvalidArgumentException
     * @param string $expression
     * @param \DOMNode $context
     * @param int $options
     * @return array|bool|\DOMNodeList|float|string
     */
    private function fetchNodes($expression, \DOMNode $context = NULL, $options = 0) {
      $nodes = $this->_nodes->xpath($expression, $context);
      if (!$nodes instanceof \DOMNodeList) {
        throw new \InvalidArgumentException(
          'Given selector/expression did not return a node list.'
        );
      }
      $nodes = iterator_to_array($nodes);
      if (Constraints::hasOption($options, self::REVERSE)) {
        return array_reverse($nodes, FALSE);
      }
      return $nodes;
    }

    /**
     * @param array $nodes
     * @param callable $filter
     * @param callable $stopAt
     * @param int $options
     * @return array
     */
    private function filterNodes(
      array $nodes, callable $filter = NULL, callable $stopAt = NULL, $options = 0
    ) {
      $result = array();
      foreach ($nodes as $index => $node) {
        list($isFilter, $isStopAt) = $this->getNodeStatus(
          $node, $index, $filter, $stopAt
        );
        if ($isStopAt) {
          if (
            $isFilter &&
            Constraints::hasOption($options, self::INCLUDE_STOP)
          ) {
            $result[] = $node;
          }
          return $result;
        } elseif ($isFilter) {
          $result[] = $node;
        }
      }
      return $result;
    }

    /**
     * @param \DOMNode $node
     * @param int $index
     * @param callable $filter
     * @param callable $stopAt
     * @return boolean[]
     */
    private function getNodeStatus(
      \DOMNode $node, $index, callable $filter = NULL, callable $stopAt = NULL
    ) {
      return [
        empty($filter) || $filter($node, $index),
        !empty($stopAt) && $stopAt($node, $index)
      ];
    }
  }
}