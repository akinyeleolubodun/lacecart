<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Pdf\Build\Font\TrueType\Table;

/**
 * POST table class
 *
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Post extends AbstractTable
{

    /**
     * Allowed properties
     * @var array
     */
    protected $allowed = [
        'italicAngle' => 0,
        'fixed'       => 0
    ];

    /**
     * Constructor
     *
     * Instantiate a TTF 'post' table object.
     *
     * @param  \Pop\Pdf\Build\Font\TrueType $font
     * @return Post
     */
    public function __construct(\Pop\Pdf\Build\Font\TrueType $font)
    {
        parent::__construct($this->allowed);

        $bytePos = $font->tableInfo['post']->offset + 4;

        $italicBytes       = $font->read($bytePos, 4);
        $this->italicAngle = $font->readFixed(16, 16, $italicBytes);

        $bytePos += 8;

        $ary = unpack('nfixed/', $font->read($bytePos, 2));
        $ary = $font->shiftToSigned($ary);
        $this->fixed = $ary['fixed'];
    }

}
