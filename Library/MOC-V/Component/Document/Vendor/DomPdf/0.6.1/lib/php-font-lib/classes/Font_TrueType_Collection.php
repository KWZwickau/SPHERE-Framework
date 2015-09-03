<?php
/**
 * @package php-font-lib
 * @link    https://github.com/PhenX/php-font-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

require_once dirname(__FILE__)."/Font_Binary_Stream.php";
require_once dirname(__FILE__)."/Font_TrueType.php";

/**
 * TrueType collection font file.
 *
 * @package php-font-lib
 */
class Font_TrueType_Collection extends Font_Binary_Stream implements Iterator, Countable
{

    protected $collectionOffsets = array();
    protected $collection = array();
    protected $version;
    protected $numFonts;
    /**
     * Current iterator position.
     *
     * @var integer
     */
    private $position = 0;

    function current()
    {

        return $this->getFont($this->position);
    }

    /**
     * @param int $fontId
     *
     * @throws OutOfBoundsException
     * @return Font_TrueType
     */
    function getFont($fontId)
    {

        $this->parse();

        if (!isset( $this->collectionOffsets[$fontId] )) {
            throw new OutOfBoundsException();
        }

        if (isset( $this->collection[$fontId] )) {
            return $this->collection[$fontId];
        }

        $font = new Font_TrueType();
        $font->f = $this->f;
        $font->setTableOffset($this->collectionOffsets[$fontId]);

        return $this->collection[$fontId] = $font;
    }

    function parse()
    {

        if (isset( $this->numFonts )) {
            return;
        }

        $this->read(4); // tag name

        $this->version = $this->readFixed();
        $this->numFonts = $this->readUInt32();

        for ($i = 0; $i < $this->numFonts; $i++) {
            $this->collectionOffsets[] = $this->readUInt32();
        }
    }

    function key()
    {

        return $this->position;
    }

    function next()
    {

        return ++$this->position;
    }

    function rewind()
    {

        $this->position = 0;
    }

    function valid()
    {

        $this->parse();
        return isset( $this->collectionOffsets[$this->position] );
    }

    function count()
    {

        $this->parse();
        return $this->numFonts;
    }
}
