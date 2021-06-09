<?php

namespace App\Message;

class UserNotificationMessage
{

    private int $userId;
    private string $message;
    private string $notifType;

    public function __construct(int $userId, string $message, string $notifType)
    {
        $this->userId = $userId;
        $this->message = $message;
        $this->notifType = $notifType;
    }

    /**
     * Get the value of userId
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Get the value of message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get the value of notifType
     */
    public function getNotifType()
    {
        return $this->notifType;
    }
}
