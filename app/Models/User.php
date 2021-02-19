<?php

namespace App\Models;

use App\Group;
use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'level',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Boot the model.
     *
     * @return void
     */
    public static function boot(): void
    {
        parent::boot();

        // Don't allow the root admin to be deleted.
        static::deleting(
            function (User $user) {
                if ($user->getKey() === 1) {
                    throw new DomainException('Cannot delete the root admin');
                }
            }
        );
    }

    /**
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->level === Group::ADMINISTRATOR;
    }
}
