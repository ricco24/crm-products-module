<?php

namespace Crm\ProductsModule\Tests;

use Crm\ApplicationModule\ActiveRow;
use Crm\ApplicationModule\Tests\DatabaseTestCase;
use Crm\ProductsModule\Manager\ProductManager;
use Crm\ProductsModule\Repository\ProductBundlesRepository;
use Crm\ProductsModule\Repository\ProductsRepository;

class ProductManagerTest extends DatabaseTestCase
{
    /** @var ProductsRepository */
    private $productsRepository;

    /** @var ProductBundlesRepository */
    private $productBundlesRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->productsRepository = $this->getRepository(ProductsRepository::class);
        $this->productBundlesRepository = $this->getRepository(ProductBundlesRepository::class);
    }

    protected function requiredRepositories(): array
    {
        return [
            ProductsRepository::class,
            ProductBundlesRepository::class
        ];
    }

    protected function requiredSeeders(): array
    {
        return [
        ];
    }

    public function testDecreaseStockSimpleProduct(): void
    {
        /** @var ActiveRow $product */
        $product = $this->insertProduct();

        $productManager = new ProductManager($this->productsRepository);

        $productManager->decreaseStock($product, 1);
        $product = $this->productsRepository->find($product->id);
        $this->assertEquals(9, $product->stock);

        $productManager->decreaseStock($product, 3);
        $product = $this->productsRepository->find($product->id);
        $this->assertEquals(6, $product->stock);
    }

    public function testDecreaseStockBundleProducts(): void
    {
        $productManager = new ProductManager($this->productsRepository);

        /** @var ActiveRow $mainProduct */
        $mainProduct = $this->insertProduct(10);
        /** @var ActiveRow $bundleProduct */
        $bundleProduct = $this->insertProduct(7);

        $this->productBundlesRepository->add($mainProduct->id, $bundleProduct->id);

        $productManager->decreaseStock($bundleProduct, 3);

        $this->assertEquals(4, $this->productsRepository->find($mainProduct->id)->stock);
        $this->assertEquals(4, $this->productsRepository->find($bundleProduct->id)->stock);

        /** @var ActiveRow $mainProduct */
        $mainProduct = $this->insertProduct(4);
        /** @var ActiveRow $bundleProduct1 */
        $bundleProduct = $this->insertProduct(10);

        $this->productBundlesRepository->add($mainProduct->id, $bundleProduct->id);

        $productManager->decreaseStock($bundleProduct, 3);

        $this->assertEquals(4, $this->productsRepository->find($mainProduct->id)->stock);
        $this->assertEquals(7, $this->productsRepository->find($bundleProduct->id)->stock);

        /** @var ActiveRow $mainProduct */
        $mainProduct = $this->insertProduct(10);
        /** @var ActiveRow $bundleProduct1 */
        $bundleProduct1 = $this->insertProduct(10);
        /** @var ActiveRow $bundleProduct2 */
        $bundleProduct2 = $this->insertProduct(14);

        $this->productBundlesRepository->add($mainProduct->id, $bundleProduct1->id);
        $this->productBundlesRepository->add($mainProduct->id, $bundleProduct2->id);

        $productManager->decreaseStock($bundleProduct1, 2);
        $productManager->decreaseStock($bundleProduct2, 2);

        $this->assertEquals(8, $this->productsRepository->find($mainProduct->id)->stock);
        $this->assertEquals(8, $this->productsRepository->find($bundleProduct1->id)->stock);
        $this->assertEquals(12, $this->productsRepository->find($bundleProduct2->id)->stock);
    }

    private function insertProduct(int $stock = 10)
    {
        return $this->productsRepository->insert([
            'name' => 'test1',
            'code' => 'test1_code',
            'price' => 10.0,
            'vat' =>  19,
            'user_label' => 'user_label',
            'bundle' => 0,
            'shop' => 1,
            'visible' => 1,
            'stored' => 1,
            'stock' => $stock,
            'created_at' => new \DateTime(),
            'modified_at' => new \DateTime()
        ]);
    }
}
