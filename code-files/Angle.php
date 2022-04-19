<?php
class Angle {
    public $svg;
    public $angle;
    public $angles = [];
    public $lineLength = 100;
    public $height;
    public $width;
    public $originLeft;
    public $originTop;
    public $isDirty = false;
    public $showArc = false;
    public $showVertex = false;
    public $showLinePoints = false;
    public $showLineLabels = false;
    public $showAngle = false;
    public $arcLines = [0, 1];
    public $isAcute = false;
    public $isObtuse = false;
    public $isRight = false;

    private $uuid;

    public function __construct(...$angles) {
        $this->uuid = Ramsey\Uuid\Uuid::uuid4();

        foreach($angles as $angle) {
            $angle %= 360;
            if ($angle < 0) $angle += 360;

            $this->angles[] = $angle;
        }

        if (sizeof($angles) < 2) 
            $this->angles[] = 0;        
    }

    public function lineLength($lineLength = 100) {
        $this->lineLength = $lineLength;

        return $this;
    }

    public function showArc($val = [0, 1]) {
        $this->showArc = true;
        $this->arcLines = $val;

        return $this;
    }

    public function showVertex($val = true) {
        $this->showVertex = $val;

        return $this;
    }

    public function showPoints($val = true) {
        $this->showLinePoints = $val;

        return $this;
    }

    public function showLabels($val = true) {
        $this->showLineLabels = $val;

        return $this;
    }

    public function showAngle($val = true) {
        $this->showAngle = $val;

        return $this;
    }

    public function build() {   
        $this->setDimensions();
        $this->setAngleDetails();

        $lines = $this->getLines();
        $linePoints = $this->getLinePoints();
        $lineLabels = $this->getLineLabels();
        $arc = $this->getArc();
        $vertex = $this->getVertexPoint();
        $angle = $this->showAngle ? "<text x='" . ($this->originLeft + (($this->lineLength - 20) * .10)) . "' y='" . ($this->originTop + (($this->lineLength - 20) * .10)) . "'>{$this->angle}&deg;</text>" : '';

        $this->svg = "
            <svg width='{$this->width}px' height='{$this->height}px' xmlns='http://www.w3.org/2000/svg'>
                <defs>
                    <marker id='arrow_{$this->uuid}_end' markerWidth='10' markerHeight='10' refX='0' refY='3' orient='auto' markerUnits='strokeWidth'>
                        <path d='M0,0 L0,6 L9,3 z' fill='#000' />
                    </marker>

                    <circle id='point_{$this->uuid}' r='4' style='fill:#000; stroke:#000; stroke-width:2;' />
                </defs>

                {$linePoints}
                {$lineLabels}
                {$arc}
                {$angle}
                {$lines}
                {$vertex}
            </svg>";

        return $this;
    }

    private function setDimensions() {
        $left = $top = $right = $bottom = 0;

        foreach($this->angles as &$angle) {
            $rad = deg2rad($angle);

            $width = cos($rad) * $this->lineLength;
            $height = sin($rad) * $this->lineLength;

            if ($width > 0 && $width > $right)
                $right = $width;
            
            if ($width < 0 && $width < $left)
                $left = $width;

            if ($height > 0 && $height > $top)
                $top = $height;

            if ($height < 0 && $height < $bottom)
                $bottom = $height;
        }

        $padding = $this->showLineLabels ? 50 : 20;

        $this->width = (abs($left) + $right) + $padding;
        $this->height = (abs($bottom) + $top) + $padding; 

        $this->originLeft = abs($left) + $padding / 2;
        $this->originTop = abs($top) + $padding / 2; 
    }

    private function getLines() {
        $lines = '';
        $lineEnd = ($this->originLeft + $this->lineLength - 20);

        foreach ($this->angles as $angle) {
            $rotate = ($angle * -1);

            $lines .= "<line x1='{$this->originLeft}' y1='{$this->originTop}' x2='{$lineEnd}' y2='{$this->originTop}' stroke='#000' transform='rotate({$rotate}, {$this->originLeft}, {$this->originTop})' stroke-width='2' marker-end='url(#arrow_{$this->uuid}_end)' />";
            
        }

        return $lines;
    }

    private function getLinePoints() {
        if (!$this->showLinePoints)
            return '';

        $points = '';

        foreach ($this->angles as $angle) {
            $x = ($this->originLeft + ($this->lineLength - 20) * .85);
            $rotate = ($angle * -1);

            $points .= "<use xlink:href='#point_{$this->uuid}' x='{$x}' y='{$this->originTop}' transform='rotate({$rotate}, {$this->originLeft}, {$this->originTop})' />";
        }

        return $points;
    }
    
    private function getLineLabels() {
        if (!$this->showLineLabels)
            return '';

        $labels = '';
        $alphabet = new Alphabet();

        foreach ($this->angles as $i => $angle) {
            $size = ($this->lineLength);
            $x = $this->originLeft + 1.1 * (cos(deg2rad($angle)) * $size) + 0;
            $y = $this->originTop - 1.13 * (sin(deg2rad($angle)) * $size) - ((abs($angle) > 110) || ($angle < -45) ? 15 : 0);
            
            $labels .= "
                <foreignObject x='{$x}' y='{$y}' width='20px' height='20px')'>
                    <div xmlns='http://www.w3.org/1999/xhtml'>{$alphabet::$letters[$i]}</div>
                </foreignObject>";
            
        }

        return $labels;
    }

    private function getArc() {
        if (!$this->showArc)
            return '';

        $size = ($this->lineLength * .10);
        $angle1 = $this->angles[$this->arcLines[0]];
        $angle2 = $this->angles[$this->arcLines[1]];

        // angle is 90 or equiv
        if (abs($this->angle) == 90 || abs($this->angle) == 270) {
            $rotate = (($this->angle * -1) - $angle2);

            if ($this->angle == 90 || $this->angle == -270) {
                $y = $this->originTop;
            }

            if ($this->angle == 270 || $this->angle == -90) {
                $y = ($this->originTop - $size);
            }

            return "<rect width='{$size}' height='{$size}' x='{$this->originLeft}' y='{$y}' transform='rotate({$rotate}, {$this->originLeft}, {$this->originTop})' style='fill:#fff; stroke:#000; stroke-width:2;' />";
        }

        if ($this->angle < 90)
            $size *= 1.5;

        $x1 = $this->originLeft + cos(deg2rad($angle1)) * $size;
        $y1 = $this->originTop - sin(deg2rad($angle1)) * $size;

        $x2 = $this->originLeft + cos(deg2rad($angle2)) * $size;
        $y2 = $this->originTop - sin(deg2rad($angle2)) * $size;

        $largeArcFlag = $angle2 - $angle1 <= 180 ? "0" : "1";

        return "<path xmlns='http://www.w3.org/2000/svg' d='M{$x1},{$y1} A{$size} {$size}, 0, {$largeArcFlag}, 0, {$x2},{$y2}' fill='#fff' stroke='black' stroke-width='2'/>";
    }

    private function getVertexPoint() {
        if (!$this->showVertex)
            return '';

        return "<use xlink:href='#point_{$this->uuid}' x='{$this->originLeft}' y='{$this->originTop}' />";
    }

    private function getAngle($angles) {
        $angle1 = $this->angles[$angles[0]];
        $angle2 = $this->angles[$angles[1]];

        return $angle1 - $angle2;
    }

    private function setAngleDetails() {
        $this->angle = $this->getAngle($this->arcLines);

        if (in_array(abs($this->angle), [90, 270]))
            $this->isRight = true;

        if (abs($this->angle) < 90)
            $this->isAcute = true;

        if (abs($this->angle) > 90 && !in_array(abs($this->angle), [90, 270]))
            $this->isObtuse = true;
    }

    public function __toString() {
        return $this->svg;
    }
}