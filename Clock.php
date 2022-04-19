<?php
class Clock extends Circle {
    protected $fillColor = '#fff';
    protected $ttsDescription = 'clock';
    protected $strokeColor = '#6BB3FA';
    protected $strokeWidth = 3;
    private $timeArray = [];
    private $hour;
    private $minute;
    private $seconds;
    private $hideHourHand = false;
    private $hideMinuteHand = false;
    private $showSecondsHand = false;

    public function __construct($time = null) {
        $this->timeArray = ($time) ? array_map('intval', explode(':', $time)) : [mt_rand(1, 12), mt_rand(0, 59), mt_rand(0, 59)];

        $this->hour($this->timeArray[0]);
        $this->minute($this->timeArray[1] ?? 0);
        $this->seconds($this->timeArray[2] ?? 0);
    }

    public function hour($hour) {
        $this->hour = ($hour == 12) ? 0 : $hour * 5;
        $this->timeArray[0] = $hour;

        return $this;
    }

    public function minute($minute) {
        $this->minute = $this->timeArray[1] = $minute;
        
        return $this;
    }

    public function seconds($seconds) {
        $this->seconds = $this->timeArray[2] = $seconds;

        return $this;
    }

    public function hideHourHand($bool = true) {
        $this->hideHourHand = $bool;

        return $this;
    }

    public function hideMinuteHand($bool = true) {
        $this->hideMinuteHand = $bool;

        return $this;
    }

    public function showSecondsHand($bool = true) {
        $this->showSecondsHand = $bool;

        return $this;
    }

    public function getTime($output = 'string') {
        if ($output == 'string') {
            $timeString = '';

            foreach ($this->timeArray as $key => $timeSlot) {
                if ($timeSlot < 10) $timeSlot = sprintf('%02d', $timeSlot);

                $timeString .= "$timeSlot";
    
                if ($key != array_key_last($this->timeArray)) $timeString .= ':';
            }

            return $timeString;
        }

        if ($output == 'array') return ['hr' => $this->timeArray[0], 'mn' => $this->timeArray[1], 's' => $this->timeArray[2]];

        return $this;
    }
    
    public function build() {
        $clockStyles = wrapUnique($this->getClockStyles());
        $borderCircle = $this->buildCircle(($this->radiusX() + (5 * $this->scale)), ($this->radiusY() + (5 * $this->scale)));
        $clock = $this->buildClock();

        $this->svg = "
            <div class='svg-container' speech='{$this->getTts()}' style='{$this->getCss(['width' => "{$this->calculateWidth()}px", 'height' => "{$this->calculateHeight()}px"])}'>
                <svg width='{$this->calculateWidth()}' height='{$this->calculateHeight()}' xmlns='http://www.w3.org/2000/svg'>
                    {$clockStyles}
                    {$borderCircle}
                    {$clock}
                </svg>
            </div>
        ";

        return $this;
    }

    private function getClockStyles() {
        return "
        <style>
            .clock-text{
                font-family: Verdana, Arial, Helvetica, sans-serif; color: #333;
            } 
            .hr-text-primary{
                font-size: " . (13 * $this->scale) . "px;
            } 
            .hr-text-secondary{
                font-size: " . (10 * $this->scale) . "px;
            }
        </style>";
    }

    private function buildClock() {
        $clock = '';

        $percent = 1 / 60;
        $hourPositions = range(0, 60, 5);

        for ($i = 0; $i < 60; $i++) {
            $tickOffset = (in_array($i, $hourPositions)) ? 5 : 4;
            $tickColor = (in_array($i, $hourPositions)) ? '#6BB3FA' : '#9E9E9E';

            list($startX, $startY) = $this->getPercentCoords(($percent * $i - .25), $this->radiusX(), $this->radiusY());
            list($endX, $endY) = $this->getPercentCoords(($percent * $i - .25), ($this->radiusX() - ($tickOffset * $this->scale)), ($this->radiusY() - ($tickOffset * $this->scale)));

            // below block utilizes current tick position (out of 60) to insert hourText elements (text centered inside a rect)
            if (in_array($i, $hourPositions)) {
                list($x, $y) = $this->getPercentCoords(($percent * $i - .25), ($this->radiusX() - (15 * $this->scale)), ($this->radiusY() - (15 * $this->scale)));
                
                $clock .= $this->hourText(($i / 5), $x, $y);
            }

            $clock .= "<path d='M{$startX},{$startY} L{$endX},{$endY}' stroke='{$tickColor}' stroke-width='" . (2 * $this->scale) . "' stroke-linecap='round'></path>";
        }

        if (!$this->hideHourHand) {
            $hourPrecision = ($this->minute / 60) * (5 * $percent);

            list($hourHandX, $hourHandY) = $this->getPercentCoords(($percent * $this->hour - .25 + $hourPrecision), $this->radiusX() - (42.5 * $this->scale), $this->radiusY() - (42.5 * $this->scale));

            $clock .= "
                <circle cx='" . ($this->calculateWidth() / 2) . "' cy='" . ($this->calculateHeight() / 2) . "' r='" . (3 * $this->scale) . "' fill='#666' />
                <path d='M" . ($this->calculateWidth() / 2) . "," . ($this->calculateHeight() / 2) . " L{$hourHandX},{$hourHandY}' stroke='#666' stroke-width='" . (3.25 * $this->scale) . "' stroke-linecap='round'></path>
            ";
        }

        if (!$this->hideMinuteHand) {
            list($minuteHandX, $minuteHandY) = $this->getPercentCoords(($percent * $this->minute - .25), $this->radiusX() - (25 * $this->scale), $this->radiusY() - (25 * $this->scale));

            $clock .= "<path d='M" . ($this->calculateWidth() / 2) . "," . ($this->calculateHeight() / 2) . " L{$minuteHandX},{$minuteHandY}' stroke='#666' stroke-width='" . (3 * $this->scale) . "' stroke-linecap='round'></path>";
        }

        if ($this->showSecondsHand) {
            list($secondsHandX, $secondsHandY) = $this->getPercentCoords(($percent * $this->seconds - .25), $this->radiusX() - (21 * $this->scale), $this->radiusY() - (21 * $this->scale));

            $clock .= "
                <circle cx='" . ($this->calculateWidth() / 2) . "' cy='" . ($this->calculateHeight() / 2) . "' r='" . (1.5 * $this->scale) . "' fill='#6BB3FA' />
                <path d='M" . ($this->calculateWidth() / 2) . "," . ($this->calculateHeight() / 2) . " L{$secondsHandX},{$secondsHandY}' stroke='#6BB3FA' stroke-width='" . (1 * $this->scale) . "' stroke-linecap='round'></path>
            ";
        }

        return $clock;
    }

    private function hourText($hour, $x, $y) {
        $cssClass = ($hour % 3 == 0) ? 'hr-text-primary' : 'hr-text-secondary';

        if ($hour == 0) $hour = 12;

        $hourText = "
            <rect width='" . (20 * $this->scale) . "' height='" . (20 * $this->scale) . "' x='{$x}' y='{$y}' fill='none' stroke='none' transform='translate(" . (-10 * $this->scale) . " " . (-10 * $this->scale) . ")' />
            <text class='clock-text {$cssClass}' x='{$x}' y='{$y}' fill='#333' text-anchor='middle' dominant-baseline='middle'>{$hour}</text>
        ";

        return $hourText;
    }
}
