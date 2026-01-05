<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MainCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'slug',
        'position',
        'priority',
        'status',
        'start_time',
        'end_time'
    ];

    protected $casts = [
        'position' => 'integer',
        'priority' => 'integer',
        'status' => 'integer',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function scopeActive($query)
    {
        return $query->where('status', '=', 1);
    }

    public function scopeActiveByTime($query)
    {
        $currentTime = now()->format('H:i:s');
        return $query->where('status', '=', 1)
                    ->where(function($q) use ($currentTime) {
                        $q->whereNull('start_time')
                          ->orWhere('start_time', '<=', $currentTime);
                    })
                    ->where(function($q) use ($currentTime) {
                        $q->whereNull('end_time')
                          ->orWhere('end_time', '>=', $currentTime);
                    });
    }

    public function isCurrentlyActive()
    {
        if (!$this->status) {
            return false;
        }

        if (!$this->start_time && !$this->end_time) {
            return true;
        }

        $currentTime = now()->format('H:i:s');
        
        if ($this->start_time && $this->end_time) {
            return $currentTime >= $this->start_time && $currentTime <= $this->end_time;
        }

        if ($this->start_time) {
            return $currentTime >= $this->start_time;
        }

        if ($this->end_time) {
            return $currentTime <= $this->end_time;
        }

        return true;
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'main_category_id');
    }

    public function getNameAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'name') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    protected static function booted()
    {
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                return $query->where('locale', app()->getLocale());
            }]);
        });
    }

    protected static function boot()
    {
        parent::boot();
        static::created(function ($mainCategory) {
            $mainCategory->slug = $mainCategory->generateSlug($mainCategory->name);
            $mainCategory->save();
        });
    }

    private function generateSlug($name)
    {
        $slug = Str::slug($name);
        if ($max_slug = static::where('slug', 'like',"{$slug}%")->latest('id')->value('slug')) {

            if($max_slug == $slug) return "{$slug}-2";

            $max_slug = explode('-',$max_slug);
            $count = array_pop($max_slug);
            if (isset($count) && is_numeric($count)) {
                $max_slug[]= ++$count;
                return implode('-', $max_slug);
            }
        }
        return $slug;
    }
}
