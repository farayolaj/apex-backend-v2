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
     * Get the title of the event.
     */
    public function getTitle(): string;

    /**
     * Get the message of the event.
     */
    public function getMessage(): string;

    /**
     * Get the metadata associated with the event.
     * This will be serialised into the database as a JSON object.
     */
    public function getMetadata(): array | null;

    /**
     * Get the recipients of the event.
     * @return Recipient[]
     */
    public function getRecipients(): array;

    /**
     * Get the sender of the event.
     * Return null if the event initiator is not a user.
     */
    public function getSender(): ?Sender;
}
