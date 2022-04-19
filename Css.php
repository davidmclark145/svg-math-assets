<?php
trait Css {
    protected $defaultCss = [];
    protected $userDefinedCss = [];

    public function css($css = []) {
        $this->userDefinedCss = $css;

        return $this;
    }

    protected function getCss($finalCss = []) {
        foreach($this->userDefinedCss as $key => $val) {
            $finalCss[$key] = $val;
        }

        foreach($this->defaultCss as $key => $val) {
            if (!array_key_exists($key, $finalCss)) {
                $finalCss[$key] = $val;
            }
        }

        return $this->convertArrayToCss($finalCss);
    }
    
    private function convertArrayToCss($props) {
        $string = '';

        // add 'px', 'em', '%', or nothing
        foreach ($props as $key => $val) {
            $string .= "{$key}: {$val};";
        }

        return $string;
    }
}
