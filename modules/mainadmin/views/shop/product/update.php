<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use mihaildev\ckeditor\CKEditor;

/* @var $this yii\web\View */
/* @var $model \app\fond\forms\manage\shop\ProductEditForm */
/* @var $product \app\fond\entities\manage\shop\Product */

$this->title = 'Редактирование товара: ' . $product->name;
$this->params['breadcrumbs'][] = ['label' => 'Товары', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $product->name, 'url' => ['view', 'id' => $product->id]];
$this->params['breadcrumbs'][] = 'Редактирование';
?>
<div class="product-update">
    <?php $form = ActiveForm::begin() ?>
    <div class="box box-default">
        <div class="box-header with-border">Общее</div>
        <div class="box-body">
            <div class="row">
                <div class="col-sm-4">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-sm-4">
                    <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-sm-4">
                    <?= $form->field($model, 'slug')->textInput(['maxlength' => true])->hint('не заполняйте это поле. Привозникновении ошибок и замечаний, обратитесь к администратору и получите инструкции по заполнению.') ?>
                </div>
            </div>
            <br>
            <div>
                <?= $form->field($model, 'body')->widget(CKEditor::className()) ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="box box-default">
                <div class="box-header with-border">Категория</div>
                <div class="box-body">
                    <?= $form->field($model->categories, 'main')->dropDownList($list = $model->categories->categoriesList(), ['prompt' => '']) ?>
                    <?= $form->field($model->categories, 'others')->checkboxList($list) ?>
                </div>
            </div>
        </div>
        <div class="col-sm-6">

        </div>
    </div>

    <div class="box box-default">
        <div class="box-header with-border">SEO</div>
        <div class="box-body">
            <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'keywords')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end() ?>
</div>