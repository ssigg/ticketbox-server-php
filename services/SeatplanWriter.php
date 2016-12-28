<?php

namespace Services;

class SeatplanWriter implements TicketPartWriterInterface {
    private $blockMapper;
    private $outputDirectoryPath;

    public function __construct(\Spot\MapperInterface $blockMapper, $outputDirectoryPath) {
        $this->blockMapper = $blockMapper;
        $this->outputDirectoryPath = $outputDirectoryPath;
    }

    public function write(ExpandedReservationInterface $reservation, array $partFilePaths, $locale) {
        $seatPlanFilePath = $this->markSeatOnSeatplanAndReturnImageFilePath($reservation);
        $partFilePaths["seatplan"] = $seatPlanFilePath;
        return $partFilePaths;
    }

    private function markSeatOnSeatplanAndReturnImageFilePath(ExpandedReservationInterface $reservation) {
        $seat = $reservation->seat;
        $block = $this->blockMapper->get($seat->block_id);
        if (!preg_match('/data:([^;]*);base64,(.*)/', $block->seatplan_image_data_url, $matches)) {
            throw new Exception("Invalid image format.");
        }
        $imageData = base64_decode($matches[2]);
        $image = imagecreatefromstring($imageData);
        imagesetthickness($image, 4);
        $color = imagecolorallocate($image, 255, 50, 50); // TODO: use color from settings
        imageline($image, $seat->x0, $seat->y0, $seat->x1, $seat->y1, $color);
        imageline($image, $seat->x1, $seat->y1, $seat->x2, $seat->y2, $color);
        imageline($image, $seat->x2, $seat->y2, $seat->x3, $seat->y3, $color);
        imageline($image, $seat->x3, $seat->y3, $seat->x0, $seat->y0, $color);

        $seatPlanFilePath = $this->outputDirectoryPath . "/" . $reservation->unique_id . "_seatplan.png";
        imagepng($image, $seatPlanFilePath);
        imagedestroy($image);
        return $seatPlanFilePath;
    }
}