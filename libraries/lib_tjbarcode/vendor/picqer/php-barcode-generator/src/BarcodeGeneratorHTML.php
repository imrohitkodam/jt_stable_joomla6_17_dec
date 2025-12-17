<?php

namespace Picqer\Barcode;

class BarcodeGeneratorHTML extends BarcodeGenerator
{
    /**
     * Return an HTML representation of barcode.
     * This original version uses pixel based widths and heights. Use Dynamic HTML version for better quality representation.
     *
     * @param string $barcode code to print
     * @param string $type type of barcode
     * @param int $widthFactor Width of a single bar element in pixels.
     * @param int $height Height of a single bar element in pixels.
     * @param string $foregroundColor Foreground color for bar elements as '#333' or 'orange' for example (background is transparent).
     * @return string HTML code.
     */
    public function getBarcode($barcode, $type, int $widthFactor = 2, int $height = 30, string $foregroundColor = 'black')
    {
        $barcodeData = $this->getBarcodeData($barcode, $type);

        $html = '<div style="font-size:0;position:relative;width:' . ($barcodeData->getWidth() * $widthFactor) . 'px;height:' . ($height) . 'px;">' . PHP_EOL;

        $positionHorizontal = 0;
        /** @var BarcodeBar $bar */
        foreach ($barcodeData->getBars() as $bar) {
            $barWidth = round(($bar->getWidth() * $widthFactor), 3);
            $barHeight = round(($bar->getHeight() * $height / $barcodeData->getHeight()), 3);

            if ($bar->isBar() && $barWidth > 0) {
                $positionVertical = round(($bar->getPositionVertical() * $height / $barcodeData->getHeight()), 3);
                // draw a vertical bar

                $prevPositionHorizontal = !empty($prevPositionHorizontal) ? $prevPositionHorizontal : 0;
                $html .= '<div style="float:left; background-color:' . $foregroundColor . ';width:' . $barWidth . 'px;height:' . $barHeight . 'px; margin-left:' . ( $positionHorizontal - $prevPositionHorizontal ) . 'px;">&nbsp;</div>' . PHP_EOL;
            }

            $prevPositionHorizontal = $positionHorizontal;
            $positionHorizontal += $barWidth;
        }

        $html .= '</div><div style="clear:both"></div>' . PHP_EOL;

        return $html;
    }
}
