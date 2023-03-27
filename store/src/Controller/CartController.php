<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\Type\CartType;
use App\Service\CartInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    private readonly CartInterface $cartHandler;

    /**
     * This will inject the default service CartHandler.
     * If you want to use the CartFile, rename the argument to $cartFile. @see config/services.yaml file.
     *
     * @param CartInterface $handler
     */
    public function __construct(CartInterface $handler)
    {
        $this->cartHandler = $handler;
    }

    #[Route('/cart', name: 'app_cart')]
    public function index(Request $request, EntityManagerInterface $manager): Response
    {
        $cart = $this->cartHandler->getCartInstance();
        $form = $this->createForm(CartType::class, $cart);
        $form->add('updateCart', SubmitType::class, ['label' => 'Update Cart'])
            ->add('apply_code', SubmitType::class, ['label' => 'Apply Code'])
            ->add('checkout', SubmitType::class, ['label' => 'checkout']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            if ($form->get('checkout')->isClicked()) {
                return $this->redirectToRoute('app_checkout');
            }
            return $this->redirectToRoute('app_cart');
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'form' => $form->createView(),
        ]);
    }


    #[Route(path: '/checkout', name: 'app_checkout')]
    public function checkout(): Response
    {
        return $this->render('cart/checkout.html.twig');
    }
}
