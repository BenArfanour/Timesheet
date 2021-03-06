<?php


namespace Application\Sonata\UserBundle\Admin;



use Sonata\UserBundle\Admin\Model\UserAdmin as BaseAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\UserBundle\Form\Type\SecurityRolesType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormTypeInterface;

class UserAdmin extends BaseAdmin
{
    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * {@inheritdoc}
     */
    public function getFormBuilder()
    {
        $this->formOptions['data_class'] = $this->getClass();

        $options = $this->formOptions;
        $options['validation_groups'] = (!$this->getSubject() || null === $this->getSubject()->getId()) ? 'Registration' : 'Profile';

        $formBuilder = $this->getFormContractor()->getFormBuilder($this->getUniqid(), $options);

        $this->defineFormBuilder($formBuilder);

        return $formBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getExportFields()
    {
        // avoid security field to be exported
        return array_filter(parent::getExportFields(), function ($v) {
            return !in_array($v, ['password', 'salt']);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($user): void
    {
        $this->getUserManager()->updateCanonicalFields($user);
        $this->getUserManager()->updatePassword($user);
    }

    /**
     * @param UserManagerInterface $userManager
     */
    public function setUserManager(UserManagerInterface $userManager): void
    {
        $this->userManager = $userManager;
    }

    /**
     * @return UserManagerInterface
     */
    public function getUserManager()
    {
        return $this->userManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('username')
            ->add('email')
            ->add('groups')
            ->add('enabled', null, ['editable' => true])
            ->add('createdAt')

            ->add('dateOfBirth', 'date', array(
                  'pattern' => 'dd MMM y G',
                  'locale' => 'fr',
                   'label' => 'Date de Naissance',
                'timezone' => 'Europe/Paris',
                   )  );

//        if ($this->isGranted('ROLE_SONATA_ADMIN')) {
//            $listMapper
//                ->add('impersonating', 'string', ['template' => '@SonataUser/Admin/Field/impersonating.html.twig']);
//        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filterMapper): void
    {
        $filterMapper
            ->add('id')
            ->add('username')
            ->add('email')
            ->add('groups');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->with('General')
            ->add('username')
            ->add('email')
            ->end()
            ->with('Groups')
            ->add('groups')
            ->end()
            ->with('Profile')
            ->add('dateOfBirth')
            ->add('firstname')
            ->add('lastname')
            //->add('website')
            // ->add('biography')
            ->add('gender')
            //  ->add('locale')
            // ->add('timezone')
            ->add('phone')
            ->end()
            //  ->with('Social')
            //  ->add('facebookUid')
            //->add('facebookName')
            //->add('twitterUid')
            //->add('twitterName')
            //->add('gplusUid')
            //->add('gplusName')
            //->end()
            //->with('Security')
            //->add('token')
            //->add('twoStepVerificationCode')
            //->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        // define group zoning
        $formMapper
            ->tab('User')
            ->with('Profile', ['class' => 'col-md-6'])->end()
            ->with('General', ['class' => 'col-md-6'])->end()
            //->with('Social', ['class' => 'col-md-6'])->end()
            ->end()
            ->tab('Security')
            ->with('Status', ['class' => 'col-md-4'])->end()
            ->with('Groups', ['class' => 'col-md-4'])->end()
//            ->with('Keys', ['class' => 'col-md-4'])->end()
            ->with('Roles', ['class' => 'col-md-4'])->end()
            ->end();

        $now = new \DateTime();

        $genderOptions = [
            'choices' => call_user_func([$this->getUserManager()->getClass(), 'getGenderList']),
            'required' => true,
            'translation_domain' => $this->getTranslationDomain(),
        ];

        // NEXT_MAJOR: Remove this when dropping support for SF 2.8
        if (method_exists(FormTypeInterface::class, 'setDefaultOptions')) {
            $genderOptions['choices_as_values'] = true;
        }

        $formMapper
            ->tab('User')
            ->with('General')
            ->add('username')
            ->add('email')
            ->add('plainPassword', 'repeated', array(
                'type' => 'password',
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' => 'form.password'),
                'second_options' => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
            ))
            ->end()
            ->with('Profile')
            ->add('images', 'sonata_type_collection', array(), array(
                'edit' => 'inline',
                'inline' => 'table',
                'link_parameters' => array(
                    'context' => 'images',
                    'provider' => 'sonata.media.provider.image'
                )
            ))
            ->add('dateOfBirth','sonata_type_date_picker', array(
                'label' => 'Date de Naissance',
                'dp_language' => 'fr',
                'format' => 'dd/MM/yyyy'
            ))
            ->add('firstname', null, ['required' => false])
            ->add('lastname', null, ['required' => false])
//            ->add('website', UrlType::class, ['required' => false])
//            ->add('biography', TextType::class, ['required' => false])
            ->add('gender', ChoiceType::class, $genderOptions)
//            ->add('locale', LocaleType::class, ['required' => false])
//            ->add('timezone', TimezoneType::class, ['required' => false])
            ->add('phone', null, ['required' => false])
            ->end(
)//            ->with('Social')
//            ->add('facebookUid', null, ['required' => false])
//            ->add('facebookName', null, ['required' => false])
//            ->add('twitterUid', null, ['required' => false])
//            ->add('twitterName', null, ['required' => false])
//            ->add('gplusUid', null, ['required' => false])
//            ->add('gplusName', null, ['required' => false])
//            ->end()
            ->end()
            ->tab('Security')
            ->with('Status')
            ->add('enabled', null, ['required' => false])
            ->end()
            ->with('Groups')
            ->add('groups', ModelType::class, [
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ])
            ->end()
            ->with('Roles')
            ->add('realRoles', SecurityRolesType::class, [
                     'label' => 'form.label_roles',
                     'expanded' => true,
                     'multiple' => true,
                     'required' => false,
            ])
            ->end();

    }
}
