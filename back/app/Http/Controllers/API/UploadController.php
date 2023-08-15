<?php

namespace App\Http\Controllers\API;

use App\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Routing\Controller;

class UploadController extends Controller
{
    public static function store($model)
    {
        echo request()->file('file');
        try {
            if (!is_dir(public_path('/images'))) {
                mkdir(public_path('/images'), 0777);
            }
            $images = Collection::wrap(request()->file('file'));
            $images->each(function ($image) use ($model) {
                $basename = Str::random();
                $original = $basename . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('/images'), $original);
                $model->image()->create(['image_url' => '/images' . '/' . $original]);
            });
        } catch (Exception $e) {
            return $e;
        }
        return true;
    }
}
