<?php

namespace app\fond\service\shop;


use app\fond\entities\manage\shop\Material;
use app\fond\forms\manage\shop\MaterialForm;
use app\fond\repositories\shop\MaterialRepository;

class MaterialManageService
{
    private $materials;

    public function __construct(MaterialRepository $materials)
    {
        $this->materials = $materials;
    }

    public function create(MaterialForm $form): Material
    {
        $material = Material::create(
            $form->name
        );
        $this->materials->save($material);

        return $material;
    }

    public function edit($id, MaterialForm $form): void
    {
        $material = $this->materials->get($id);
        $material->edit(
            $form->name
        );
        $this->materials->save($material);
    }

    public function remove($id): void
    {
        $material = $this->materials->get($id);
        $this->materials->remove($material);
    }
}