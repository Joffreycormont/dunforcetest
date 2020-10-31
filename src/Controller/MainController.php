<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;

class MainController extends AbstractController
{
    protected $organizationList = "";

    public function __construct()
    {
        $this->organizationList = Yaml::parseFile(__DIR__.'/../Datas/organizations.yaml');
    }

    /**
     * @Route("/", name="home")
     * Page d'accueil
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'organizationList' => $this->organizationList['organizations']
        ]);
    }

    /**
     * @Route("/organisation/nouveau", name="add_organization_page")
     * Page d'ajout d'une organisation
     */
    public function addOrganization(): Response
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
            'label' => 'Envoyer '
        ])
        ->getForm();


        return $this->render('home/addOrganization.html.twig', [
            'organizationList' => $this->organizationList['organizations'],
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/organisation/edition/{id}", name="edit_organization_page")
     * Page d'édition d'une organisation
     */
    public function editOrganization($id): Response
    {
        $defaultData = [
            'name' => $this->organizationList['organizations'][$id]['name'],
            'description' => $this->organizationList['organizations'][$id]['description'],
        ];
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


        return $this->render('home/editOrganization.html.twig', [
            'index' => $id,
            'organization' => $this->organizationList['organizations'][$id],
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/utilisateur/nouveau", name="add_user_page")
     *  Page d'ajout utilisateur
     */
    public function addUser(): Response
    {
        $choiceOrganizationArray = [];

        foreach ($this->organizationList['organizations'] as $key => $organisation) {
            $choiceOrganizationArray += [$organisation['name'] => $key];
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
            'label' => 'Mot de passe '
        ])
        ->add('organization', ChoiceType::class, [
            'label' => 'Organisation ',
            'group_by' => 'group',
            'choices' => [
                $choiceOrganizationArray
            ]
        ])
        ->add('add', SubmitType::class, [
            'label' => 'Ajouter '
        ])
        ->getForm();


        return $this->render('user/addUser.html.twig', [
            'controller_name' => 'UserController',
            'userForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/utilisateur/edition/{id}/{organizationId}", name="edit_user_page")
     *  Page d'édition utilisateur
     */
    public function editUser($id, $organizationId): Response
    {
        $roles = "";

        foreach ($this->organizationList['organizations'][$organizationId]['users'][$id]['role'] as $role) {
            if(strlen($roles) == 0){
                $roles = $role;
            }else{
                $roles = $roles.','.$role;
            }
        }

        $defaultData = [
            'name' => $this->organizationList['organizations'][$organizationId]['users'][$id]['name'],
            'role' => $roles
        ];
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


        return $this->render('user/editUser.html.twig', [
            'controller_name' => 'UserController',
            'userId' => $id,
            'organizationId' => $organizationId,
            'userForm' => $form->createView()
        ]);
    }


    /**
     * @Route("/telechargement/fichier/yaml/{filename}", name="download_yaml")
     *  Page d'édition utilisateur
     */
    public function doawnloadYaml($filename): Response
    {
        $path = __DIR__.'/../Datas/';
        $content = file_get_contents($path.$filename.'.yaml');
    
        $response = new Response();
    
        //set headers
        $response->headers->set('Content-Type', 'mime/type');
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$filename.'.yaml');
    
        $response->setContent($content);
        return $response;
    }


}
