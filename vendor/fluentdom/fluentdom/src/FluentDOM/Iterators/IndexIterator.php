<?php
/**
 * A abstract superclass for index based iterators. The object
 * using this iterator needs to implement \Countable and
 * allow to get the current item by an zero based index position.
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright Copyright (c) 2009-2014 Bastian Feder, Thomas Weinert
 */
namespace FluentDOM\Iterators {

  /**
 * A abstract superclass for index based iterators. The object
 * using this iterator needs to implement \Countable and
 * allow to get the current item by an zero based index position.
   */
  abstract class IndexIterator implements \SeekableIterator {

    /**
     * internal position pointer variable
     * @var integer
     */
    protected $_position  = 0;

    /**
     * owner (object) of the iterator
     * @var \Countable
     */
    private $_owner = NULL;

    /**
     * @param \Countable $owner
     */
    public function __construct(\Countable $owner) {
      $this->_owner = $owner;
    }

    /**
     * Return the owner object
     *
     * @return \Countable
     */
    protected function getOwner() {
      return $this->_owner;
    }

    /**
     * Get current iterator pointer
     *
     * @return integer
     */
    public function key() {
      return $this->_position;
    }

    /**
     * Move iterator pointer to next element
     *
     * @return void
     */
    public function next() {
      ++$this->_position;
    }

    /**
     * Reset iterator pointer
     */
    public function rewind() {
      $this->_position = 0;
    }

    /**
     * Move iterator pointer to specified element
     *
     * @param integer $position
     * @throws \InvalidArgumentException
     */
    public function seek($position) {
      if ($this->getOwner()->count() > $position) {
        $this->_position = $position;
      } else {
        throw new \InvalidArgumentException(
          sprintf(
            'Unknown position %d, only %d items',
            $position, $this->getOwner()->count()
          )
        );
      }
    }
  }
}
