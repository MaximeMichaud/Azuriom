<?php

namespace Azuriom\Game;

use Azuriom\Models\User;

interface Game
{
    public function name();

    /**
     * Get the avatar URL of the user.
     *
     * @param  \Azuriom\Models\User  $user
     * @param  int  $size
     */
    public function getAvatarUrl(User $user, int $size = 64);

    /**
     * Get the game id of the user.
     *
     * @param  string  $name
     */
    public function getUserUniqueId(string $name);

    /**
     * Get the game user name.
     *
     * @param  \Azuriom\Models\User  $user
     * @return mixed
     */
    public function getUserName(User $user);
}
