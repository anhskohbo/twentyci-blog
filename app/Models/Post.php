<?php

namespace App\Models;

use App\Helpers\Traits\HasOptionsAttribute;
use App\Helpers\Traits\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * @mixin \Eloquent
 */
class Post extends Model
{
    use HasFactory;
    use Sluggable;
    use HasOptionsAttribute;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'posts';

    /**
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'content',
    ];

    /**
     * @var array|string[]
     */
    protected $hidden = [
        'options',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'options' => 'json',
        'published_at' => 'datetime',
    ];

    /**
     * Configure the sluggable.
     *
     * @var array
     */
    protected $sluggable = [
        'slug' => 'title',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::creating(
            function (self $post) {
                if ($post->user_id && Auth::check()) {
                    $post->user_id = Auth::id();
                }
            }
        );
    }

    /**
     * Start a new post.
     *
     * @param string $title
     * @param User $user
     * @return static
     */
    public static function start(string $title, User $user): Post
    {
        $post = new static();

        $post->title = $title;
        $post->user_id = $user->id;

        $post->setRelation('user', $user);

        return $post;
    }

    /**
     * Define the relationship with the post's author.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
