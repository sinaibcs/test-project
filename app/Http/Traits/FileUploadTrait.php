<?php

/**
 * Created by Vscode.
 * User: Shafiqul Islam
 * Date: 30/05/2022
 * Time: 12:28
 */

namespace App\Http\Traits;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

trait FileUploadTrait
{
    /**
     * @var string
     */
    protected $uploadPath = 'uploads';

    /**
     * @var
     */
    public $folderName;

    /**
     * @var string
     */
    public $rule = 'file|max:8000';

    /**
     * @return bool
     */
    private function createUploadFolder(): bool
    {
        if (!file_exists(config('disks.local') . '/' . $this->uploadPath . '/' . $this->folderName)) {
            $attachmentPath = config('disks.local') . '/' . $this->uploadPath . '/' . $this->folderName;
            File::makeDirectory(public_path($attachmentPath), 0777, true, true);
            return true;
        }

        if (!file_exists(public_path() . '/storage')) {
            Artisan::call('storage:link');
        }

        return false;
    }

    /**
     * For handle validation file action
     *
     * @param $file
     * @return fileUploadTrait|\Illuminate\Http\RedirectResponse
     */
    private function validateFileAction($file)
    {

        $rules = array('fileupload' => $this->rule);
        $file  = array('fileupload' => $file);

        $fileValidator = Validator::make($file, $rules);

        if ($fileValidator->fails()) {

            $messages = $fileValidator->getMessageBag();

            return redirect()->back()->withInput(request()->all())
                ->withErrors($messages);
        }
    }

    /**
     * For Handle validation file
     *
     * @param $files
     * @return fileUploadTrait|\Illuminate\Http\RedirectResponse
     */
    private function validateFile($files)
    {
        if (is_array($files)) {
            foreach ($files as $file) {
                return $this->validateFileAction($file);
            }
        }

        return $this->validateFileAction($files);
    }

    /**
     * For Handle Put File
     *
     * @param $file
     * @return bool|string
     */
    private function putFile($file)
    {
        $fileName = preg_replace('/\s+/', '-', time() . '-' . $file->getClientOriginalName());
        $path     = $this->uploadPath . '/' . $this->folderName . '/';

        if (Storage::putFileAs('public/' . $path, $file, $fileName)) {
            return 'storage/' . $path . $fileName;
        }

        return false;
    }

    /**
     * For Handle Save File Process
     *
     * @param $files
     * @return array
     */
    public function saveFiles($files)
    {
        $data = [];

        if ($files != null) {

            $this->validateFile($files);

            $this->createUploadFolder();

            if (is_array($files)) {

                foreach ($files as $file) {
                    $data[] = $this->putFile($file);
                }
            } else {

                $data[] = $this->putFile($files);
            }
        }

        return $data;
    }

    public function saveImage($file, $path, $width = 0, $height = 0): string
    {
        if ($file) {
            $url = $this->putFile($file);
            $public_path   = public_path($url);
            if ($width && $height) {
                $img   = Image::make($public_path)->resize($width, $height);
            }
            return $img->save($public_path) ? $url : '';
        }
    }
    public function base64Image($file)
    {

        $position = strpos($file, ';');
        $sub = substr($file, 0, $position);
        $ext = explode('/', $sub)[1];
        if(isset($ext) && ($ext == "png" || $ext == "jpeg" || $ext == "jpg" || $ext == "pdf")) {
            $newImageName = time() . "." . $ext;
        } else {
            $ext2 = explode('.', $ext)[3];
            if ($ext2 == "document") {
                $ext = "docx";
                $newImageName = time() . "." . $ext;
            } elseif ($ext2 == "sheet") {
                $ext = "xlsx";
                $newImageName = time() . "." . $ext;
            } else {
                $newImageName = "";
            }
            $newImageName = time() . "." . $ext;
        }
        $this->validateFile($file);
        $this->createUploadFolder();
        $uploadedPath = $this->uploadFile($newImageName, $file);
        return $uploadedPath;
    }
    private function uploadFile($newImageName, $file)
    {

        $path = $this->uploadPath . '/' . $this->folderName . '/';
        if (Storage::putFileAs('public/' . $path, $file, $newImageName)) {
            return  $path . $newImageName;
        }
    }
}
