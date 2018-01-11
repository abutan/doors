<?php

namespace app\fond\forms\manage\shop;


use yii\base\Model;
use yii\web\UploadedFile;

class PhotosForm extends Model
{
    public $files;

    public function rules()
    {
        return [
            ['files', 'each', 'rule' => ['image']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'files' => 'Изображения',
        ];
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()){
            $this->files = UploadedFile::getInstances($this, 'files');
            return true;
        }
        return false;
    }
}