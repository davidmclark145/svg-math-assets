<?php
use function Lambdish\Phunctional\some;

class DataSet {
    private $dataValues = [];
    private $middleIndex;
    private $valueCount;
    private $valueRange;
    private $modeCount;
    private $allUnique = false;
    private $allowPrime = true;
    private $fromFactor = false;

    public function __construct(array $dataValues = null) {
        if ($dataValues) {
            sort($dataValues, SORT_NUMERIC);
            $this->dataValues = $dataValues;
            $this->middleIndex = $this->calculateMiddleIndex($dataValues);
        }
    }

    // 1st of 2 required methods to init object
    public function valueCount($valueCount) {
        $this->valueCount = $valueCount;
        
        return $this;
    }

    // 2nd of 2 required methods to init object
    public function valueRange(int $start, int $end) {
        $this->valueRange = range($start, $end);

        return $this;
    }

    public function getValueCount() {
        return $this->valueCount;
    }

    public function getValueRange() {
        return array(min($this->valueRange), max($this->valueRange));
    }

    public function getValues() {
        return $this->dataValues;
    }

    public function allowPrime(bool $val) {
        $this->allowPrime = $val;

        return $this;
    }

    public function generateFromFactor(bool $val) {
        $this->fromFactor = $val;
        
        return $this;
    }

    public function showDataRow() {
        $dataRow = "
        <table style='margin-left: 35px'>
            <tr>";
    
        foreach ($this->dataValues as $dataValue) {
            $dataRow .= "<td style='border: 1px solid black; padding: 8px; font-size: 14px; font-weight: 400;'>{$dataValue}</td>";
        }

        $dataRow .= "
            </tr>
        </table>";

        return $dataRow;
    }

    // if array count is 10, 'middle' index will be 5 (or position 6/10), since index 5.5 is not possible
    public function getMiddleIndex() {
        return $this->middleIndex;
    }

    public function getMean() {
        return array_sum($this->dataValues) / count($this->dataValues);
    }

    public function getMedian() {  
        return $this->calculateMedian($this->dataValues, $this->middleIndex);
    }

    // set the # of modes preferred, can be 1, 0, or multiple
    public function modeCount(int $modeCount) {
        $this->modeCount = $modeCount;

        return $this;
    }

    public function unique(bool $val) {
        $this->allUnique = $val;

        return $this;
    }

    // may naturally return multiple or 0 modes, use $this->modeCount() above if needed
    public function getMode(): array {
        return $this->findModes();
    }

    public function getRange() {
        return max($this->dataValues) - min($this->dataValues);
    }

    public function getFirstQuartile() {
        $valuesBeforeMedian = array_slice($this->dataValues, 0, $this->middleIndex);
        $firstQuartile = $this->calculateMedian($valuesBeforeMedian, $this->calculateMiddleIndex($valuesBeforeMedian));

        return $firstQuartile;
    }

    public function getThirdQuartile() {
        $valuesAfterMedian = (count($this->dataValues) % 2 == 0) ? array_slice($this->dataValues, $this->middleIndex) : array_slice($this->dataValues, $this->middleIndex + 1);
        $thirdQuartile = $this->calculateMedian($valuesAfterMedian, $this->calculateMiddleIndex($valuesAfterMedian));

        return $thirdQuartile;
    }

    public function getLowestValue() {
        return min($this->dataValues);
    }

    public function getHighestValue() {
        return max($this->dataValues);
    }
    
    public function countMatches($input) {
        $inputArray = (is_int($input)) ? (array) $input : $input;

        $timesMatched = count(array_filter($this->dataValues, function($dataValue) use($inputArray) {
            return in_array($dataValue, $inputArray);
        } ));

        return ($timesMatched != 0) ? $timesMatched : null;
    }

    public function getGCD():int {
        return array_reduce($this->dataValues, 'getGCD');
    }

    public function getLCM():int {
        $result = $this->dataValues[0];

        for ($i = 1; $i < sizeof($this->dataValues); $i++) {
            $result = ((($this->dataValues[$i] * $result)) / (getGCD($this->dataValues[$i], $result)));
        }
    
        return $result;
    }

    public function generate() {
        if (!$this->modeCount) 
            $this->dataValues = $this->generateDataValues($this->valueCount, $this->valueRange);

        if ($this->modeCount) {
            // $repeatingLength = mt_rand(2, round_down(($this->valueCount - 1) / $this->modeCount));
            $repeatingLength = mt_rand(2, ($this->valueCount - 1));
            
            do {
                $this->dataValues = $this->generateDataValues($this->valueCount, $this->valueRange, $repeatingLength);
                $modes = $this->findModes($this->dataValues);
            } while (count($modes) != $this->modeCount);
        }

        sort($this->dataValues, SORT_NUMERIC);
        $this->middleIndex = $this->calculateMiddleIndex($this->dataValues);

        return $this;
    }

    private function generateDataValues($valueCount, $valueRange, $repeatingLength = 1) {
        $minimumUnique = $repeatingLength == 1 
            ? ($valueCount / 2) 
            : (($valueCount - $repeatingLength) / 2);

        do {
            if ($repeatingLength > 1) {
                $dataValues = array_fill(0, $repeatingLength, mt_rand(min($valueRange), max($valueRange)));
                $startingIndex = $repeatingLength;
            } else {
                $dataValues = [];
                $startingIndex = 0;
            }

            if ($this->fromFactor) {
                $factor = mt_rand(min($valueRange), round_down(max($valueRange) / $valueCount));

                for ($i = 0; $i < $valueCount; $i++) {
                    $dataValues[] = $factor * mt_rand(2, round_down(max($valueRange) / $factor));
                }

                // chance to include factor
                if (mt_rand(1, 5) == 1)
                    $dataValues[mt_rand(0, $valueCount - 1)] = $factor;
            } else {
                for ($i = $startingIndex; $i < $valueCount; $i++) {
                    $dataValues[] = mt_rand(min($valueRange), max($valueRange));
                }
            }

            $uniqueCount = count(array_unique($dataValues));
            $hasPrime = some(function ($number) {
                return is_prime($number);
            }, $dataValues);
        } while ($uniqueCount <= $minimumUnique || ($this->allUnique && $uniqueCount != intval($valueCount)) || (!$this->allowPrime && $hasPrime));
        
        return $dataValues;
    }

    private function calculateMiddleIndex($dataValues) {
        return (count($dataValues) % 2 == 0) ? count($dataValues) / 2 : (int) round((count($dataValues) / 2), 0, PHP_ROUND_HALF_DOWN);
    }

    private function calculateMedian($dataValues, $middleIndex) {
        return (count($dataValues) % 2 != 0) 
            ? $dataValues[$middleIndex] 
            : ($dataValues[$middleIndex - 1] + $dataValues[$middleIndex]) / 2;
    }

    private function findModes(): array {
        $duplicateCounts = array_count_values($this->dataValues);

        if (max($duplicateCounts) == 1) 
            return [];

        $modes = array_filter($duplicateCounts, function($duplicateCount) use($duplicateCounts) { 
            return $duplicateCount == max($duplicateCounts); 
        });

        return (count($modes) == 1) ? [array_key_first($modes)] : array_keys($modes);
    }
    
}
