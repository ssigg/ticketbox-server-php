<?php

namespace Services;

interface TicketPartWriterInterface {
    function write(ExpandedReservationInterface $reservation, array $partFilePaths, $locale);
}