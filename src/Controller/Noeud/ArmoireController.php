<?php

namespace App\Controller\Noeud;

use App\Entity\Armoire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


use App\Form\ArmoireType;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Knp\Component\Pager\PaginatorInterface;

class ArmoireController extends AbstractController {


    /**
     * @Route("/armoire", name="armoire_page")
     */
    public function index(Request $request,PaginatorInterface $paginator){

        $em = $this->getDoctrine()->getManager();

        $search_input = !empty($request->get('search_input')) ? $request->get('search_input'):'';

        $page = !empty($request->get('page'))?$request->query->get('page'):1;

        $selected_row = !empty($request->get('selected_row'))?$request->query->get('selected_row'):'';

        $query = $em->getRepository(Armoire::class)->findBySearchInput($search_input);

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', $page), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('Noeud/armoire/index.html.twig',[
            'current_menu'=>'armoire',
            'armoires'=>$pagination,
            'search_input'=>$search_input,
            'selected_row'=>$selected_row,
            'page'=>$page,
            'action'=>'' // add or edit
        ]);
    }


    /**
     * @Route("/add_armoire", name="add_armoire_page")
     */
    public function add(Request $request,PaginatorInterface $paginator){

        $em = $this->getDoctrine()->getManager();

        $armoire = new Armoire();

        $form = $this->createForm(ArmoireType::class,$armoire);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $em->persist($armoire);
            $em->flush();

            $this->addFlash('success', 'Un nouvel enregistrement est ajouté avec succès');
            
            return $this->redirectToRoute('armoire_page',array('selected_row'=>$armoire->getId()));
        }

        $query = $em->getRepository(Armoire::class)->findBySearchInput($search_input="");

        /*
hello
        */

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('Noeud/armoire/index.html.twig',[
            'current_menu'=>'armoire',
            'form'=>$form->createView(),
            'armoires'=>$pagination,
            'search_input'=>'',
            'selected_row'=>'',
            'page'=>1,
            'action'=>'add'
        ]);

    }


    /**
     * @Route("/edit_armoire/{id}", name="edit_armoire_page")
     */
    public function update(Request $request,PaginatorInterface $paginator,$id){

        $em = $this->getDoctrine()->getManager();

        $search_input = !empty($request->get('search_input')) ? $request->get('search_input'):'';

        $page = !empty($request->get('page'))?$request->query->get('page'):1;

        $armoire = $em->getRepository(Armoire::class)->find($id);

        if(!$armoire){

            $this->addFlash('warning', 'Aucun enregistrement correspond à votre requête !');

            return $this->redirectToRoute('armoire_page');
        }

        $form = $this->createForm(armoireType::class,$armoire);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $em->flush();

            $this->addFlash('success', 'Cet enregistrement est modifié avec succès');
            
            return $this->redirectToRoute('armoire_page',array('selected_row'=>$armoire->getId(),'search_input'=>$search_input,'page'=>$page));
        }

        $query = $em->getRepository(Armoire::class)->findBySearchInput($search_input);

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', $page), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('Noeud/armoire/index.html.twig',[
            'current_menu'=>'armoire',
            'form'=>$form->createView(),
            'armoires'=>$pagination,
            'search_input'=>$search_input,
            'selected_row'=>$armoire->getId(),
            'page'=>$page,
            'action'=>'edit'
        ]);

    }

    /**
     * @Route("/delete_armoire",name="delete_armoire")
     */
    public function delete(Request $request){

        $id = $request->get('id');

        $em = $this->getDoctrine()->getManager();

        $armoire = $em->getRepository(Armoire::class)->find($id);

        try{ 
            
        $em->remove($armoire);

        $em->flush();

       $this->addFlash('success', 'Cet enregistrement est supprimé avec succés');

        return new Response(json_encode(array()),
            200,
            array('Content-Type'=>'application/json'));

        } catch (ForeignKeyConstraintViolationException $e) {

            $msg = "Cet enregistrement a des services connectés, il ne peut donc pas être supprimé !";

            return new Response(json_encode(array('msg'=>$msg)),
            201,
            array('Content-Type'=>'application/json'));
        }

    }


} // fin armoireController
