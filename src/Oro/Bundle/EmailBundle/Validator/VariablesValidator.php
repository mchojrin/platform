<?php

namespace Oro\Bundle\EmailBundle\Validator;

use Doctrine\ORM\EntityManager;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EmailBundle\Validator\Constraints\VariablesConstraint;

class VariablesValidator extends ConstraintValidator
{
    /** @var \Twig_Environment */
    protected $twig;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var EntityManager */
    protected $entityManager;

    /**
     * @param \Twig_Environment        $twig
     * @param SecurityContextInterface $securityContext
     * @param EntityManager            $entityManager
     */
    public function __construct(
        \Twig_Environment $twig,
        SecurityContextInterface $securityContext,
        EntityManager $entityManager
    ) {
        $this->twig            = $twig;
        $this->securityContext = $securityContext;
        $this->entityManager   = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($emailTemplate, Constraint $constraint)
    {
        /** @var EmailTemplate $emailTemplate */
        /** @var VariablesConstraint $constraint */

        $fieldsToValidate = array(
            'subject' => $emailTemplate->getSubject(),
            'content' => $emailTemplate->getContent(),
        );

        foreach ($emailTemplate->getTranslations() as $trans) {
            if (in_array($trans->getField(), array('subject', 'content'))) {
                $fieldsToValidate[$trans->getLocale() . '.' . $trans->getField()] = $trans->getContent();
            }
        }

        $errors = array();
        if (class_exists($emailTemplate->getEntityName())) {
            $className = $emailTemplate->getEntityName();

            /** @var ClassMetadataInfo $metadata */
            $classMetadata = $this->entityManager->getClassMetadata($className);
            $entity        = $classMetadata->newInstance();

            $errors = array();

            /** @var \Twig_Extension_Sandbox $sandbox */
            $sandbox = $this->twig->getExtension('sandbox');
            $sandbox->enableSandbox();

            foreach ($fieldsToValidate as $fieldName => $template) {
                try {
                    $this->twig->render(
                        $template,
                        array(
                            'entity' => $entity,
                            'user'   => $this->getUser()
                        )
                    );
                } catch (\Twig_Sandbox_SecurityError $e) {
                    $errors[$fieldName] = true;
                }
            }

            $sandbox->disableSandbox();
        }

        if (!empty($errors)) {
            $this->context->addViolation($constraint->message);
        }
    }

    /**
     * Return current user
     *
     * @return UserInterface|bool
     */
    private function getUser()
    {
        return $this->securityContext->getToken() && !is_string($this->securityContext->getToken()->getUser())
            ? $this->securityContext->getToken()->getUser() : false;
    }
}
