<?php
class Circle extends Svg {
    protected $rotation = 0;
    protected $ttsDescription = 'circle';
    protected $strokeWidth = .5;
    private $partitions;
    private $shadedCount;
    private $fillSequence = [];

    public function partitions($partitions = 3) {
        $this->partitions = $partitions;
        $this->rotation = -90;

        return $this;
    }

    // set $shadingSequence to 'rand' to randomize position of shaded partition
    public function shade($shadedCount, $shadingSequence = 'clockwise') {
        $this->shadedCount = $shadedCount;

        $this->fillSequence = array_fill(0, $this->shadedCount, $this->fillColor);
        $this->fillSequence = array_pad($this->fillSequence, $this->partitions, '#fff');

        if ($shadingSequence == 'rand') shuffle($this->fillSequence);

        return $this;
    }

    public function build() {
        $circle = (!$this->partitions) ? $this->buildCircle($this->radiusX(), $this->radiusY()) : '';
        $partitionedCircle = ($this->partitions) ? $this->getPartitionPaths() : '';

        $this->svg = "<div class='svg-container' speech='{$this->getTts()}' style='{$this->getCss(['width' => "{$this->calculateWidth()}px", 'height' => "{$this->calculateHeight()}px"])}'>
            <svg width='{$this->calculateWidth()}' height='{$this->calculateHeight()}' transform='rotate({$this->rotation})' xmlns='http://www.w3.org/2000/svg'>

                {$circle}
                {$partitionedCircle}
            </svg>
        </div>";

        return $this;
    }

    protected function buildCircle($radiusX, $radiusY) {
        return "<ellipse cx='" . ($this->calculateWidth() / 2) . "' cy='" . ($this->calculateHeight() / 2) . "' rx='{$radiusX}' ry='{$radiusY}' fill='{$this->fillColor}' stroke='{$this->strokeColor}' stroke-width='" . ($this->strokeWidth * $this->scale) . "' />";
    }
    
    protected function radiusX() {
        return ($this->calculateWidth() / 2) - (10 * $this->scale);
    }

    protected function radiusY() {
        return ($this->calculateHeight() / 2) - (10 * $this->scale);
    }

    protected function getPercentCoords($percent, $radiusX, $radiusY) {
        $x = cos(2 * pi() * $percent) * $radiusX;
        $y = sin(2 * pi() * $percent) * $radiusY;

        return [($x + $this->radiusX() + (10 * $this->scale)), ($y + $this->radiusY() + (10 * $this->scale))];
    }

    private function getPartitionPaths() {
        $partitionPaths = '';

        $percent = 1 / $this->partitions;
        $cumulativePercent = 0;

        for ($i = 0; $i < $this->partitions; $i++) {
            list($startX, $startY) = $this->getPercentCoords($cumulativePercent, $this->radiusX(), $this->radiusY());

            $cumulativePercent += $percent;

            list($endX, $endY) = $this->getPercentCoords($cumulativePercent, $this->radiusX(), $this->radiusY());

            $fillColor = ($this->shadedCount) ? $this->fillSequence[$i] : $this->fillColor;
            $partitionPaths .= "<path d='M{$startX},{$startY} A{$this->radiusX()},{$this->radiusY()} 0,0,1 {$endX},{$endY} L" . ($this->radiusX() + (10 * $this->scale)) . "," . ($this->radiusY() + (10 * $this->scale)) . " z' fill='" . wrapUnique($fillColor) . "' stroke='{$this->strokeColor}' stroke-width='{$this->strokeWidth}'></path>";
        }

        return "<g data-shaded-count='{$this->shadedCount}'>$partitionPaths</g>";
    }
}
