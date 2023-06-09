

<style>
green { color: #299660}
yel { color: #9ea647}
blue { color: #099fc0}
red {color: #ce4141}
fs { font-size: 13px}
</style>

# symfony_files

# [Markdown Docs](https://www.w3schools.io/file/markdown-convertpdf/) 👋 Symfony Form Files  [symfony](https://symfony.com/ "@embed")
___________________________________________________________
>
>  Files symfony. ![image](https://cdn.path.to/some/image.jpg "This is some image...")

- ## <green>setup
      - symfony new Files --webapp
      - composer require "stof/doctrine-extensions-bundle" 
    ```
    config/packages/stof_doctrine-extensions.yaml
  
  
     stof_doctrine_extensions:
         default_locale: en_US
         orm:
              default:
                  sluggable: true
                  timestampable: true
  
  
  
     #[ORM\Entity(repositoryClass: ArticleRepository::class)]
    class Article
    {
        use TimestampableEntity;
    
        #[Gedmo\Slug(fields:['title'])]
        #[ORM\Column(length: 100, unique: true)]
        private ?string $slug = null;
    .... 
    }
    ```

      - symfony console make:entity   
      - composer require orm-fixtures --dev 
      - composer require zenstruck/foundry --dev 
      - symfony console make:factory 
    ```
       protected function getDefaults(): array
        {
            return [
                'title' => self::faker()->text(255)
            ];
        }
    ```
      - symfony console d:d:c
      - symfony console make:migration 
      - symfony console d:m:m
      - symfony console d:f:l
      - composer require form annotations  
      - symfony console make:crud
- ## <green>upload function in controller using simple Html Form
```

use Gedmo\Sluggable\Util\Urlizer;

 #[Route('/files', name: 'app_files')]
    public function index(Request $request): Response
    {
        if($request->isMethod('post')){

            $uploadedFile= $request->files->get('file');
            $destination=$this->getParameter('kernel.project_dir').'/public/uploads';

            //unique name
            //Urlizer remplace les espaces par des traits d'union
            $originalFilename=pathinfo($uploadedFile->getClientOriginalName(),PATHINFO_FILENAME);
            $newFilename= Urlizer::urlize( $originalFilename).'_'.uniqid('', true).'-'.$uploadedFile->guessExtension();

            //upload
            $uploadedFile->move($destination, $newFilename);
        }

        return $this->render('files/index.html.twig');
    }
    
    
    
    
 {% block body %}

    <h1 class="text-center">Upload and Download</h1>
    <div class="d-flex justify-content-center mt-5">
        <form method="post" action="{{ path('app_files') }}" enctype="multipart/form-data">
             <input type="file" name="file">
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>
{% endblock %}   

```
- ## <green>Upload using service and symfony Form
```
config/services.yaml

parameters:

services:
    # default configuration for services in *this* file
    _defaults:
        autowire: true      # Automatically injects dependencies in your services.
        autoconfigure: true # Automatically registers your services as commands, event subscribers, etc.

        bind:
             $uploadsPath: '%kernel.project_dir%/public/uploads'



________________________________________________________
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploaderHelper
{
  //declarer uploads dans config/services.yaml  services:bind:  $uploadsPath: '%kernel.project_dir%/public/uploads'
    public function __construct(private readonly string $uploadsPath)
    {
    }

    public function uploadFile(UploadedFile $uploadedFile):string{

    $destination = $this->uploadsPath;

    //unique name
    //Urlizer remplace les espaces par des traits d'union
    $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
    $newFilename = Urlizer::urlize($originalFilename) . '_' . uniqid('', true) . '-' . $uploadedFile->guessExtension();

    //upload
    $uploadedFile->move($destination, $newFilename);

    return $newFilename;
}
}

________________________________________________
class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title',TextareaType::class)
            ->add('imageFile',FileType::class,[
                'mapped'=>false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}

______________________________________________________
 #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, ArticleRepository $articleRepository,UploaderHelper $uploaderHelper): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form['imageFile']->getData()) {
                $uploadedFile = $form['imageFile']->getData();
                $newFilename=$uploaderHelper->uploadFile($uploadedFile);
                $article->setFile($newFilename);
            }
            $articleRepository->save($article, true);

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

```