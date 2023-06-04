<?php

namespace App\Controller;
use mysql_xdevapi\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Job;
use App\Entity\Image;
use App\Entity\Candidature;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation;
use App\Form\JobType;
use function PHPUnit\Framework\returnArgument;

class JobController extends AbstractController
{
    /**
     * @Route("/job", name="app_job")
     */
    public function index(): Response

    {
        $entityManager = $this->getDoctrine()->getManager();
        $job = new Job();
        $job->setType('Architecte');
        $job->setCompany('OffShoreBox');
        $job->setDescription('Genie logiciel');
        $job->setExpiresAt(new \DateTimeImmutable());
        $job->setEmail('khawla@gmail.com');
        $image=new Image();
        $image->setUrl('https://cdn.pixabay.com/photo/2015/10/30/10/03/gold-1013618_960_720.jpg');
        $job->setImage($image);
        //ajout de candidats
        $candidature1=new Candidature();
        $candidature1->setCandidat("khawla");
        $candidature1->setContenu("formation j2EE");
        $candidature1->getDatec(new \DateTime());
        $candidature2=new Candidature();
        $candidature2->setCandidat("youssef");
        $candidature2->setContenu("formation symfony");
        $candidature2->getDatec(new \DateTime());
        $candidature1->setJob($job);
        $candidature2->setJob($job);
        $entityManager->persist($job);
        $entityManager->persist($candidature1);
        $entityManager->persist($candidature2);
        $entityManager->flush();
        return $this->render('job/index.html.twig', [
            'id'=>$job->getId()
        ]);

       $entityManager->persist($job);
        $entityManager->flush();

        return $this->render('job/index.html.twig', [

            'id'=>$job->getId(),
        ]);
    }

/**
 * @Route("/job/{id}", name="job_show")
 */
public function show($id,Request $request)
{

    $job = $this->getDoctrine()
        ->getRepository(Job::class)
        ->find($id);
    if (!$job) {
        throw $this->createNotFoundException(
            'No job found for id '.$id
        );
    }
    $em=$this->getDoctrine()->getManager();

    $listCandidatures=$em
        ->getRepository(Candidature::class)
        ->findBy(['Job'=>$job]);
    $publicPath = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath().'/uploads/jobs/';

    return $this->render('job/show.html.twig', [
        'job' =>$job,
       'listCandidatures'=>$listCandidatures,
        'publicPath' => $publicPath
    ]);
}
    /**
     * @Route("/", name="home")
     */
    public function home(Request $request){
        //creation champ
        $form = $this->createFormBuilder()
            ->add("critere",TextType::class)
            ->add ('valider',SubmitType::class)
            ->getForm();
            $form->handleRequest($request);
            $em=$this->getDoctrine()->getManager();
        $repo=$em->getRepository(Candidature::class);
        $lesCandidats=$repo->findAll();
        if($form->isSubmitted()){
            $data=$form->getData();
            $lesCandidats=$repo->recherche($data['critere']);
        }

        return $this->render('job/home.html.twig',[
            'lesCandidats'=>$lesCandidats,
            'form'=>$form->createView()
        ]);

    }
    /**
     * @Route("/Ajouter", name="Ajouter")
     */
    public function ajouter(Request $request)
    {
        $candidat = new Candidature();
        $fb = $this->createFormBuilder($candidat)
            ->add('candidat', TextType::class)
            ->add('contenu', TextType::class, array("label" => "Contenu"))
            ->add('datec', DateType::class)
            ->add('job', EntityType::class, [
                'class' => Job::class,
                'choice_label' => 'type',
            ])
            ->add('valider', SubmitType::class);

        //générer le formulaire a partir de formBuilder

        $form = $fb->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($candidat);
            $em->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render('job/ajouter.html.twig',
            ['f'=>$form->createView()]);
    }
        /**
         * @Route("/add", name="ajouter_job")
         */

        public function ajouter2(Request $request)
    {
        $publicPath ="uploads/jobs/";
        $job =new Job();
        $form = $this->createForm("App\Form\JobType",$job);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            /*
        *@var UploadedFile $image
         */
            $image = $form->get('image')->getData();
            $em =$this->getDoctrine()->getManager();
            if($image){
                $imageName = $job->getDescription().'.'.$image->guessExtension();
                $image->move($publicPath,$imageName);
                $job->setImage($imageName);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($job);
            $em->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render('job/ajouter.html.twig',
            ['f'=>$form->createView()]);




    }
/**
 * @Route ("/supp/{id}", name="cand_delete")
 *
 */
public function delete (Request $request,$id):Response
{
    $c = $this->getDoctrine()
        ->getRepository(Candidature::class)
        ->find($id);
    if (!$c){
        throw $this->createNotFoundException(
            'No job found for id ' .$id
        );

    }
    $entityManager=$this->getDoctrine()->getManager();
    $entityManager->remove($c);
    $entityManager->flush();
    return $this->redirectToRoute('home');
}
    /**
     * @Route("/editU/{id}", name="edit_user")
     * Method({"GET","POST"})
     */
    public function edit(Request $request, $id)
    { $candidat = new Candidature();
        $candidat = $this->getDoctrine()
            ->getRepository(Candidature::class)
            ->find($id);
        if (!$candidat) {
            throw $this->createNotFoundException(
                'No candidat found for id '.$id
            );
        }
        $fb = $this->createFormBuilder($candidat)
            ->add('candidat', TextType::class)
            ->add('contenu', TextType::class, array("label" => "Contenu"))
            ->add('datec', DateType::class)
            ->add('job', EntityType::class, [
                'class' => Job::class,
                'choice_label' => 'type',
            ])
            ->add('Valider', SubmitType::class);
// générer le formulaire à partir du FormBuilder
        $form = $fb->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render('job/ajouter.html.twig',
            ['f' => $form->createView()] );
    }
//liste job
    /**
     * @Route("/listejob", name="listejob")
     */
    public function liste (Request $request){
        //creation champ
        $form = $this->createFormBuilder()
            ->add("critere",TextType::class)
            ->add ('valider',SubmitType::class)
            ->getForm();
        $form->handleRequest($request);
        $em=$this->getDoctrine()->getManager();
        $repo=$em->getRepository(Job::class);
        $lesjobs=$repo->findAll();


        return $this->render('job/list.html.twig',[
            'lesjobs'=>$lesjobs,
            'form1'=>$form->createView()
        ]);

    }

}