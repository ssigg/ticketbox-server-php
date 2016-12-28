<?php

namespace Services;

interface TicketPartWriterInterface {
    function write(ExpandedReservation $reservation, array $partFilePaths, $locale);
}