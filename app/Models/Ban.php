<?php

namespace Azuriom\Models;

use Azuriom\Models\Traits\HasUser;
use Azuriom\Models\Traits\Loggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property int $user_id
 * @property int $author_id
 * @property int $remover_id
 * @property string $reason
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $removed_at
 *
 * @property \Azuriom\Models\User $user
 * @property \Azuriom\Models\User $author
 * @property \Azuriom\Models\User $remover
 */
class Ban extends Model
{
    use HasUser;
    use Loggable;
    use SoftDeletes;

    protected const DELETED_AT = 'removed_at';

    protected static $logEvents = [
        'created', 'deleted',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'reason',
    ];

    /**
     * The user key associated with this model.
     *
     * @var string
     */
    protected $userKey = 'author_id';

    /**
     * Get the banned user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the author of the ban.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the remover of the ban.
     */
    public function remover()
    {
        return $this->belongsTo(User::class, 'remover_id');
    }

    /**
     * Remove this ban.
     *
     * @param  \Azuriom\Models\User|null  $remover
     *
     * @throws \Exception
     */
    public function removeBan(User $remover = null)
    {
        $this->remover()->associate($remover ?? Auth::user());
        $this->save();

        $this->delete();

        $this->user->update(['is_banned' => false]);
    }
}
