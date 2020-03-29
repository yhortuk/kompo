<?php

namespace Kompo\Core;

use Kompo\Database\ModelManager;

class FileHandler
{
    /**
     * Boolean flag to indicate whether to store file attributes in separate columns.
     * 
     * @var array
     */
    protected $attributesToColumns = false;

    /**
     * The specified disk for uploaded files. 
     * By default, it is stored in the 'local' disk.
     */
    protected $disk = 'local';

	//File column names from config
    protected $allKeys;
    protected $idKey;
    protected $pathKey;
    protected $nameKey;
    protected $mime_typeKey;
    protected $sizeKey;

	public function __construct()
	{
        collect($this->allKeys = config('kompo.files_attributes'))->each(function($column, $key){
            $this->{$key.'Key'} = $column;
        });
	}


    /**
     * Saves the uploaded file or image to the specified disk. 
     * By default, it is stored in the 'local' disk.
     *
     * @param string $disk   The disk instance key.
     *
     * @return self
     */
    public function setDisk($disk)
    {
        return $this->disk = $disk;
    }

    /**
     * Sets the flag for a direct upload to a files table with columns from kompo.files_attributes.
     *
     * @return void 
     */
    public function activateAttributesToColumns()
    {
        $this->attributesToColumns = true;

        return $this->pathKey; //set the field name when direct file uploads
    }

    /**
     * Store the file and return the file information to be stored in the DB.
     *
     * @param Illuminate\Http\UploadedFile         $file
     * @param Illuminate\Database\Eloquent\Model   $model
     * @param string                               $name
     * @param boolean                              $withId
     *
     * @return array
     */
    public function fileToDB($file, $model, $name = null, $withId = false)
    {
    	$name = ($this->attributesToColumns || !$name) ? $this->pathKey  : $name;

        $modelPath = ModelManager::getStoragePath($model, $name);
        
        $file->store('public/'.$modelPath, $this->disk);

        return $this->mapToDB($file, $modelPath, $withId);
    }

    /**
     * Map the uploaded file information to the configurable file_attribute keys.
     *
     * @param Illuminate\Http\UploadedFile $file
     * @param string                       $modelPath 
     * @param boolean                      $withId
     *
     * @return array
     */
    protected function mapToDB($file, $modelPath, $withId = false)
    {
    	return array_merge([
            $this->nameKey => $file->getClientOriginalName(),
            $this->pathKey => 'storage/'.$modelPath.'/'.$file->hashName(),
            $this->mime_typeKey => $file->getClientMimeType(),
            $this->sizeKey => $file->getSize()
        ], $withId ? [
            $this->idKey => $file->hashName()
        ] : []);
    }

    /**
     * Prepare file information from the DB for Front-End display
     *
     * @param Illuminate\Database\Eloquent\Model $model
     * @param boolean                            $withId
     *
     * @return array
     */
    public function mapFromDB($model, $withId = false)
    {
    	return collect($this->allKeys)->map(function($key) use ($model, $withId){

    		if($key !== $this->idKey || $withId)
            	return $model->{$key};

        })->filter()->all();
    }

    /**
     * Delete the file if found in Storage
     *
     * @param mixed $file
     */
    public function unlinkFileIfExists($file)
    {
        if($file){
            $filePath = $file[$this->pathKey] ?? $file->{$this->pathKey};
            if($filePath && file_exists($path = storage_path('app/public'.substr($filePath, 7)) )){
                unlink($path);
                if(($this->withThumbnail ?? false) && file_exists(thumb($path)))
                    unlink(thumb($path));
            }
        }
    }

    /**
     * Clean old files if not present in request files.
     *
     * @param array $oldFiles  The old files
     * @param array $requestFiles  The new files
     */
    public function unlinkOldFilesInAttribute($oldFiles, $requestFiles)
    {
    	if(!$oldFiles)
    		return;
    	
        collect($oldFiles)->map(function($file) use($requestFiles){

            if(!in_array($file[$this->idKey] ?? null, $requestFiles->pluck($this->idKey)->all() ))

                $this->unlinkFileIfExists($file);
        });
    }

    /**
     * Gets the keys without id and path.
     */
    public function getKeysWithoutIdPath()
    {
    	return collect($this->allKeys)->filter(function($key){
            if(!in_array($key, [$this->idKey, $this->pathKey]))
                return $key;
        });
    }
    
}