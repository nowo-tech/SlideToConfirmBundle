<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\UseCaseFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

use function in_array;

use const JSON_THROW_ON_ERROR;

final class DemoController extends AbstractController
{
    /** @var list<string> */
    private const USE_CASES = [
        'payment',
        'delete',
        'publish',
        'legal',
        'cancel',
        'batch',
        'emergency',
        'gate',
    ];

    /** @var list<string> */
    private const LOCALES = ['en', 'es', 'it', 'fr', 'pt', 'de', 'nl'];

    #[Route('/', name: 'demo_index_default', methods: ['GET'])]
    public function indexDefault(): Response
    {
        return $this->redirectToRoute('demo_index', ['_locale' => 'en'], Response::HTTP_FOUND);
    }

    #[Route('/{_locale}', name: 'demo_index', requirements: ['_locale' => 'en|es|it|fr|pt|de|nl'], methods: ['GET', 'POST'])]
    public function index(Request $request, TranslatorInterface $translator): Response
    {
        $case = (string) $request->query->get('case', 'payment');
        if (!in_array($case, self::USE_CASES, true)) {
            $case = 'payment';
        }

        $form = $this->createForm(UseCaseFormType::class, null, [
            'use_case' => $case,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = json_encode($form->getData(), JSON_THROW_ON_ERROR);
            $this->addFlash('success', $translator->trans('demo.form_submitted', ['%data%' => $data]));

            return $this->redirectToRoute('demo_index', [
                '_locale' => $request->getLocale(),
                'case'    => $case,
            ]);
        }

        return $this->render('demo/index.html.twig', [
            'form'      => $form,
            'use_cases' => self::USE_CASES,
            'current'   => $case,
            'locales'   => self::LOCALES,
        ]);
    }
}
