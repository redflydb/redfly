<?php
namespace CCR\REDfly\Download\Service;

use CCR\REDfly\Service\Message\QueryInterface;
/**
 * Interface for defining an encoding strategy for encoding the data within a
 * traversable to a string suitable for sending to the client as a response to a
 * query.
 */
interface Encoder
{
    /**
     * Encodes an iterable to a string.
     * @param iterable $data The data to serialize.
     * @param QueryInterface $primaryQuery
     *     The main query object that initiated the action.
     *     Used for any parameters that the encoding procedure may require.
     * @param array $stagingData
     * @return string The result of the encode.
     */
    public function encode(
        iterable $data,
        QueryInterface $primaryQuery,
        array $stagingData
    );
}
