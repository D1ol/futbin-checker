<?php

namespace App\Controller;

use App\Entity\Test\Product;
use App\Form\AddItemToCartFormType;
use App\Repository\Test\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends AbstractController
{
    /**
     * @Route("/", name="app_homepage")
     * @Route("/category/{id}", name="app_category")
     */
    public function index(Request $request, ProductRepository $productRepository, int $id): Response
    {
        $searchTerm = $request->query->get('q');
        $products = $productRepository->search(
            $searchTerm
        );

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'searchTerm' => $searchTerm
        ]);
    }

    #[Route('/product/{id}', name: 'app_product')]
    public function showProduct(Product $product): Response
    {
        $addToCartForm = $this->createForm(AddItemToCartFormType::class, null, [
            'product' => $product
        ]);

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'addToCartForm' => $addToCartForm->createView()
        ]);
    }
}
