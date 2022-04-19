<?php
// https://print-graph-paper.com/virtual-graph-paper
class Shape extends Svg {
    protected $gridWidth = 180;
    protected $gridHeight = 180;
    private $shapes;
    private $shapeProps = [];
    private $shape;
    private $filteredShapes = [];
    private $showGrid = false;
    private $showLineOfSymmetry;
    private $lineSymmetry;
    private $casts = [
        'linesOfSymmetry' => 'integer',
        'parallelSets' => 'integer',
        'perpendicularSets' => 'integer',
        'rightAngles' => 'integer',
        'obtuseAngles' => 'integer',
        'acuteAngles' => 'integer',
        'gridFriendly' => 'boolean',
        'type' => 'array',
        'subType' => 'array',
        'sides' => 'integer',
        'corners' => 'integer',
        'edges' => 'integer',
        'faces' => 'integer',
    ];

    public function __construct() {
        parent::__construct();
        $this->shapes = include("shapeData.php");
        $this->shapeProps = $this->getShapeProperties();

        $this->build();
    }

    public function __get($property) {
        if (in_array($property, $this->shapeProps))
            return isset($this->shape->{$property}) ? $this->shape->{$property} : $this->getUnknownProperty($property);

        if ($property == 'lineSymmetry')
            return $this->lineSymmetry;
    }

    public function include($property, $values = null) {
        $filteredShapes = $this->filter(true, $property, $values);

        $this->shapes = $this->filteredShapes = array_merge($this->filteredShapes, $filteredShapes);

        return $this;
    }

    public function exclude($property, $values = null) {
        $this->shapes = $this->filteredShapes = $this->filter(false, $property, $values);

        return $this;
    }

    public function select($property, $values = null) {
        $this->shapes = $this->filteredShapes = $this->filter(true, $property, $values);

        return $this;
    }

    public function grid($cellSize = 10) {
        $this->showGrid = true;
        $this->columnWidth = $this->rowHeight = $cellSize;

        return $this;
    }

    public function rebuild() {
        $this->svg = $this->buildSvg();

        return $this;
    }

    public function lineOfSymmetry() {
        $this->showLineOfSymmetry = true;

        return $this;
    }

    public function build() {
        $this->shape = (object) $this->shapes[mt_rand(0, count($this->shapes) - 1)];

        $this->svg = $this->buildSvg();

        return $this;
    }

    private function buildSvg() {
        $svg = '';

        $grid = ($this->showGrid) ? $this->getGridLines() : '';
        $lineOfSymmetry = ($this->showLineOfSymmetry) ? $this->getLineOfSymmetry() : '';

        $svg .= "<div class='svg-container' speech='{$this->getTts()}' style='" . wrapUnique($this->getCss(['width' => "{$this->calculateWidth()}px", 'height' => "{$this->calculateHeight()}px"])) . "'>
            <svg viewBox='{$this->viewBox}' width='{$this->calculateWidth()}' height='{$this->calculateHeight()}' transform='scale(" . wrapUnique($this->xAxisFlip) . ", " . wrapUnique($this->yAxisFlip) . ") rotate(" . wrapUnique($this->rotation) . ")' data-shape-id='{$this->shape->id}' xmlns='http://www.w3.org/2000/svg'>
                <defs>
                    {$this->getGridDefs()}
                </defs>
                
                {$grid}
                {$this->getShape()}
                {$lineOfSymmetry}
            </svg>
        </div>";

        return $svg;
    }

    private function getShape() {
        $shape = $optionalTransparency = '';
        $layerCount = count($this->shape->pathData['shape']);
        
        for ($i = 0; $i < $layerCount; $i++) {
            $fillColor = 'none';
            
            if ($layerCount < 3 && $i == 0 || $layerCount == 3 && $i == 1) {
                $fillColor = $this->fillColor;
                
                if ($this->fillOpacity) $optionalTransparency = "fill-opacity={$this->fillOpacity}";
            }

            $shape .= "<path d='{$this->shape->pathData['shape'][$i]}' fill='{$fillColor}' stroke='{$this->strokeColor}' stroke-width='{$this->strokeWidth}' {$optionalTransparency} />";
        }

        return $shape;
    }

    private function getLineOfSymmetry() {
        $lineType = mt_rand(0, count($this->pathData) - 2) == 0 ? 'lineSymmetry' : 'lineAsymmetry';
        $this->lineSymmetry = ($lineType == 'lineSymmetry') ? true : false;
        
        return "<path d='{$this->pathData[$lineType][mt_rand(0, count($this->pathData[$lineType]) - 1)]}' fill='none' stroke='{$this->strokeColor}' stroke-width='" . ($this->strokeWidth * 1.5) . "' />";
    }

    private function filter($returnType, $property, $values = null) {
        $shapeSet = ($returnType) ? include("shapeData.php") : $this->shapes;

        if (!$values) {
            $filteredShapes = array_filter($shapeSet, function($shape) use($property, $returnType) {
                return ($returnType) ? isset($shape[$property]) : !isset($shape[$property]);
            });
        }

        $filteredShapes = array_filter($shapeSet, function($shape) use($property, $values, $returnType) {
            if (isset($shape[$property])) {
                if (is_array($shape[$property]))
                    return ($returnType) ? !empty(array_intersect($shape[$property], $values)) : empty(array_intersect($shape[$property], $values));

                if (!is_array($shape[$property]))
                    return ($returnType) ? in_array($shape[$property], $values) : !in_array($shape[$property], $values);
            }

            return ($returnType) ? false : true;
        });

        return array_values($filteredShapes);
    }

    private function getShapeProperties() {
        $propArray = [];

        foreach ($this->shapes as $shape) {
            foreach ($shape as $propKey => $propValue) {
                $propArray[] = $propKey;
            }
        };

        return $this->shapeProps = array_unique($propArray);
    }

    private function getUnknownProperty($property) {
        if ($this->casts[$property] == 'integer') return 0;
        if ($this->casts[$property] == 'array') return [];
        if ($this->casts[$property] == 'boolean') return false;

        return '';
    }
}
