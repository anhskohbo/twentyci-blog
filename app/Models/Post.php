<?php

namespace App\Models;

use App\Helpers\Traits\HasOptionsAttribute;
use App\Helpers\Traits\Sluggable;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Parsedown;

/**
 * @mixin \Eloquent
 */
class Post extends Model
{
    use HasFactory;
    use Sluggable;
    use HasOptionsAttribute;

    public const STATUS_PUBLISH = 'publish';
    public const STATUS_DRAFT = 'draft';

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
        'content_filtered',
        'published_at',
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

        static::saving(
            function (self $post) {
                if ($post->isDirty('content')) {
                    $post->content_filtered = self::parseContent($post->content);
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

    /**
     * @return HtmlString|string
     */
    public function getContent()
    {
        if (!$this->content) {
            return '';
        }

        if ($this->content_filtered) {
            return new HtmlString($this->content_filtered);
        }

        try {
            $content = self::parseContent($this->content);

            $this->timestamps = false;
            $this->update(['content_filtered' => $content]);
            $this->timestamps = true;

            return new HtmlString($content);
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * @param string $content
     * @return string|null
     */
    public static function parseContent(string $content): ?string
    {
        $parsedown = new Parsedown();

        $parsedown->setSafeMode(true);
        $parsedown->setMarkupEscaped(true);

        try {
            return $parsedown->text($content);
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->published_at === null || $this->published_at <= Carbon::now();
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where(
            function (Builder $builder) {
                $builder->whereNull('published_at');
                $builder->orWhere('published_at', '<=', Carbon::now()->format('Y-m-d H:i:s'));
            }
        );
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeUnPublished(Builder $query): Builder
    {
        return $query->where(
            function (Builder $builder) {
                $builder->whereNotNull('published_at');
                $builder->where('published_at', '>', Carbon::now()->format('Y-m-d H:i:s'));
            }
        );
    }
}
