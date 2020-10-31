<?php

namespace App\Controller;

use App\Controller\MainController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

class OrganizationsController extends MainController
{
    /**
     * @Route("/organization/add", name="organization_add", methods={"POST"})
     * Ajouter une organisation dans le fichier yaml
     */
    public function add(Request $request): Response
    {

        $defaultData = [];
        $form = $this->createFormBuilder($defaultData)
        ->add('name', TextType::class)
        ->add('description', TextareaType::class)
        ->add('send', SubmitType::class)
        ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            array_push($this->organizationList['organizations'],[
                'name' => $data['name'],
                'description' => $data['description'],
                'users' => [

                ]
            ]);
    
            $convertedToYaml = Yaml::dump($this->organizationList);
            file_put_contents(__DIR__.'/../Datas/organizations.yaml', ($convertedToYaml));
        }
    
        return $this->redirectToRoute('home');
    }


    /**
     * @Route("/organization/edit/{id}", name="organization_edit", methods={"POST"})
     * Ajouter une organisation dans le fichier yaml
     */
    public function edit($id, Request $request): Response
    {
        $defaultData = [];
        $form = $this->createFormBuilder($defaultData)
        ->add('name', TextType::class, [
            'label' => 'Nom '
        ])
        ->add('description', TextareaType::class, [
            'label' => 'Description '
        ])
        ->add('send', SubmitType::class, [
            'label' => 'Modifier '
        ])
        ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->organizationList['organizations'][$id]['name'] = $data['name'];
            $this->organizationList['organizations'][$id]['description'] = $data['description'];

    
            $convertedToYaml = Yaml::dump($this->organizationList);
            file_put_contents(__DIR__.'/../Datas/organizations.yaml', ($convertedToYaml));
        }
    
        return $this->redirectToRoute('home');
    }


    /**
     * @Route("/organization/remove/{id}", name="organization_remove")
     * Ajouter une organisation dans le fichier yaml
     */
    public function remove($id): Response
    {
        unset($this->organizationList['organizations'][$id]);

        $convertedToYaml = Yaml::dump($this->organizationList);
        file_put_contents(__DIR__.'/../Datas/organizations.yaml', ($convertedToYaml));
        return $this->redirectToRoute('home');
    }
}
