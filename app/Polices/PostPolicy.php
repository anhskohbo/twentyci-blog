<?php

namespace App\Polices;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Determines if given user can create new post.
     *
     * @param User $actor
     * @return bool|null
     */
    public function start(User $actor): bool
    {
        return true; // always true for now.
    }

    /**
     * Determines if given user can update/modify a post.
     *
     * @param User $actor
     * @param Post $post
     * @return bool
     */
    public function update(User $actor, Post $post): bool
    {
        return $actor->id === $post->user_id || $actor->isSuperAdmin();
    }

    /**
     * Determines if given user can delete a post.
     *
     * @param User $actor
     * @param Post $post
     * @return bool
     */
    public function delete(User $actor, Post $post): bool
    {
        return $actor->id === $post->user_id || $actor->isSuperAdmin();
    }

    /**
     * Determines if given user can publish a post.
     *
     * @param User $actor
     * @param Post $post
     * @return bool
     */
    public function publish(User $actor, Post $post): bool
    {
        return $actor->isSuperAdmin();
    }
}
