<?php
/**
 * Created by PhpStorm.
 * User: Evgeniy
 * Date: 24.01.2019
 * Time: 14:40
 */

namespace App\Controller;

use App\Entity\Library;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class LibraryController extends Controller
{
    /**
     * @Route("/",methods={"GET"},name="library_list")
     *
     */
    public function index(PaginatorInterface $paginator, Request $request)
    {
        $books = $paginator->paginate(
            $this->getDoctrine()->getRepository(Library::class)->findAll(),
            $request->query->getInt('page',1),
            10
        );
        return $this->render('library/index.html.twig', [
            'books' => $books
        ]);
    }

    /**
     * @Route("/show/{id}",name="library_show")
     */
    public function show($id)
    {
        $book = $this->getDoctrine()->getRepository(Library::class)->find($id);
        return $this->render('/library/show.html.twig',[
            'book' => $book
        ]);
    }

    /**
     * @Route("/new",methods={"GET","POST"},name="new_book")
     */
    public function new(Request $request)
    {
        $books = new Library();
        $form = $this->createFormBuilder($books)
            ->add('name', TextType::class,[
                'attr' => ['class' => 'form-control']
            ])
            ->add('author', TextType::class,[
                'attr' => ['class' => 'form-control']
            ])
            ->add('year', IntegerType::class,[
                'attr' => ['class' => 'form-control']
            ])->add('cover', FileType::class,[
                'attr' => ['class' => 'form-control']
            ])
            ->add('save',SubmitType::class,[
                'label' => 'Create',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ])
            ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $books->setCover($this->upload($request)); //upload image
            $entityManager = $this->getDoctrine()->getManager();
            $book = $form->getData();
            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('library_list');
        }
        return $this->render('/library/new.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}",methods={"GET","POST"},name="edit_book")
     */
    public function edit(Request $request, $id) {
        $book = $this->getDoctrine()->getRepository(Library::class)->find($id);

        $form = $this->createFormBuilder($book)
            ->add('name', TextType::class,[
                'attr' => ['class' => 'form-control']
            ])
            ->add('author', TextType::class,[
                'attr' => ['class' => 'form-control']
            ])
            ->add('year', IntegerType::class,[
                'attr' => ['class' => 'form-control']
            ])
            ->add('save',SubmitType::class,[
                'label' => 'Update',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ])
            ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('library_list');
        }
        return $this->render('/library/new.html.twig',[
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/delete/{id}",name="library_delete")
     * @Method({"DELETE"})
     */
    public function delete(Request $request,$id)
    {
        $book = $this->getDoctrine()->getRepository(Library::class)->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($book);
        $entityManager->flush();
        $response = new Response();
        $response->send();
    }

    protected function upload($request)
    {
        $file = $request->files->get('form')['cover'];
        $uploads_directory = $this->getParameter('uploads_directory');
        $filename = md5(uniqid()).'.'.$file->guessExtension();
        $file->move(
            $uploads_directory,
            $filename
        );
        return $filename;
    }



}