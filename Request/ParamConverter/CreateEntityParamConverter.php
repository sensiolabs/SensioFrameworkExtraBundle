<?php


namespace Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter;

use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ValidatorInterface;

class CreateEntityParamConverter implements ParamConverterInterface
{
    /**
     * @var EntityManager Manager registry
     */
    private $em;

    private $validator;

    /**
     * @param EntityManager $registry Manager registry
     */
    public function __construct(EntityManager $em, ValidatorInterface $validator)
    {
        $this->em = $em;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     *
     * Check, if object supported by our converter
     */
    public function supports(ParamConverter $configuration)
    {

        // Check, if option class was set in configuration
        if (null === $configuration->getClass()) {
            return false;
        }

        if(null === $this->em->getClassMetadata($configuration->getClass())->getName()){
            return false;   
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * Applies converting
     *
     * @throws \InvalidArgumentException When route attributes are missing
     * @throws NotFoundHttpException     When object not found
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = $configuration->getOptions($configuration);
        $fields = array();
        $fields = $request->request->all();

        if(!empty($options)){
            $fields = $this->excludeFields($fields, $options);
            $fields = $this->mapFields($fields, $options); 
        }
        $class = new \ReflectionClass($this->em->getClassMetadata($configuration->getClass())->getName());
        $instance = $class->newInstanceWithoutConstructor();
        $class_props = $class->getProperties();

        foreach ($class_props as $prop) {
            if(isset($fields[$prop->getName()])){
                $prop->setAccessible(true);
                $prop->setValue($instance, $fields[$prop->getName()]);    
            }
        }

        $config_name = $configuration->getName();
        $request->attributes->set($config_name, $instance);
        $errors = $this->validator->validate($instance);
        if(count($errors) > 0){
            $request->attributes->set('errors', $errors);

            return false;
        }

        $this->persistObject($instance, $options);

        return true;
    }

    protected function excludeFields($fields, $options){
        $field_exclude = isset($options['exclude']) ? $options['exclude'] : array();
        $fields = array_diff_key($fields, array_flip($field_exclude));

        return $fields; 
    }

    protected function mapFields($fields, $options){
        $field_mapping = isset($options['mapping']) ? $options['mapping'] : array();
        foreach ($field_mapping as $key => $value) {
            if(isset($fields[$key])){
                $fields[$value] = $fields[$key];
                unset($fields[$key]);    
            }
        }

        return $fields; 
    }

    protected function persistObject($object, $options){
        $persist = isset($options['persist']) ? $options['persist'] : false;
        if ($persist == true){
            $this->em->persist($object);
            $this->em->flush();  
        }
    }
}
