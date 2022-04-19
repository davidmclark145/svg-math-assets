<?php
abstract class Svg {
    use Css;

    protected $width = 180;
    protected $height = 180;
    protected $xAxisFlip = 1;
    protected $yAxisFlip = 1;
    protected $rotation = 0;
    protected $columnWidth = 10;
    protected $rowHeight = 10;
    protected $ttsDescription = 'shape';
    protected $fillColor = '#6BB3FA';
    protected $fillOpacity;
    protected $strokeColor = '#000';
    protected $gridStrokeColor = '#666';
    protected $gridWidth;
    protected $gridHeight;
    protected $strokeWidth = 1;
    protected $scale = 1;
    protected $viewBox = '0 0 180 180';
    protected $svg;
    protected $uuid;

    public function __construct() {
        $this->uuid = wrapUnique(Ramsey\Uuid\Uuid::uuid4());
    }

    public function width($width = 180) {
        $this->width = $width;

        return $this;
    }

    public function height($height = 180) {
        $this->height = $height;

        return $this;
    }

    public function flip() {
        $this->xAxisFlip = (mt_rand(1, 2) == 1) ? -1 : 1;
        $this->yAxisFlip = (mt_rand(1, 2) == 1) ? -1 : 1;

        return $this;
    }

    public function rotate($rotation = null) {
        if (!$rotation) $rotation = mt_rand(0, 3) * 90;
        
        $this->rotation = $rotation;
        
        return $this;
    }

    public function fillColor($fillColor = '#6BB3FA') {
        $this->fillColor = $fillColor;

        return $this;
    }

    public function fillOpacity($fillOpacity = .75) {
        $this->fillOpacity = $fillOpacity;

        return $this;
    }

    public function strokeColor($strokeColor = '#000') {
        $this->strokeColor = $strokeColor;

        return $this;
    }

    public function strokeWidth($strokeWidth = 1) {
        $this->strokeWidth = $strokeWidth;

        return $this;
    }

    public function scale($scale = 1) {
        $this->scale = $scale;

        return $this;
    }

    public function viewBox($viewBox = '0 0 180 180') {
        $this->viewBox = $viewBox;

        return $this;
    }

    public function __toString() {
        return $this->svg;
    }

    protected function calculateWidth() {
        return $this->width * $this->scale;
    }

    protected function calculateHeight() {
        return $this->height * $this->scale;
    }

    protected function getGridDefs() {
        return "<pattern id='grid_{$this->uuid}' width='{$this->columnWidth}' height='{$this->rowHeight}' patternUnits='userSpaceOnUse'>
            <path d='M{$this->columnWidth},0 L0,0 0,{$this->rowHeight}' fill='none' stroke='{$this->gridStrokeColor}' stroke-width='{$this->strokeWidth}' />
        </pattern>";
    }

    protected function getGridLines() {
        $gridWidth = !empty($this->gridWidth) ? $this->gridWidth : $this->calculateWidth();
        $gridHeight = !empty($this->gridHeight) ? $this->gridHeight : $this->calculateHeight();

        return "<rect width='{$gridWidth}' height='{$gridHeight}' x='0' y='0' fill='url(#grid_{$this->uuid})' stroke='{$this->gridStrokeColor}' stroke-width='{$this->strokeWidth}' />";
    }

    protected function getTts() {
        return $this->ttsDescription;
    }
}
