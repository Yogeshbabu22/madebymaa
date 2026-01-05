<?php

namespace App\Http\Controllers\Admin;

use App\Models\MainCategory;
use App\Models\Translation;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;

class MainCategoryController extends Controller
{
    function index(Request $request)
    {
        $key = explode(' ', $request['search']);
        $mainCategories = MainCategory::latest()
        ->when(isset($key), function ($q) use($key){
            $q->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%");
                }
            });
        })
        ->paginate(config('default_pagination'));
        return view('admin-views.main-category.index', compact('mainCategories'));
    }

    function store(Request $request)
    {
        $request->validate([
            'name.0' => 'required|unique:main_categories,name',
            'image' => 'nullable|max:2048',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
        ], [
            'name.0.required' => translate('default_name_is_required'),
            'start_time.date_format' => translate('messages.Invalid start time format'),
            'end_time.date_format' => translate('messages.Invalid end time format'),
            'end_time.after' => translate('messages.End time must be after start time'),
        ]);

        $mainCategory = new MainCategory();
        $mainCategory->name = $request->name[array_search('default', $request->lang)];
        $mainCategory->image = $request->has('image') ? Helpers::upload(dir:'main-category/', format: 'png', image: $request->file('image')) : 'def.png';
        $mainCategory->position = 0;
        $mainCategory->start_time = $request->start_time;
        $mainCategory->end_time = $request->end_time;
        $mainCategory->save();

        $data = [];
        $default_lang = str_replace('_', '-', app()->getLocale());

        foreach($request->lang as $index => $key)
        {
            if($default_lang == $key && !($request->name[$index])){
                if ($key != 'default') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\MainCategory',
                        'translationable_id' => $mainCategory->id,
                        'locale' => $key,
                        'key' => 'name',
                        'value' => $mainCategory->name,
                    ));
                }
            } else {
                if ($request->name[$index] && $key != 'default') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\MainCategory',
                        'translationable_id' => $mainCategory->id,
                        'locale' => $key,
                        'key' => 'name',
                        'value' => $request->name[$index],
                    ));
                }
            }
        }

        if(count($data))
        {
            Translation::insert($data);
        }

        Toastr::success(translate('messages.main_category_added_successfully'));
        return back();
    }

    public function edit($id)
    {
        $mainCategory = MainCategory::withoutGlobalScope('translate')->findOrFail($id);
        return view('admin-views.main-category.edit', compact('mainCategory'));
    }

    public function status(Request $request)
    {
        $mainCategory = MainCategory::find($request->id);
        $mainCategory->status = $request->status;
        $mainCategory->save();
        Toastr::success(translate('messages.main_category_status_updated'));
        return back();
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name.0' => 'required|max:100|unique:main_categories,name,'.$id,
            'image' => 'nullable|max:2048',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
        ],[
            'name.0.required' => translate('default_name_is_required'),
            'start_time.date_format' => translate('messages.Invalid start time format'),
            'end_time.date_format' => translate('messages.Invalid end time format'),
            'end_time.after' => translate('messages.End time must be after start time'),
        ]);

        $mainCategory = MainCategory::find($id);
        $slug = Str::slug($request->name[array_search('default', $request->lang)]);
        $mainCategory->slug = $mainCategory->slug ? $mainCategory->slug : "{$slug}{$mainCategory->id}";
        $mainCategory->name = $request->name[array_search('default', $request->lang)];
        $mainCategory->start_time = $request->start_time;
        $mainCategory->end_time = $request->end_time;
        $mainCategory->image = $request->has('image') ? Helpers::update(dir:'main-category/', old_image:$mainCategory->image, format: 'png', image:$request->file('image')) : $mainCategory->image;
        $mainCategory->save();

        $default_lang = str_replace('_', '-', app()->getLocale());

        foreach($request->lang as $index => $key)
        {
            if($default_lang == $key && !($request->name[$index])){
                if (isset($mainCategory->name) && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\MainCategory',
                            'translationable_id' => $mainCategory->id,
                            'locale' => $key,
                            'key' => 'name'
                        ],
                        ['value' => $mainCategory->name]
                    );
                }
            } else {
                if ($request->name[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\MainCategory',
                            'translationable_id' => $mainCategory->id,
                            'locale' => $key,
                            'key' => 'name'
                        ],
                        ['value' => $request->name[$index]]
                    );
                }
            }
        }

        Toastr::success(translate('messages.main_category_updated_successfully'));
        return back();
    }

    public function delete(Request $request)
    {
        $mainCategory = MainCategory::findOrFail($request->id);
        if ($mainCategory?->categories?->count() == 0){
            $mainCategory?->translations()?->delete();
            $mainCategory->delete();
            Toastr::success(translate('messages.Main Category removed!'));
        } else {
            Toastr::warning(translate('messages.remove_categories_first'));
        }
        return back();
    }

    public function update_priority(MainCategory $mainCategory, Request $request)
    {
        $priority = $request->priority ?? 0;
        $mainCategory->priority = $priority;
        $mainCategory->save();
        Toastr::success(translate('messages.main_category_priority_updated_successfully'));
        return back();
    }
}
