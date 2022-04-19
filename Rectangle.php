<?php
class Rectangle extends Svg {
    protected $width = 240;
    protected $height = 120;
    protected $columnWidth = 80;
    protected $rowHeight = 40;
    protected $ttsDescription = 'rectangle';
    protected $gridStrokeColor = '#000';
    private $rowCount;
    private $columnCount;
    private $shadedCount;
    private $shadingSequence = 'sequential';
    private $fillPosition;
    private $fillSequence = [];
    private $partitionWidth;
    private $partitionHeight;

    public function rows($rowCount = 3) {
        $this->rowCount = $rowCount;

        return $this;
    }

    public function columns($columnCount = 3) {
        $this->columnCount = $columnCount;

        return $this;
    }

    // set $shadingSequence to 'rand' to randomize position of shaded partition
    public function shade($shadedCount, $shadingSequence = 'sequential') {
        $this->shadedCount = $shadedCount;
        $this->shadingSequence = $shadingSequence;

        return $this;
    }

    public function build() {
        $this->columnWidth = ($this->columnCount) ? $this->calculateWidth() / $this->columnCount : $this->calculateWidth();
        $this->rowHeight = ($this->rowCount) ? $this->calculateHeight() / $this->rowCount : $this->calculateHeight();

        $shadedMask = ($this->shadedCount) ? $this->getShadedMask() : '';
        $shadedRect = ($this->shadedCount) ? $this->getShadedRect() : '';

        $fillColor = ($this->shadedCount) ? '#fff' : $this->fillColor;
        
        $grid = ($this->rowCount || $this->columnCount) ? $this->getGridLines() : '';

        $this->svg = "<div class='svg-container' speech='{$this->getTts()}' style='{$this->getCss(['width' => "{$this->calculateWidth()}px", 'height' => "{$this->calculateHeight()}px"])}'>
            <svg width='{$this->calculateWidth()}' height='{$this->calculateHeight()}' xmlns='http://www.w3.org/2000/svg'>
                <defs>
                    {$this->getGridDefs()}
                    {$shadedMask}
                </defs>

                <rect width='{$this->calculateWidth()}' height='{$this->calculateHeight()}' x='0' y='0' fill='{$fillColor}' stroke='{$this->gridStrokeColor}' stroke-width='{$this->strokeWidth}' />

                {$shadedRect}
                {$grid}
            </svg>
        </div>";

        return $this;
    }

    private function getShadedMask() {
        $shadedDefs = "<mask id='mask_{$this->uuid}' data-shaded-count='{$this->shadedCount}'>";

        $rectRows = [];
        $this->fillPosition = 0;

        $this->rowCount = ($this->rowCount) ? $this->rowCount : 1;
        $this->columnCount = ($this->columnCount) ? $this->columnCount : 1;

        $this->partitionWidth = ($this->columnCount == 1) ? $this->calculateWidth() : $this->calculateWidth() / $this->columnCount;
        $this->partitionHeight = ($this->rowCount == 1) ? $this->calculateHeight() : $this->calculateHeight() / $this->rowCount;

        $this->fillSequence = array_fill(0, $this->shadedCount, '#fff');
        $this->fillSequence = array_pad($this->fillSequence, $this->rowCount * $this->columnCount, '#000');

        if ($this->shadingSequence == 'rand') shuffle($this->fillSequence);

        $x = $y = 0;

        for ($i = 0; $i < $this->rowCount; $i++) {
            $y = ($this->rowCount == 1) ? $y : $i * $this->partitionHeight;

            $rectRows[] = $this->buildRow($x, $y);
        }

        foreach($rectRows as $rectRow) {
            $shadedDefs .= $rectRow;
        }

        return $shadedDefs . "</mask>";
    }

    private function buildRow($x, $y) {
        $rowRects = '';

        for ($i = 0; $i < $this->columnCount; $i++) {
            $x = (!$this->columnCount) ? $x : $i * $this->partitionWidth;

            $rowRects .= "<rect width='{$this->partitionWidth}' height='{$this->partitionHeight}' x='{$x}' y='{$y}' fill='" . wrapUnique($this->fillSequence[$this->fillPosition]) . "' stroke='none' />";
            $this->fillPosition++;
        }

        return $rowRects;
    }

    private function getShadedRect() {
        return "<rect width='{$this->calculateWidth()}' height='{$this->calculateHeight()}' x='0' y='0' fill='{$this->fillColor}' stroke='none' mask='url(#mask_{$this->uuid})' />";
    }
}
