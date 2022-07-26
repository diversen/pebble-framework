<?php

declare(strict_types=1);

namespace Pebble;

class Flash
{
    /**
     * Set a SESSION flash message
     * @param string $message
     * @param string $type e.g. 'info', 'success', 'warning', 'error' or any other you may use in your app.
     * @param array<mixed> $options ['flash_remove' => true] Options. E.g. ['flash_remove' => true] could be used to remove the message after 5 secs.
     */
    public function setMessage(string $message, string $type, array $options = []): void
    {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][] = ['message' => $message, 'type' => $type, 'options' => $options];
    }

    /**
     * Get all flash messages from SESSION as an array, and then delete the flash messages from SESSION
     * @return array<mixed> $messages
     */
    public function getMessages(): array
    {
        $messages = [];

        if (isset($_SESSION['flash'])) {
            foreach ($_SESSION['flash'] as $message_ary) {
                $messages[] = $message_ary;
            }
        }

        if (isset($_SESSION['flash'])) {
            unset($_SESSION['flash']);
        }

        return $messages;
    }
}
