<?php
namespace App\Traits;

use App\Models\ImageItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

trait HandlesImages
{
    /**
     * Handle image upload.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $model
     * @param  string  $inputName
     * @param  string  $directory
     * @return void
     */
    public function uploadImages(Request $request, $model, $inputName = 'images', $path = '/uploads')
    {
        if ($request->hasFile($inputName)) {
            $files = $request->file($inputName);
            $files = is_array($files) ? $files : [$files];

            foreach ($files as $file) {
                $ext = $file->getClientOriginalExtension();
                $fileName = 'media_' . now()->timestamp . uniqid() . '.' . $ext;
                $file->move(public_path($path), $fileName);
                $model->images()->create(['image_path' => $path . '/' . $fileName]);
            }
        }
    }

    /**
     * Delete images by ID.
     *
     * @param  array  $imageIds
     * @param  mixed  $model
     * @return void
     */
    public function deleteImages(array $imageIds, $model)
    {
        foreach ($imageIds as $imageId) {
            $image = ImageItem::findOrFail($imageId);
            if ($image->imageable_id == $model->id && $image->imageable_type == get_class($model)) {
                if (File::exists(public_path($image->image_path))) {
                    $fullFilePath = public_path($image->image_path);
                    File::delete($fullFilePath);
                }
                $image->delete();
            }
        }
    }
}
