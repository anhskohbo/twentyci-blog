<?php

namespace App\Repository;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class PostRepository extends AbstractRepository
{
    /**
     * @var array
     */
    protected $with = ['user'];

    /**
     * @var array
     */
    protected $search = ['id', 'title', 'slug'];

    /**
     * Filter the post query.
     *
     * @param User|null $actor
     * @return Builder
     */
    public function query(?User $actor = null): Builder
    {
        $query = Post::query();

        $query->where(
            function (Builder $query) use ($actor) {
                $query->where('posts.status', Post::STATUS_PUBLISH);

                if ($actor) {
                    $query->orWhere('posts.status', Post::STATUS_DRAFT);
                }

                // Show posts if they require approval and they are
                // authored by the current user, or the current user has permission to
                // approve posts.
                if ($actor && !$actor->isSuperAdmin()) {
                    $query->where('posts.user_id', $actor->getKey());
                }
            }
        );

        return $query;
    }
}
