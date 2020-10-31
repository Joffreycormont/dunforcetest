<?php

namespace App\Controller;

use App\Controller\MainController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;

class UserController extends MainController
{
    /**
     * @Route("/user/add", name="user_add")
     *  route d'ajout d'un utilisateur
     */
    public function add(Request $request): Response
    {
        $choiceOrganizationArray = [];

        foreach ($this->organizationList['organizations'] as $key => $organisation) {
            $choiceOrganizationArray += [$organisation['name'] => $key];
        }

        $defaultData = [];
        $form = $this->createFormBuilder($defaultData)
        ->add('name', TextType::class)
        ->add('role', TextType::class)
        ->add('password', PasswordType::class)
        ->add('organization', ChoiceType::class, [
            'label' => 'Organisation ',
            'group_by' => 'group',
            'choices' => [
                $choiceOrganizationArray
            ]
        ])
        ->add('add', SubmitType::class)
        ->getForm();


        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            //gestion des roles multiple
            $roles = [];
            foreach ( explode(',',$data['role']) as $role) {
                array_push($roles, $role);
            }

            array_push($this->organizationList['organizations'][$data['organization']]['users'],[
                'name' => $data['name'],
                'role' => $roles,
                'password' => $data['password']
            ]);
    
            $convertedToYaml = Yaml::dump($this->organizationList);
            file_put_contents(__DIR__.'/../Datas/organizations.yaml', ($convertedToYaml));
        }

        
        return $this->redirectToRoute('home');
    }


    /**
     * @Route("/user/edit/{id}/organisation/{organizationId}", name="user_edit")
     * modifie un utilisateur dans le fichier yaml
     */
    public function edit($id, $organizationId, Request $request): Response
    {
        $roles = "";

        foreach ($this->organizationList['organizations'][$organizationId]['users'][$id]['role'] as $role) {
            if(strlen($roles) == 0){
                $roles = $role;
            }else{
                $roles = $roles.','.$role;
            }
        }

        $defaultData = [];
        $form = $this->createFormBuilder($defaultData)
        ->add('name', TextType::class, [
            'label' => 'Nom de l\'utilisateur '
        ])
        ->add('role', TextType::class, [
            'label' => 'Rôles (si plusieurs séparé par une ,) '
        ])
        ->add('password', PasswordType::class, [
            'label' => 'Mot de passe ',
            'empty_data' => $this->organizationList['organizations'][$organizationId]['users'][$id]['password'],
            'required' => false
        ])
        ->add('add', SubmitType::class, [
            'label' => 'Modifier '
        ])
        ->getForm();


        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            //gestion des roles multiple
            $roles = [];
            foreach ( explode(',',$data['role']) as $role) {
                array_push($roles, $role);
            }

            $this->organizationList['organizations'][$organizationId]['users'][$id]['name'] = $data['name'];
            $this->organizationList['organizations'][$organizationId]['users'][$id]['role'] = $roles;
            $this->organizationList['organizations'][$organizationId]['users'][$id]['password'] = $data['password'];
        
    
            $convertedToYaml = Yaml::dump($this->organizationList);
            file_put_contents(__DIR__.'/../Datas/organizations.yaml', ($convertedToYaml));
        }
        

        return $this->redirectToRoute('edit_organization_page', array('id' => $organizationId));
    }


    /**
     * @Route("/user/remove/{id}/organisation/{organizationId}", name="user_remove")
     * Supprime un utilisateur dans le fichier yaml
     */
    public function remove($id, $organizationId): Response
    {
        unset($this->organizationList['organizations'][$organizationId]['users'][$id]);

        $convertedToYaml = Yaml::dump($this->organizationList);
        file_put_contents(__DIR__.'/../Datas/organizations.yaml', ($convertedToYaml));

        return $this->redirectToRoute('edit_organization_page', array('id' => $organizationId));
    }
}
