<?php
namespace App\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

trait FileUploadTrait
{

    public function uploadFile(Request $request, string $inputName, ?string $oldPath = null, string $path = '/uploads')
    {
        if ($request->hasFile($inputName)) {
            if ($oldPath && File::exists(public_path($oldPath))) {
                $fullFilePath = public_path($oldPath);
                File::delete($fullFilePath);
            }
            $file = $request->file($inputName);
            $ext = $file->getClientOriginalExtension();
            $fileName = 'media_' . uniqid() . '.' . $ext;
            $file->move(public_path($path), $fileName);

            return $path . '/' . $fileName;
        }
        return null;
    }

    public function deleteFile(string $path)
    {
        if (File::exists(public_path($path))) {
            File::delete(public_path($path));
        }
    }
}