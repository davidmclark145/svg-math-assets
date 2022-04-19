<?php
class BoxPlot {
    public $svg;
    private $dataCount;
    private $gridRange = [];
    private $valueRange;
    private $headerText;
    private $footerText;
    private $width = 400;
    private $height = 300;
    private $scale = 1;
    private $boxScale = 1;
    private $boxColor = '#4472c4';
    private $marginLeft = 30;
    private $marginTop = 10;
    private $marginBottom = 10;

    public function __construct(array $dataValues = null, int $padding = null) {
        if ($dataValues) {
            $this->dataSet = new DataSet($dataValues);
            $this->gridRange = (!$padding) ? range(min($dataValues), max($dataValues)) : range(min($dataValues) - $padding, max($dataValues) + $padding);
        }
    }

    // 1st of 2 required to init
    public function dataCount(int $dataCount) {
        $this->dataCount = $dataCount;

        return $this;
    }

    // 2nd of 2 required to init | sets both grid range and values within that range
    public function gridRange(int $start, int $end) {
        $this->gridRange = range($start, $end);

        return $this;
    }

    // allows more control over values within set grid range | use to 'pad' grid rows on top and/or bottom
    public function valueRange(int $start, int $end) {
        $this->valueRange = range($start, $end);

        return $this;
    }

    public function header($headerText) {
        $this->headerText = $headerText;
        $this->marginTop = 35;

        return $this;
    }

    public function footer($footerText) {
        $this->footerText = $footerText;
        $this->marginBottom = 35;

        return $this;
    }

    public function width($width) {
        $this->width = $width;

        return $this;
    }

    public function height($height) {
        $this->height = $height;

        return $this;
    }

    public function scale($scale = 1) {
        $this->scale = $scale;

        return $this;
    }

    // scales the 'box' part of the 'box-and-whisker' graph | default width is half of graphWidth
    public function boxScale($boxScale = 1) {
        $this->boxScale = $boxScale;

        return $this;
    }

    public function boxColor($boxColor = '#4472c4') {
        $this->boxColor = $boxColor;

        return $this;
    }

    // 30 accommodates 2-digit numbers @ 14px font size | increase when increasing digit count/font size
    public function marginLeft($marginLeft = 30) {
        $this->marginLeft = $marginLeft;

        return $this;
    }

    // returns simple numerical array of all data values from DataSet()
    public function getDataValues() {
        return $this->dataSet->getValues();
    }

    // presents the data set, formatted in a table row
    public function showDataRow() {
        return $this->dataSet->showDataRow();
    }

    public function getDataCount() {
        return $this->dataSet->getValueCount();
    }

    public function getGridRange() {
        return [min($this->gridRange), max($this->gridRange)];
    }

    public function getValueRange() {
        return $this->dataSet->getValueRange();
    }

    public function getMedian() {
        return $this->dataSet->getMedian();
    }

    public function getFirstQuartile() {
        return $this->dataSet->getFirstQuartile();
    }

    public function getThirdQuartile() {
        return $this->dataSet->getThirdQuartile();
    }

    public function getLowestValue() {
        return $this->dataSet->getLowestValue();
    }

    public function getHighestValue() {
        return $this->dataSet->getHighestValue();
    }

    public function build() {
        $this->dataSet = (isset($this->dataSet)) ? $this->dataSet : $this->generateDataSet($this->dataCount, $this->gridRange);
        
        $rangeMax = max($this->gridRange);
        $rangeMin = min($this->gridRange);
        $gridLineCount = count($this->gridRange);

        $graphWidth = $this->width - $this->marginLeft;
        $boxWidth = ($graphWidth * .5) * $this->boxScale;
        $graphBottom = $this->height - $this->marginBottom;
        $lineGap = ($this->height - ($this->marginTop + $this->marginBottom)) / ($gridLineCount - 1);

        $median = $this->dataSet->getMedian();
        $firstQuartile = $this->dataSet->getFirstQuartile();
        $thirdQuartile = $this->dataSet->getThirdQuartile();
        $lowestValue = $this->dataSet->getLowestValue();
        $highestValue = $this->dataSet->getHighestValue();

        $header = (!$this->headerText) ? '' : $this->buildHeader($this->headerText, $graphWidth);
        $footer = (!$this->footerText) ? '' : $this->buildFooter($this->footerText, $graphWidth, $graphBottom);

        $this->svg = "<svg viewBox='0 0 {$this->width} {$this->height}' width='" . ($this->width * $this->scale) . "' height='" . ($this->height * $this->scale) . "' xmlns='http://www.w3.org/2000/svg'>";

        for ($i = 0; $i < $gridLineCount; $i++) {
            $this->svg .= "
                <line data-id='graph-line-{$i}' x1='{$this->marginLeft}' x2='{$this->width}' y1='" . ($graphBottom - ($i * $lineGap)) . "' y2='" . ($graphBottom - ($i * $lineGap)) . "' stroke='#808080' stroke-width='1'></line>
                <text data-id='y-label-{$i}' dominant-baseline='middle' text-anchor='end' style='font-size: 14px;' x='" . ($this->marginLeft - 10) . "' y='" . ($graphBottom - ($i * $lineGap)) . "'>{$this->gridRange[$i]}</text>";
        }
        
        $this->svg .= "
            <line data-id='left-graph-line' x1='{$this->marginLeft}' x2='{$this->marginLeft}' y1='" . (($this->marginTop) - .5) . "' y2='" . ($graphBottom + .5) . "' stroke='#808080' stroke-width='1'></line>
            <line data-id='right-graph-line' x1='{$this->width}' x2='{$this->width}' y1='" . (($this->marginTop) - .5) . "' y2='" . ($graphBottom + .5) . "' stroke='#808080' stroke-width='1'></line>
            
            <line data-id='top-whisker-vertical-line' x1='" . ($this->marginLeft + ($graphWidth * .5)) . "' x2='" . ($this->marginLeft + ($graphWidth * .5)) . "' y1='" . ($this->marginTop + (($rangeMax - $highestValue) * $lineGap)) . "' y2='" . ($this->marginTop + (($rangeMax - $thirdQuartile) * $lineGap)) . "' stroke='#000' stroke-width='2'></line>
            <line data-id='bottom-whisker-vertical-line' x1='" . ($this->marginLeft + ($graphWidth * .5)) . "' x2='" . ($this->marginLeft + ($graphWidth * .5)) . "' y1='" . ($graphBottom - (($firstQuartile - $rangeMin) * $lineGap)) . "' y2='" . ($graphBottom - (($lowestValue - $rangeMin) * $lineGap)) . "' stroke='#000' stroke-width='2'></line>
            <line data-id='top-whisker-horizontal-line' x1='" . ($this->marginLeft + (($graphWidth / 16) * 7)) . "' x2='" . ($this->marginLeft + (($graphWidth / 16) * 9)) . "' y1='" . ($this->marginTop + (($rangeMax - $highestValue) * $lineGap)) . "' y2='" . ($this->marginTop + (($rangeMax - $highestValue) * $lineGap)) . "' stroke='#000' stroke-width='2'></line>
            <line data-id='bottom-whisker-horizontal-line' x1='" . ($this->marginLeft + (($graphWidth / 16) * 7)) . "' x2='" . ($this->marginLeft + (($graphWidth / 16) * 9)) . "' y1='" . ($graphBottom - (($lowestValue - $rangeMin) * $lineGap)) . "' y2='" . ($graphBottom - (($lowestValue - $rangeMin) * $lineGap)) . "' stroke='#000' stroke-width='2'></line>

            <rect data-id='box' x='" . ($this->marginLeft + (($graphWidth - $boxWidth) / 2)) . "' y='" . ($graphBottom - (($thirdQuartile - $rangeMin) * $lineGap)) . "' width='{$boxWidth}' height='" . (($thirdQuartile - $firstQuartile) * $lineGap) . "' fill='{$this->boxColor}' />
            <line data-id='median-line' x1='" . ($this->marginLeft + (($graphWidth - $boxWidth) / 2)) . "' x2='" . ($this->marginLeft + (($graphWidth - $boxWidth) / 2) + $boxWidth) . "' y1='" . ($graphBottom - (($median - $rangeMin) * $lineGap)) . "' y2='" . ($graphBottom - (($median - $rangeMin) * $lineGap)) . "' stroke='#000' stroke-width='1.5'></line>

            {$header}
            {$footer}
        </svg>";

        return $this;
    }

    private function generateDataSet($valueCount, $valueRange) {
        if ($this->valueRange) $valueRange = $this->valueRange;

        $dataSet = (new DataSet())
            ->valueCount($valueCount)
            ->valueRange(min($valueRange), max($valueRange))
            ->generate();

        return $dataSet;
    }

    private function buildHeader($headerText, $graphWidth) {
        $svgHeader = '';

        $svgHeader .= "<text data-id='header' dominant-baseline='middle' text-anchor='middle' style='font-size: 18px;' x='" . ($this->marginLeft + ($graphWidth / 2)) . "' y='" . ($this->marginTop - 15) . "'>{$headerText}</text>";

        return $svgHeader;
    }

    private function buildFooter($footerText, $graphWidth, $graphBottom) {
        $svgFooter = '';

        $svgFooter .= "<text data-id='footer' dominant-baseline='middle' text-anchor='middle' style='font-size: 18px;' x='" . ($this->marginLeft + ($graphWidth / 2)) . "' y='" . ($graphBottom + 20) . "'>{$footerText}</text>";

        return $svgFooter;
    }
    
    public function __toString() {
        return $this->svg;
    }

}
