<?php

namespace App\Libraries\Notifications\Events;

interface EventInterface
{
    /**
     * Get the name of the event.
     * Used to identify the event.
     */
    public function getName(): string;

    /**
     * Get the metadata associated with the event.
     * This will be serialised into the database as a JSON object.
     */
    public function getMetadata(): array;

    /**
     * Get the recipients of the event.
     * @return Recipient[]
     */
    public function getRecipients(): array;
}
