<?php

class Sizr {

    const NO_IMAGE = 1;

    protected $directory = '.';
    protected $src;

    protected $origWidth;
    protected $origHeight;
    protected $origType;
    protected $origAspect;

    protected $width;
    protected $height;

    protected $calcWidth;
    protected $calcHeight;

    protected $imgOriginal;
    protected $imgResized;

    /**
     * Set up the Sizr instance.
     */
    public function __construct()
    {
        
    }

    /**
     * The path to the directory containing the images to be resized.
     * @param string $directory
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    /**
     * The path to the source image, relative to $this->directory.
     * @param string $src
     */
    public function setSrc($src)
    {
        $this->src = $src;
    }

    /**
     * Set the width of the output image.
     * 
     * Specify the desired width in pixels or null for automatic sizing based
     * on the image's height.
     * 
     * @param mixed $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * Set the height of the output image.
     * 
     * Specify the desired height in pixels or null for automatic sizing based
     * on the image's width.
     * 
     * @param mixed $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * Output the rendered image.
     */
    public function output()
    {

        // Get the filename and check it is valid
        $filename = $this->getFilename();
        if (!$filename || !file_exists($filename)) {
            trigger_error("Missing file {$filename}", E_USER_ERROR);
        }

        $this->loadOrigInfo($filename);
        $this->calcResizedDimensions();

        $this->createFromOriginal($filename);
        $this->createResized();

        $this->resize();

        $this->outputResized();

    }

    /**
     * Get the filename of the requested image. Returns FALSE if no filename
     * has been specified.
     * @return mixed
     */
    protected function getFilename()
    {
        if ($this->src == self::NO_IMAGE) {
            return false;
        }
        return $this->directory . $this->src;
    }

    /**
     * Load information about the original image.
     * @param string $filename
     */
    protected function loadOrigInfo($filename)
    {
        
        $fileInfo = getimagesize($filename);

        $this->origWidth  = $fileInfo[0];
        $this->origHeight = $fileInfo[1];
        $this->origType   = $fileInfo['mime'];
        $this->origAspect = $this->origWidth / $this->origHeight;

    }

    /**
     * Calculate the dimensions of the new image.
     */
    protected function calcResizedDimensions()
    {

        if ($this->width && $this->height) {

            // Both width and height are set

        } elseif ($this->width) {

            // Only width is set
            $this->calcWidth  = (int) $this->width;
            $this->calcHeight = (int) round($this->width / $this->origAspect);

        } elseif ($this->height) {

            // Only height is set
            $this->calcHeight = (int) $this->height;
            $this->calcWidth  = (int) round($this->height * $this->origAspect);
            
        }

    }

    /**
     * Create a GD image from the original.
     * @param string $filename
     */
    protected function createFromOriginal($filename)
    {

        // Get the input function
        switch ($this->origType) {
            case 'image/jpeg':
            case 'image/pjpeg':
                $func = 'imagecreatefromjpeg';
                break;
            case 'image/gif':
                $func = 'imagecreatefromgif';
                break;
            case 'image/png':
                $func = 'imagecreatefrompng';
                break;
        }

        $this->imgOriginal = $func($filename);

    }

    /**
     * Create the new image.
     */
    protected function createResized()
    {

        $this->imgResized = imagecreatetruecolor(
            $this->calcWidth,
            $this->calcHeight
        );

    }

    /**
     * Resize the original image into the resized image.
     */
    protected function resize()
    {

        imagecopyresampled(
            $this->imgResized, 
            $this->imgOriginal,
            0, 
            0,
            0,
            0,
            $this->calcWidth,
            $this->calcHeight,
            $this->origWidth,
            $this->origHeight
        );

    }

    /**
     * Output the resized image to a file or the browser.
     * @param string $outputFilename
     */
    protected function outputResized($outputFilename = null)
    {

        // Get the output function
        switch ($this->origType) {
            case 'image/jpeg':
            case 'image/pjpeg':
                $func = 'imagejpeg';
                break;
            case 'image/gif':
                $func = 'imagegif';
                break;
            case 'image/png':
                $func = 'imagepng';
                break;
        }

        if (!$outputFilename) {
            header('Content-Type: ' . $this->origType);
        }

        $func($this->imgResized, $outputFilename);

    }

}