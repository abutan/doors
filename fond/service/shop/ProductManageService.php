<?php

namespace app\fond\service\shop;


use app\fond\entities\manage\shop\Product;
use app\fond\forms\manage\shop\FeaturesForm;
use app\fond\forms\manage\shop\ModificationForm;
use app\fond\forms\manage\shop\PhotosForm;
use app\fond\forms\manage\shop\PriceForm;
use app\fond\forms\manage\shop\ProductCreateForm;
use app\fond\forms\manage\shop\ProductEditForm;
use app\fond\forms\manage\shop\ThicknessForm;
use app\fond\repositories\shop\CategoryRepository;
use app\fond\repositories\shop\ColorRepository;
use app\fond\repositories\shop\MaterialRepository;
use app\fond\repositories\shop\ProductRepository;
use app\fond\repositories\shop\SizeRepository;
use app\fond\service\TransactionManager;
use yii\helpers\Inflector;

class ProductManageService
{
    private $products;
    private $categories;
    private $transactions;
    private $colors;
    private $materials;
    private $sizes;

    public function __construct(ProductRepository $products, CategoryRepository $categories, ColorRepository $colors, MaterialRepository $materials, SizeRepository $sizes, TransactionManager $transactions)
    {
        $this->products = $products;
        $this->categories = $categories;
        $this->colors = $colors;
        $this->materials = $materials;
        $this->transactions = $transactions;
        $this->sizes = $sizes;
    }

    /**
     * @param ProductCreateForm $form
     * @return Product
     * @throws \yii\web\NotFoundHttpException
     */
    public function create(ProductCreateForm $form): Product
    {
        $category = $this->categories->get($form->categories->main);
        $product = Product::create(
            $form->name,
            $form->additionalName,
            $category->id,
            $form->code,
            $form->body,
            $form->slug ? : Inflector::slug($form->name),
            $form->title,
            $form->description,
            $form->slug
        );
        $product->setPrice(
            $form->price->doorOldPrice,
            $form->price->boxOldPrice,
            $form->price->boxPrice,
            $form->price->oldPrice,
            $form->price->price
        );
        $product->setThickness(
            $form->thickness->doorThickness,
            $form->thickness->doorFrameThickness,
            $form->thickness->doorSteelThickness,
            $form->thickness->frameSteelThickness
        );
        $product->setFeatures(
            $form->features->features,
            $form->features->innerFacing,
            $form->features->outFacing,
            $form->features->glass,
            $form->features->describe,
            $form->features->reveal,
            $form->features->opening,
            $form->features->complect,
            $form->features->cam,
            $form->features->packing,
            $form->features->doorInsulation,
            $form->features->boxInsulation,
            $form->features->intensive,
            $form->features->bracing,
            $form->features->weight
        );
        if (!empty($form->categories->others)){
            foreach ($form->categories->others as $otherId){
                $category = $this->categories->get($otherId);
                $product->assignCategory($category->id);
            }
        }
        if ($form->photos->files){
            foreach ($form->photos->files as $file) {
                $product->addPhoto($file);
            }
        }

        if ($form->colors->existing){
            foreach ($form->colors->existing as $colorId){
                $color = $this->colors->get($colorId);
                $product->addColor($color->id);
            }
        }

        if ($form->materials->existing){
            foreach ($form->materials->existing as $materialId){
                $material = $this->materials->get($materialId);
                $product->addMaterial($material->id);
            }
        }

        if ($form->sizes->existing){
            foreach ($form->sizes->existing as $sizeId){
                $size = $this->sizes->get($sizeId);
                $product->addSize($size->id);
            }
        }

        if ($form->relates->existing){
            foreach ($form->relates->existing as $relateId){
                $product->assignRelatedProduct($relateId);
            }
        }

        if ($form->additions->existing){
            foreach ($form->additions->existing as $additionId){
                $product->addAdditionalProduct($additionId);
            }
        }

        $this->products->save($product);

        return $product;
    }

    /**
     * @param $id
     * @param ProductEditForm $form
     * @throws \yii\db\Exception
     * @throws \yii\web\NotFoundHttpException
     */
    public function edit($id, ProductEditForm $form): void
    {
        $product = $this->products->get($id);
        $category = $this->categories->get($form->categories->main);

        $product->edit(
            $form->name,
            $form->additionalName,
            $category->id,
            $form->code,
            $form->body,
            $form->slug ? : Inflector::slug($form->name),
            $form->title,
            $form->description,
            $form->slug
        );
        $product->changeMainCategory($category->id);
        $this->transactions->wrap(function () use ($product, $form){
           $product->removeCategories();
           $product->revokeColors();
           $product->revokeMaterials();
           $product->revokeSizes();
           $product->revokeRelatedProducts();
           $product->revokeAdditionalProducts();
           $this->products->save($product);

            if (!empty($form->categories->others)){
                foreach ($form->categories->others as $otherId){
                    $category = $this->categories->get($otherId);
                    $product->assignCategory($category->id);
                }
            }

            if ($form->colors->existing){
                foreach ($form->colors->existing as $colorId){
                    $color = $this->colors->get($colorId);
                    $product->addColor($color->id);
                }
            }

            if ($form->materials->existing){
                foreach ($form->materials->existing as $materialId){
                    $material = $this->materials->get($materialId);
                    $product->addMaterial($material->id);
                }
            }

            if ($form->sizes->existing){
                foreach ($form->sizes->existing as $sizeId){
                    $size = $this->sizes->get($sizeId);
                    $product->addSize($size->id);
                }
            }

            if ($form->relates->existing){
                foreach ($form->relates->existing as $relateId){
                    $product->assignRelatedProduct($relateId);
                }
            }

            if ($form->additions->existing){
                foreach ($form->additions->existing as $additionId){
                    $product->addAdditionalProduct($additionId);
                }
            }
            $this->products->save($product);
        });
    }

    ##########

    /**
     * @param $id
     * @param PriceForm $form
     * @throws \yii\web\NotFoundHttpException
     */
    public function changePrice($id, PriceForm $form): void
    {
        $product = $this->products->get($id);
        $product->setPrice(
            $form->doorOldPrice,
            $form->boxOldPrice,
            $form->boxPrice,
            $form->oldPrice,
            $form->price
        );
        $this->products->save($product);
    }

    /**
     * @param $id
     * @param ThicknessForm $form
     * @throws \yii\web\NotFoundHttpException
     */
    public function changeThickness($id, ThicknessForm $form): void
    {
        $product = $this->products->get($id);
        $product->setThickness(
            $form->doorThickness,
            $form->doorFrameThickness,
            $form->doorSteelThickness,
            $form->frameSteelThickness
        );
        $this->products->save($product);
    }

    /**
     * @param $id
     * @param FeaturesForm $form
     * @throws \yii\web\NotFoundHttpException
     */
    public function changeFeatures($id, FeaturesForm $form): void
    {
        $product = $this->products->get($id);
        $product->setFeatures(
            $form->features,
            $form->innerFacing,
            $form->outFacing,
            $form->glass,
            $form->describe,
            $form->reveal,
            $form->opening,
            $form->complect,
            $form->cam,
            $form->packing,
            $form->doorInsulation,
            $form->boxInsulation,
            $form->intensive,
            $form->bracing,
            $form->weight
        );
        $this->products->save($product);
    }

    #########

    /**
     * @param $id
     * @throws \yii\web\NotFoundHttpException
     */
    public function draft($id): void
    {
        $product = $this->products->get($id);
        $product->draft();
        $this->products->save($product);
    }

    /**
     * @param $id
     * @throws \yii\web\NotFoundHttpException
     */
    public function activate($id): void
    {
        $product = $this->products->get($id);
        $product->activate();
        $this->products->save($product);
    }

    ###########

    /**
     * @param $id
     * @param PhotosForm $form
     * @throws \yii\web\NotFoundHttpException
     */
    public function addPhotos($id, PhotosForm $form): void
    {
        $product = $this->products->get($id);
        foreach ($form->files as $file){
            $product->addPhoto($file);
        }
        $this->products->save($product);
    }

    /**
     * @param $id
     * @param $photoId
     * @throws \yii\web\NotFoundHttpException
     */
    public function movePhotoUp($id, $photoId): void
    {
        $product = $this->products->get($id);
        $product->movePhotoUp($photoId);
        $this->products->save($product);
    }

    /**
     * @param $id
     * @param $photoId
     * @throws \yii\web\NotFoundHttpException
     */
    public function movePhotoDown($id, $photoId): void
    {
        $product = $this->products->get($id);
        $product->movePhotoDown($photoId);
        $this->products->save($product);
    }

    /**
     * @param $id
     * @param $photoId
     * @throws \yii\web\NotFoundHttpException
     */
    public function removePhoto($id, $photoId): void
    {
        $product = $this->products->get($id);
        $product->removePhoto($photoId);
        $this->products->save($product);
    }

    ##########

    /**
     * @param $id
     * @param ModificationForm $form
     * @throws \yii\web\NotFoundHttpException
     */
    public function addModification($id, ModificationForm $form): void
    {
        $product = $this->products->get($id);
        $product->addModification(
            $form->id,
            $form->name,
            $form->additionalName,
            $form->code,
            $form->price
        );
        $this->products->save($product);

        if (!empty($form->photo)){
            $product->addModificationPhoto($form->id, $form->photo);
        }
        $this->products->save($product);
    }

    /**
     * @param $id
     * @param $modificationId
     * @param ModificationForm $form
     * @throws \yii\web\NotFoundHttpException
     */
    public function editModification($id, $modificationId, ModificationForm $form): void
    {
        $product = $this->products->get($id);
        $product->editModification(
            $modificationId,
            $form->name,
            $form->additionalName,
            $form->code,
            $form->price
        );
        $this->products->save($product);
        if (!empty($form->photo)){
            $product->addModificationPhoto($modificationId, $form->photo);
        }
        $this->products->save($product);
    }

    /**
     * @param $id
     * @param $modificationId
     * @throws \yii\web\NotFoundHttpException
     */
    public function removeModification($id, $modificationId): void
    {
        $product = $this->products->get($id);
        $product->removeModification($modificationId);
        $this->products->save($product);
    }

    ##########

    /**
     * @param $id
     * @throws \yii\web\NotFoundHttpException
     */
    public function remove($id): void
    {
        $product = $this->products->get($id);
        $this->products->remove($product);
    }
}